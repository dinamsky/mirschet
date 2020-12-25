<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2017-2018
 * @license Webasyst
 */

use PhpUnitsOfMeasure\PhysicalQuantity\Length;
use PhpUnitsOfMeasure\PhysicalQuantity\Mass;
use SergeR\Webasyst\CdekSDK\API\Order\Request\DeleteRequest;
use SergeR\Webasyst\CdekSDK\API\Order\Request\DeliveryRequest;
use SergeR\Webasyst\CdekSDK\API\Order\Response\Type\CommonOrderResult;
use SergeR\Webasyst\CdekSDK\API\Order\Response\Type\OrderResult;
use SergeR\Webasyst\CdekSDK\Type\CourierCall;
use SergeR\Webasyst\CdekSDK\Type\OrderAddress;
use SergeR\Webasyst\CdekSDK\Type\OrderItem;
use SergeR\Webasyst\CdekSDK\Type\OrderSender;
use SergeR\Webasyst\CdekSDK\Type\ShopOrder;
use SergeR\Webasyst\CdekSDK\Type\StreetAddress;
use SergeR\Webasyst\Shipping\Packages\PackageCollection;
use SergeR\CakeUtility\Hash;

/**
 * Class shopSdekintPluginCourierActions
 */
class shopSdekintPluginCourierActions extends waJsonActions
{
    /** @var shopSdekintPlugin */
    protected $plugin;

    /** @var shopSdekintPluginCourierCallsModel */
    protected $CourierCall;

    /**
     * @throws waException
     */
    protected function preExecute()
    {
        parent::preExecute();
        $this->plugin = wa('shop')->getPlugin('sdekint');
        $this->CourierCall = new shopSdekintPluginCourierCallsModel();
    }

    public function callsAction()
    {
        $pager = $this->plugin->helper->paginator(
            waRequest::request('page', 1, waRequest::TYPE_INT),
            (int)$this->CourierCall->countAll(),
            (int)$this->getConfig()->getOption('products_per_page')
        );

        $sql = 'SELECT scc.*, sc.name as city_name, sc.area as city_area, wr.name as wr_name ' .
            'FROM shop_sdekint_courier_calls AS scc ' .
            'LEFT JOIN shop_sdekint_cities sc ON sc.sdek_id=scc.city_code ' .
            'LEFT JOIN wa_region AS wr ON wr.code=sc.region_code AND wr.country_iso3="rus" ' .
            'ORDER BY scc.call_date DESC, scc.time_beg ASC ' .
            "LIMIT {$pager['limit'][0]},{$pager['limit'][1]}";
        $calls = (array)$this->CourierCall->query($sql)->fetchAll();

        array_walk($calls, function (&$call) {
            $address_array = array();
            $person_array = array();
            if (!empty($call['wr_name']) && !in_array($call['city_code'], array('44', '137', '15256'))) {
                $address_array[] = $call['wr_name'];
            }
            foreach (array('city_area', 'city_name', 'address_street', 'address_house', 'address_flat') as $field) {
                if (!empty($call[$field])) {
                    $address_array[] = $call[$field];
                }
            }
            $call['address_string'] = implode(', ', $address_array);
            foreach (array('send_name', 'send_phone') as $field) {
                $person_array[] = $call[$field];
            }
            $call['sender'] = implode(', ', $person_array);
            $call['sent'] = waDateTime::format('humandatetime', strtotime($call['sent']));
            $call['call_date'] = waDateTime::format('humandate', strtotime($call['call_date']));
            $call['time_beg'] = waDateTime::format('time', strtotime($call['time_beg']));
            $call['time_end'] = waDateTime::format('time', strtotime($call['time_end']));
            $call['call_time'] = "{$call['time_beg']} &ndash; {$call['time_end']}";
        });

        $pagination = $this->plugin->helper->pagination($pager + ['mode' => 'hash', 'url_before' => '#couriercalls/']);
        $view = wa('shop')->getView();
        $view->assign(compact(['calls', 'pager', 'pagination']));
        $this->response['html'] = $view->fetch($this->getTemplate('Calls'));
    }

    public function indexAction()
    {
        $pagination = $this->getPagination();

        $sql = 'SELECT scc.*, sc.name as city_name, sc.area as city_area, wr.name as wr_name ' .
            'FROM shop_sdekint_courier_calls AS scc ' .
            'LEFT JOIN shop_sdekint_cities sc ON sc.sdek_id=scc.city_code ' .
            'LEFT JOIN wa_region AS wr ON wr.code=sc.region_code AND wr.country_iso3="rus" ' .
            'ORDER BY scc.call_date DESC, scc.time_beg ASC ' .
            "LIMIT {$pagination['start']},{$pagination['count']}";
        $data = $this->CourierCall->query($sql)->fetchAll();

        array_walk($data, function (&$request) {
            $address_array = array();
            $person_array = array();
            if (!empty($request['wr_name']) && !in_array($request['city_code'], array('44', '137', '15256'))) {
                $address_array[] = $request['wr_name'];
            }
            foreach (array('city_area', 'city_name', 'address_street', 'address_house', 'address_flat') as $field) {
                if (!empty($request[$field])) {
                    $address_array[] = $request[$field];
                }
            }
            $request['address_string'] = implode(', ', $address_array);
            foreach (array('send_name', 'send_phone') as $field) {
                $person_array[] = $request[$field];
            }
            $request['sender'] = implode(', ', $person_array);
            $request['sent'] = waDateTime::format('humandatetime', strtotime($request['sent']));
            $request['call_date'] = waDateTime::format('humandate', strtotime($request['call_date']));
            $request['time_beg'] = waDateTime::format('time', strtotime($request['time_beg']));
            $request['time_end'] = waDateTime::format('time', strtotime($request['time_end']));
            $request['call_time'] = "{$request['time_beg']} &ndash; {$request['time_end']}";
        });

        $this->response = array(
            'calls'      => $data,
            'pagination' => array(
                'total' => (int)$this->CourierCall->countAll(),
                'page'  => $pagination['page'],
                'limit' => $pagination['count']
            )
        );
        $this->response['pagination']['pages'] = (int)ceil($this->response['pagination']['total'] / $this->response['pagination']['limit']);
        if (!$this->response['pagination']['pages']) {
            $this->response['pagination']['pages'] = 1;
        }
    }

    /**
     * @throws waException
     */
    public function callAction()
    {
        if ($this->getRequest()->getMethod() != 'post') {
            throw new waException('Only POST requests');
        }

        $Api = new shopSdekintPluginSdekApi(['auth_login' => $this->plugin->getSettings('api_auth'), 'secret_key' => $this->plugin->getSettings('api_key')]);
        $data = $this->getRequest()->post('data');

        if (array_key_exists('call', $data) && is_array($data['call'])) {
            array_walk($data['call'], function (&$call) {
                if (array_key_exists('Weight', $call)) {
                    $call['Weight'] = shopSdekintPluginHelper::toFloat($call['Weight']);
                    $call['Weight'] = (int)shopDimension::getInstance()->convert($call['Weight'], 'weight', 'g', 'kg');
                }
            });

            try {
                $xml = $Api->CourierCallRequest($data);
            } catch (Exception $e) {
                $this->errors[] = "Неизвестный формат ответа сервера. " . $e->getMessage();
                return;
            }

            /** @var SimpleXMLElement[] $xml_err */
            $xml_err = $xml->xpath('//response/CallCourier[@ErrorCode]');
            if (count($xml_err)) {
                foreach ($xml_err as $e) {
                    $this->errors[] = 'Ошибка: ' . $e['Msg'];
                }
                return;
            }

            /** @var SimpleXMLElement[] $xml_calls */
            $xml_calls = $xml->xpath('//response/Call');
            $send_date = new DateTime();
            foreach ($xml_calls as $i => $xml_call) {
                $rec = array(
                    'code'           => (string)$xml_call["Number"],
                    'call_date'      => $data['call'][$i]['Date'],
                    'time_beg'       => $data['call'][$i]['TimeBeg'],
                    'time_end'       => $data['call'][$i]['TimeEnd'],
                    'send_phone'     => $data['call'][$i]['SendPhone'],
                    'send_name'      => $data['call'][$i]['SenderName'],
                    'weight'         => $data['call'][$i]['Weight'],
                    'comment'        => $data['call'][$i]['Comment'],
                    'city_code'      => $data['call'][$i]['SendCityCode'],
                    'address_street' => $data['call'][$i]['Address']['Street'],
                    'address_house'  => $data['call'][$i]['Address']['House'],
                    'address_flat'   => $data['call'][$i]['Address']['Flat'],
                    'sent'           => $send_date->format('Y-m-d H:i:s')
                );

                $res = $this->CourierCall->insert($rec);
                if ($res) {
                    $this->response[] = ['id' => $res, 'code' => (string)$xml_call['Number'], 'result' => 'ok'];
                }
            }
        }
    }

    /**
     * Делает запрос на удаление накладной с забором товаров по 158 тарифу.
     * Надеюсь, этого достаточно, чтобы отменить заказ
     *
     * @throws waException
     */
    public function dismissPickupAction()
    {
        if ($this->getRequest()->method() != 'post') {
            throw new waException('Method not allowed', 405);
        }

        $data = $this->getRequest()->post('data');
        if (!$data) {
            throw new waException('Bad request', 400);
        }
        $data = waUtils::jsonDecode($data, true);
        if (!is_array($data) || empty($data)) {
            throw new waException('Bad request', 400);
        }

        if (!array_key_exists('order_no', $data) || empty($data['order_no'])) {
            throw new waException('Bad request', 400);
        }

        if (!array_key_exists('number_ttn', $data) || empty($data['number_ttn'])) {
            throw new waException('Bad request', 400);
        }

        if (!$this->CourierCall->countByField(['order_no' => $data['order_no'], 'number_ttn' => $data['number_ttn']])) {
            $this->errors[] = sprintf('Заказа с номером %s не найдено', $data['order_no']);
        }

        try {
            $result = $this->plugin->getOrdersApiClient()->deleteRequest(
                (new DeleteRequest)
                    ->setNumber((string)$data['number_ttn'])
                    ->addOrder((string)$data['order_no'])
            );

            $order_id_str = (string)$data['order_no'];
            $deleted_order = $result->getOrders()->findByNumber($order_id_str);

            if (!($deleted_order instanceof CommonOrderResult)) {
                throw new waException('[' . __CLASS__ . '::dismissPickupAction]: В ответе сервера не найдена информация о заказе: ' . $order_id_str);
            }

            // Ошибка и это не "Заказ не найден"
            if ($deleted_order->isError() && ($deleted_order->getErrorCode() !== 'ERR_ORDER_NOTFIND')) {
                throw new waException($deleted_order->getErrorCode() . ': ' . $deleted_order->getErrorMessage());
            }
        } catch (Exception $e) {
            shopSdekintPluginHelper::log(
                sprintf('Ошибка удаления заказа %s. "%s"', $data['order_no'], $e->getMessage()),
                true
            );
            $this->errors[] = $e->getMessage();
            return;
        }

        $this->CourierCall->deleteByField(['order_no' => $data['order_no'], 'number_ttn' => $data['number_ttn']]);
    }

    /**
     * @throws waException
     */
    public function pickupAction()
    {
        if ($this->getRequest()->method() === 'get') {
            $this->pickupForm();
            return;
        }

        if ($this->getRequest()->method() != 'post') {
            throw new waException('Method not allowed', 405);
        }
        $data = $this->getRequest()->post('data');
        if (!$data) {
            throw new waException('Bad request', 400);
        }

        $data = waUtils::jsonDecode($data, true);
        if (!is_array($data) || empty($data)) {
            throw new waException('Bad request', 400);
        }

        if (!Hash::get($data, 'number')) {
            $this->errors[] = 'Не указан номер акта приема/передачи';
            return;
        }

        try {
            if (Hash::get($data, 'date')) {
                $date = new DateTimeImmutable(Hash::get($data, 'date'));
            } else {
                $date = new DateTimeImmutable();
            }
            $delivery_request = (new DeliveryRequest)->setNumber(Hash::get($data, 'number'))->setDate($date);
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return;
        }

        $pickup_address = StreetAddress::fromArray(array_filter([
            'street' => (string)Hash::get($data, 'call_courier.send_address.street'),
            'house'  => (string)Hash::get($data, 'call_courier.send_address.house'),
            'flat'   => (string)Hash::get($data, 'call_courier.send_address.flat')
        ]));

        $sdek_order = ShopOrder::fromArray(array_filter([
            'number'           => Hash::get($data, 'order.number'),
            'send_city_code'   => Hash::get($data, 'order.send_city_code'),
            'rec_city_code'    => Hash::get($data, 'order.rec_city_code'),
            'address'          => (new OrderAddress)->setPvzCode(Hash::get($data, 'order.pvz_code')),
            'recipient_name'   => Hash::get($data, 'order.recipient_name'),
            'phone'            => Hash::get($data, 'order.recipient_phone'),
            'tariff_type_code' => 158,
            'seller_name'      => $this->plugin->seller_name,
            'sender'           => OrderSender::fromArray(array_filter([
                'name'    => (string)Hash::get($data, 'call_courier.send_address.sender_name'),
                'address' => $pickup_address
            ])),
        ]));

        try {
            $courier_call = CourierCall::fromArray(array_filter([
                'date'           => new DateTimeImmutable(Hash::get($data, 'call_courier.date')),
                'time_beg'       => (string)Hash::get($data, 'call_courier.time_beg'),
                'time_end'       => (string)Hash::get($data, 'call_courier.time_end'),
                'send_city_code' => (int)Hash::get($data, 'call_courier.send_city_code'),
                'send_phone'     => (string)Hash::get($data, 'call_courier.send_phone'),
                'sender_name'    => (string)Hash::get($data, 'call_courier.send_address.sender_name'),
                'send_address'   => $pickup_address
            ]));
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return;
        }

        foreach ($data['order']['package'] as $kp => $p) {
            try {
                $package = \SergeR\Webasyst\CdekSDK\Type\OrderPackage::fromArray([
                    'number'   => $p['number'],
                    'bar_code' => $p['bar_code'],
                    'weight'   => new Mass((float)$p['weight'], 'kg'),
                    'size_a'   => new Length((int)$p['size_a'], 'cm'),
                    'size_b'   => new Length((int)$p['size_b'], 'cm'),
                    'size_c'   => new Length((int)$p['size_c'], 'cm'),
                ]);
            } catch (Exception $e) {
                //todo log
                continue;
            }

            foreach ($p['item'] as $i) {
                try {
                    $package->addItem(OrderItem::fromArray([
                        'ware_key' => $i['ware_key'],
                        'cost'     => $i['cost'],
                        'payment'  => $i['payment'],
                        'weight'   => new Mass((float)$i['weight'], 'kg'),
                        'amount'   => (int)$i['amount'],
                        'comment'  => $i['comment']
                    ]));
                } catch (Exception $e) {
                    //todo log
                    continue;
                }
            }

            $sdek_order->getPackage()->add($package);
        }

        $delivery_request->addOrder($sdek_order)->addCallCourier($courier_call);

        try {
            $result = $this->plugin->getOrdersApiClient()->deliveryRequest($delivery_request);
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return;
        }

        $sent_order = $result->getOrders()->findFirstByNumber(Hash::get($data, 'order.number'));
        if (!($sent_order instanceof OrderResult)) {
            $this->errors[] = 'В ответе сервера СДЭК не обнаружено информации о принятом заказе';
            return;
        }

        if ($sent_order->isError()) {
            $this->errors[] = $sent_order->getErrorMessage();
        }

        if ($result->getCalls()->isEmpty()) {
            $this->errors[] = 'В ответе сервера СДЭК не обнаружено информации о принятом вызове курьера';
        }

        $call = null;
        $err_call = null;
        /** @var CommonOrderResult $_c */
        foreach ($result->getCalls() as $_c) {
            if (!$_c->getNumber() && !$_c->getErrorCode()) {
                continue;
            }
            if ($_c->getErrorCode()) {
                $err_call = $_c;
            }
            if ($_c->getNumber()) {
                $call = $_c;
            }
        }

        if (!$call && $err_call) {
            $call = $err_call;
            $this->errors[] = $err_call->getErrorMessage();
        }

        if ($this->errors) {
            return;
        }

        $this->response['order'] = array('number' => $sdek_order->getNumber(), 'dispatch_number' => $sent_order->getDispatchNumber());
        $db_record = array(
            'call_date'       => $courier_call->getDate()->format('Y-m-d'),
            'time_beg'        => $courier_call->getTimeBeg(),
            'time_end'        => $courier_call->getTimeEnd(),
            'send_phone'      => $courier_call->getSendPhone(),
            'send_name'       => $courier_call->getSenderName(),
            'comment'         => '',
            'city_code'       => $courier_call->getSendCityCode(),
            'address_street'  => $courier_call->getSendAddress()->getStreet(),
            'address_house'   => $courier_call->getSendAddress()->getHouse(),
            'address_flat'    => $courier_call->getSendAddress()->getFlat(),
            'sent'            => date('Y-m-d H:i:s'),
            'dispatch_number' => $sent_order->getDispatchNumber(),
            'order_no'        => $sdek_order->getNumber(),
            'number_ttn'      => $delivery_request->getNumber()
        );
        try {
            $this->CourierCall->insert($db_record);
        } catch (Exception $e) {
            $this->plugin->helper->log(sprintf(
                "Ошибка записи данных об успеном заборе товаров ИМ в базу данных. Отправленные данные:\n%s\nОшибка: %s",
                var_export($db_record, true),
                $e->getMessage()
            ), true);
            $this->errors[] = 'Накладная создана, но возникла ошибка сохранения информации о заявке в базу данных плагина. Свяжитесь с разработчиком плагина.';
            return;
        }

        $profile = (array)Hash::get($data, 'profile', []);
        if (empty($profile)) {
            return;
        }

        $plugin_config = new shopSdekintPluginConfig();
        $plugin_config->setConsolidation(['city_id' => $courier_call->getSendCityCode(), 'pvz_id' => $sdek_order->getAddress()->getPvzCode()]);
        $plugin_config->saveSenderProfile(
            (string)Hash::get($profile, 'id'),
            array(
                'name'       => $courier_call->getSenderName(),
                'phone'      => $courier_call->getSendPhone(),
                'street'     => $courier_call->getSendAddress()->getStreet(),
                'house'      => $courier_call->getSendAddress()->getHouse(),
                'flat'       => $courier_call->getSendAddress()->getFlat(),
                'is_default' => (bool)Hash::get($profile, 'set_default')
            )
        );
        $plugin_config->save();
    }

    /**
     *
     */
    protected function pickupForm()
    {
        $view = wa('shop')->getView();
        $now = time();
        $plugin_config = new shopSdekintPluginConfig();
        $data = array(
            'order'    => array(
                'act_number' => $now,
                'number'     => $now,
                'from'       => $this->plugin->sender_city,
                'receiver'   => ['office_code' => ''],
                'address'    => ['street' => '', 'house' => '', 'office' => '']
            ),
            'packages' => array(
                array(
                    'number'  => '1',
                    'barcode' => '1',
                    'weight'  => '0.5',
                    'size'    => ['a' => 5, 'b' => 5, 'c' => 5],
                    'items'   => array(['sku' => '', 'name' => 'Товары интернет-магазина', 'price' => 0.0, 'payment' => 0.0, 'weight' => 0.5, 'amount' => 1])
                )
            )
        );

        $consolidation = $plugin_config->getConsolidation();
        $c_city_id = (int)Hash::get($consolidation, 'city_id');
        if ($c_city_id) {
            if ($c_city_id == $this->plugin->sender_city['id']) {
                $data = Hash::insert($data, 'order.receiver.office_code', (string)Hash::get($consolidation, 'pvz_id'));
            } else {
                try {
                    $city = (new shopSdekintPluginCityModel)->getCityByCode($c_city_id);
                    if ($city) {
                        $data = Hash::insert($data, 'order.from', ['id' => $c_city_id, 'name' => $city['name']]);
                        $data['order']['receiver']['office_code'] = (string)Hash::get($consolidation, 'pvz_id');
                    }
                } catch (Exception $e) {
                    //nothing
                }
            }
        }

        /**
         * @deprecated code block?
         */
        $stored = (array)$this->getRequest()->get('stored', [], waRequest::TYPE_ARRAY);
        if (($stored_city = ifempty($stored, 'city', []))) {
            if (ifempty($stored_city, 'value', 0)) {
                try {
                    $_city = (new shopSdekintPluginCityModel)->getCityByCode($stored_city['value']);
                    if (!empty($_city)) {
                        $data['order']['from']['id'] = $_city['code'];
                        $data['order']['from']['name'] = $_city['name'];
                    }
                } catch (waException $e) {
                    // nothing
                }
            }
        }

        $data['order']['address'] = (array)ifempty($stored, 'address', []) + $data['order']['address'];
        $data['order']['sender'] = array('name' => (string)ifset($stored, 'sender_name', ''), 'phone' => (string)ifset($stored, 'sender_phone', ''));

        $info = array('points' => []);
        if (($from_city_code = (int)Hash::get($data, 'order.from.id', 0))) {
            $info['points'] = (new shopSdekintPluginPvzModel)->find(['city_code' => $from_city_code]);
        }
        $info['senders'] = $plugin_config->getSenderProfiles();
        $info['default_sender'] = $this->plugin->helper->arrayFindFirst($info['senders'], function ($s) {
            return (bool)Hash::get($s, 'is_default');
        });
        $info['consolidation'] = $consolidation;

        $view->assign(compact('data', 'info'));
        $this->response['html'] = $view->fetch($this->getTemplate('Pickup'));
        $this->response['order'] = $data;
    }

    /**
     * @deprecated
     *
     * @return array
     */
    protected function getPagination()
    {
        $rows_on_page = (int)$this->getConfig()->getOption('products_per_page');
        $rows_on_page = $rows_on_page > 2 ? $rows_on_page : 30;
        $page = (int)$this->getRequest()->request('page', 0, waRequest::TYPE_INT);
        $page = max($page, 1);
        $offset = $rows_on_page * ($page - 1);

        return ['start' => $offset, 'count' => $rows_on_page, 'page' => $page];
    }

    /**
     * @param string $template
     * @return string
     */
    protected function getTemplate($template = '')
    {
        $path = 'plugins/sdekint/templates/actions/backend/Courier';
        if ($template) {
            $path .= '/' . $template . '.html';
        }
        return wa()->getAppPath($path, 'shop');
    }
}

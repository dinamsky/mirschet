<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2017
 * @license Webasyst
 */

use Psr\Log\LoggerInterface;
use SergeR\CakeUtility\Exception\XmlException;
use SergeR\CakeUtility\Hash;
use SergeR\CakeUtility\Xml;

/**
 *
 */
class shopSdekintPluginSdekOrderApi
{
    /** @var string */
    protected $auth;

    /** @var string */
    protected $key;

    /** @var waNet|shopSdekintPluginNet */
    protected $net;

    /** @var string */
    protected $api_host = 'https://integration.cdek.ru';

    /** @var LoggerInterface */
    protected $logger;

    /**
     * @param string|null $auth
     * @param string|null $key
     * @param waNet|null $net
     */
    function __construct($auth = null, $key = null, waNet $net = null)
    {
        if ($auth) {
            $this->auth = $auth;
        }

        if ($key) {
            $this->key = $key;
        }

        $this->net = $net ? $net : new shopSdekintPluginNet(['format' => waNet::FORMAT_XML, 'request_format' => 'default']);
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param array $calls
     * @param DateTimeImmutable $date
     * @return array
     * @throws waException
     * @throws Exception
     */
    public function callCourier(array $calls, DateTimeImmutable $date = null)
    {
        $url = $this->api_host . '/call_courier.php';
        if (!$date) {
            $date = new DateTimeImmutable();
        }

        if (empty($calls)) {
            throw new waException('Не передано данных для вызова курьера');
        }

        $default_call = array(
            'Date'         => null,
            'TimeBeg'      => null,
            'TimeEnd'      => null,
            'SendPhone'    => null,
            'SenderName'   => null,
            'Weight'       => 0,
            'SendCityCode' => null,
            'Comment'      => ''
        );
        $default_address = array('Street' => null, 'House' => null, 'Flat' => null);

        array_walk($calls, function (&$c) use ($default_call, $default_address) {
            $c = is_array($c) ? array_merge($default_call, $c) : $default_call;
            $c['Address'] = array_merge($default_address, ifempty($c, 'Address', []));
        });

        $request = array(
            '@Date'      => $date->format('Y-m-d'),
            '@Account'   => $this->auth,
            '@CallCount' => count($calls)
        );

        $request['@Secure'] = shopSdekintPluginHelper::getSecureKey($date, $this->key, 'Y-m-d');

        foreach ($calls as $call) {
            $request['Call'][] = array(
                '@Date'         => ifset($call, 'Date', ''),
                '@TimeBeg'      => ifset($call, 'TimeBeg', ''),
                '@TimeEnd'      => ifset($call, 'TimeEnd', ''),
                '@SendCityCode' => ifset($call, 'SendCityCode', ''),
                '@SendPhone'    => ifset($call, 'SendPhone', ''),
                '@SenderName'   => ifset($call, 'SenderName', ''),
                '@Weight'       => ifset($call, 'Weight', ''),
                '@Comment'      => ifset($call, 'Comment', ''),
                'Address'       => array(
                    '@Street' => ifset($call, 'Address', 'Street', ''),
                    '@House'  => ifset($call, 'Address', 'House', ''),
                    '@Flat'   => ifset($call, 'Address', 'Flat')
                )
            );
        }

        $response = $this->net
            ->setFormat(waNet::FORMAT_XML, 'default', 'text/xml,application/xml')
            ->query(
                $this->api_host . '/call_courier.php',
                ['xml_request' => Xml::build(['CallCourier' => $request])->asXML()],
                waNet::METHOD_POST
            );

        $result = array();

        $err_xml = $response->xpath('//response/CallCourier[@ErrorCode]');
        if (count($err_xml)) {
            foreach ($err_xml as $e) {
                $result['errors'][$e['ErrorCode']] = $e['Msg'];
            }
        }

        foreach ($response->xpath('//response/Call') as $c) {
            $result_call = array();
            if (isset($c['Date'])) {
                $result_call['date'] = (string)$c['Date'];
            }
            if (isset($c['ErrorCode'])) {
                $result_call['errors'][(string)$c['ErrorCode']] = isset($c['Msg']) ? (string)$c['Msg'] : (string)$c['ErrorCode'];
            }
            if (isset($c['Number'])) {
                $result_call['number'] = (string)$c['Number'];
            }

            if ($result_call) {
                $result['calls'][] = $result_call;
            }
        }

        return $result;
    }

    /**
     * @param array $orders
     * @param null|string|DateTimeImmutable $doc_date
     * @param string $doc_number
     * @return array|SimpleXMLElement|string
     * @throws waException
     *
     *
     *
     */
    public function deleteOrders(array $orders, $doc_date = null, $doc_number = '1')
    {
        /*
                array (
                    'response' =>
                        array (
                            'DeleteRequest' =>
                                array (
                                    '@Msg' => 'Удалено заказов:1',
                                    'Order' =>
                                        array (
                                            '@Number' => '#1065',
                                            '@Msg' => 'Заказ удален',
                                        ),
                                ),
                        ),
                )
                    */

        /*
                array (
                    'response' =>
                        array (
                            'DeleteRequest' =>
                                array (
                                    '@Msg' => 'Удалено заказов:0',
                                    '@ErrMsg' => 'Не удалено заказов:1',
                                    'Order' =>
                                        array (
                                            '@Number' => '#1065',
                                            '@ErrorCode' => 'ERR_ORDER_NOTFIND',
                                            '@Msg' => 'Заказ не найден в базе СДЭК: Number=#1065',
                                        ),
                                ),
                        ),
                )
                    */

        /*        array (
                    'response' =>
                        array (
                            'DeleteRequest' =>
                                array (
                                    '@Number' => '1',
                                    '@ErrorCode' => 'ERR_AUTH',
                                    '@Msg' => 'Интернет-магазин не идентифицирован: Account=9f2e246bc8227401d2064ab19dfa27cf, Secure=51130df5e5a475bb7f64229b48108bee',
                                ),
                        ),
                )
                    */
        if (!$orders) {
            throw new waException('Не указаны номера заказов для удаления');
        }

        try {
            if (!($doc_date instanceof DateTimeImmutable)) {
                $doc_date = new DateTimeImmutable($doc_date);
            }
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->error('[{class}:{method}]Получено исключение при формировании даты: {err}', ['class' => __CLASS__, 'method' => __METHOD__, 'err' => $e->getMessage()]);
            }
            throw new waException($e->getMessage());
        }

        $request = array(
            '@Number'     => $doc_number,
            '@Date'       => $doc_date->format('Y-m-d'),
            '@Account'    => $this->auth,
            '@OrderCount' => count($orders)
        );
        $request['@Secure'] = shopSdekintPluginHelper::getSecureKey($request['@Date'], $this->key, 'Y-m-d');

        foreach ($orders as $o) {
            if (!is_string($o) || empty($o)) {
                throw new waException('Передан неверный номер заказа для удаления');
            }
            $request['Order'][] = ['@Number' => $o];
        }

        try {
            $xml_request = Xml::build(['DeleteRequest' => $request])->asXML();
        } catch (XmlException $e) {
            if ($this->logger) {
                $this->logger->error('Ошибка формирования XML для удаления заказа: {err}', ['err' => $e->getMessage()]);
            }
            throw new waException($e->getMessage());
        }

        $xml_response = $this->net
            ->setFormat(waNet::FORMAT_XML, 'default', 'text/xml,application/xml')
            ->query($this->api_host . '/delete_orders.php', ['xml_request' => $xml_request], waNet::METHOD_POST);

        try {
            $_response = Xml::toArray($xml_response);
            $response = Hash::get($_response, 'response.DeleteRequest', null);

            // Fix CDEK API bug: different answer formats
            if ($response === null) {
                $response = Hash::get($_response, 'response.Order', null);
                if ($response) {
                    $response = ['Order' => $response];
                }
            }
        } catch (XmlException $e) {
            if ($this->logger) {
                $this->logger->error('Ошибка декодирования XML с ответом на удаление заказа: {err}', ['err' => $e->getMessage()]);
            }
            throw new waException($e->getMessage());
        }

        if ($this->logger) {
            $this->logger->debug("Метод:delete_orders.\nОтправлен запрос:\n{req}\nПолучен ответ:\n{res}", ['req' => $xml_request, 'res' => $xml_response->asXML()]);
        }

        if (empty($response)) {
            throw new waException('Ответ сервера пуст');
        }

        return $response;
    }

    public function infoReport()
    {

    }

    /**
     * @param array $orders
     * @param int $doc_number
     * @param null|string|DateTimeImmutable|DateTime $doc_date
     * @param array $courier_call
     * @return array
     * @throws waException
     * @throws Exception
     */
    public function newOrders(array $orders, $doc_number = 1, $doc_date = null, array $courier_call = null)
    {
        $tax_percents = ['VAT0' => 0, 'VAT10' => 10, 'VAT18' => 18, 'VAT20' => 20];
        $shopDimension = shopDimension::getInstance();

        if (!($doc_date instanceof DateTimeInterface)) {
            $doc_date = new DateTimeImmutable($doc_date);
        }

        $request = array(
            '@Number'     => $doc_number,
            '@Date'       => $doc_date->format('Y-m-d'),
            '@OrderCount' => count($orders),
            '@Account'    => $this->auth
        );
        $request['@Secure'] = shopSdekintPluginHelper::getSecureKey($request['@Date'], $this->key, 'Y-m-d');

        foreach ($orders as $o) {
            $order = array(
                '@Number'            => $o['id'],
                '@SendCityCode'      => $o['origin']['address']['city']['id'],
                '@RecCityCode'       => $o['destination']['address']['city']['id'],
                '@RecipientName'     => $o['destination']['contact']['name'],
                '@Phone'             => $o['destination']['contact']['phone'],
                '@TariffTypeCode'    => $o['delivery']['tariff'],
                '@RecipientCurrency' => $o['payment']['currency'],
                '@ItemsCurrency'     => ifset($o, 'payment', 'contract_currency', 'RUB')
            );
            if (!empty($o['origin']['sender']['name'])) {
                $order['@SellerName'] = $o['origin']['sender']['name'];
            }
            if (!empty($o['destination']['contact']['email'])) {
                $order['@RecipientEmail'] = $o['destination']['contact']['email'];
            }
            if (!empty($o['comment'])) {
                $order['@Comment'] = $o['comment'];
            }

            // Стоимость доставки добавляем только для заказов с наложенным платежом
            // Если добавляем стоимость доставки, то для доставки по РФ также добаляем НДС
            if (!$o['payment']['paid'] && (bool)$o['delivery']['cost']) {
                $order['@DeliveryRecipientCost'] = sprintf("%0.2F", $o['delivery']['cost']);
                if ($o['destination']['address']['country'] === 'rus') {
                    $t = ifset($o, 'delivery', 'tax_percent', 'VATX');
                    $order['@DeliveryRecipientVATRate'] = $t;
                    if (array_key_exists($t, $tax_percents)) {
                        $percent = $tax_percents[$t];
                        $tax_value = round($percent > 0 ? $o['delivery']['cost'] - $o['delivery']['cost'] / (100 + $percent) * 100 : 0, 2);
                        $order['@DeliveryRecipientVATSum'] = sprintf('%0.2F', $tax_value);
                    }
                }
            }

            // Адрес доставки или код ПВЗ
            switch ($o['destination']['type']) {
                case 'to-door' :
                    $order['Address'] = array(
                        '@Street' => $o['destination']['address']['street'],
                        '@House'  => $o['destination']['address']['house'],
                        '@Flat'   => ifempty($o, 'destination', 'address', 'flat', '')
                    );
                    break;
                case 'to-stock':
                    $order['Address'] = ['@PvzCode' => ifempty($o, 'destination', 'address', 'stock_id', '')];
                    break;
            }

            // Добавление упаковок с товарами
            foreach ($o['items']['packages'] as $pack) {
                $products = array_filter($o['items']['products'], function ($p) use ($pack) {
                    return $p['package'] === $pack['id'];
                });
                if (!$products) {
                    continue;
                }
                $package = array(
                    '@Number'  => $pack['number'],
                    '@Barcode' => ifempty($pack, 'barcode', $pack['number']),
                    '@Weight'  => intval($shopDimension->convert($pack['weight'], 'weight', 'g', 'kg')),
                    '@SizeA'   => (int)round(Hash::get($pack, 'size.length')),
                    '@SizeB'   => (int)round(Hash::get($pack, 'size.width')),
                    '@SizeC'   => (int)round(Hash::get($pack, 'size.height'))
                );

                foreach ($products as $p) {
                    $item = array(
                        '@WareKey' => $p['sku'],
                        '@Comment' => $p['name'],
                        '@Cost'    => sprintf('%0.2F', $p['price']),
                        '@Payment' => $o['payment']['paid'] ? '0.00' : sprintf('%0.2F', $p['cost']),
                        '@Weight'  => (int)round($shopDimension->convert($p['weight'], 'weight', 'g', 'kg')),
                        '@Amount'  => (int)$p['quantity']
                    );
                    if (!$o['payment']['paid'] && (Hash::get($o, 'destination.address.country') === 'rus')) {
                        $t = empty($p['tax_percent']) ? 'VATX' : $p['tax_percent'];
                        $item['@PaymentVATRate'] = $t;
                        if (array_key_exists($t, $tax_percents)) {
                            $percent = $tax_percents[$t];
                            $tax_value = round($percent > 0 ? $p['cost'] - $p['cost'] / (100 + $percent) * 100 : 0, 2);
                            $item['@PaymentVATSum'] = sprintf('%0.2F', $tax_value);
                        }
                    }

                    $package['Item'][] = $item;
                }

                $order['Package'][] = $package;
            }

            // Услуги
            $services = (array)ifempty($o, 'services', []);
            foreach ($services as $s) {
                $order['AddService'][] = ['@ServiceCode' => $s];
            }

            // Расписание доставки, если есть и доставка курьером
            $sch = (array)ifempty($o, 'schedule', []);
            if (!empty($sch) && ($o['destination']['type'] === 'to-door')) {
                foreach ($sch as $s) {
                    $attempt = array(
                        '@ID'      => 1,
                        '@Date'    => $s['date'],
                        '@TimeBeg' => $s['from'],
                        '@TimeEnd' => $s['to']
                    );
                    if (!empty($s['name'])) {
                        $attempt['@RecipientName'] = $s['name'];
                    }
                    if (!empty($s['phone'])) {
                        $attempt['@Phone'] = $s['phone'];
                    }
                    if (!empty($s['address'])) {
                        if (!empty($s['address']['street'])) {
                            $attempt['Address']['@Street'] = $s['address']['street'];
                        }
                        if (!empty($s['address']['house'])) {
                            $attempt['Address']['@House'] = $s['address']['house'];
                        }
                        if (!empty($s['address']['flat'])) {
                            $attempt['Address']['@Flat'] = $s['address']['flat'];
                        }
                    }
                    $order['Schedule']['Attempt'][] = $attempt;
                }
            }

            $request['Order'][] = $order;
        }

        if (!empty($courier_call)) {
            $call = array(
                '@Date'         => $courier_call['date'],
                '@TimeBeg'      => $courier_call['time_beg'],
                '@TimeEnd'      => $courier_call['time_end'],
                '@SendCityCode' => $courier_call['send_city_code'],
                '@SendPhone'    => $courier_call['send_phone'],
                '@SenderName'   => $courier_call['sender_name'],
                'SendAddress'   => array(
                    '@Street' => (string)Hash::get($courier_call, 'address.street'),
                    '@House'  => (string)Hash::get($courier_call, 'address.house'),
                    '@Flat'   => (string)Hash::get($courier_call, 'address.flat')
                )
            );

            if (!empty($courier_call['lunch_time_beg']) && !empty($courier_call['lunch_time_end'])) {
                $call += array(
                    '@LunchBeg' => $courier_call['lunch_time_beg'],
                    '@LunchEnd' => $courier_call['lunch_time_end']
                );
            }

            if (!empty($courier_call['comment'])) {
                $call['@Comment'] = $courier_call['comment'];
            }

            $request['CallCourier']['Call'][] = $call;
        }

        $url = $this->api_host . '/new_orders.php';

        $this->net->setFormat(waNet::FORMAT_XML, 'default', 'text/xml,application/xml');
        try {
            $xml_req = Xml::build(['DeliveryRequest' => $request])->asXML();
        } catch (XmlException $e) {
            if ($this->logger) {
                $this->logger->error('Ошибка построения XML: {err}', ['err' => $e->getMessage()]);
            }
            throw new waException($e->getMessage());
        }

        $response_xml = $this->net->query($url, ['xml_request' => $xml_req], waNet::METHOD_POST);

        try {
            $response = Xml::toArray($response_xml);
        } catch (XmlException $e) {
            if ($this->logger) {
                $this->logger->error('Ошибка разбора ответа: {err}', ['err' => $e->getMessage()]);
            }
            throw new waException($e->getMessage());
        }

        if ($this->logger) {
            $this->logger->debug("Метод:new_orders.\nОтправлен запрос:\n{req}\nПолучен ответ:\n{res}", ['req' => $xml_req, 'res' => $response_xml->asXML()]);
        }

        $result = array();
        foreach (Hash::extract($response, 'response.Order.{n}[@Number]') as $r) {
            $number = $r['@Number'];
            $result[$number]['id'] = $number;

            if (!empty($r['@DispatchNumber'])) {
                $result[$number]['DispatchNumber'] = (string)$r['@DispatchNumber'];
            } else {
                $result[$number]['errors'][] = [((bool)$r['@ErrorCode'] ? (string)$r['@ErrorCode'] : 'UNKNOWN'), ((bool)$r['@Msg'] ? (string)$r['@Msg'] : '')];
            }
            $result[$number]['status'] = empty($result[$number]['errors']) ? 'ok' : 'fail';
        }

        return $result;
    }

    /**
     * @param SimpleXMLElement $schedule
     * @return SimpleXMLElement
     * @throws waException
     */
    public function newSchedule(SimpleXMLElement $schedule)
    {
        $url = $this->api_host . '/new_orders.php';

        /** @var SimpleXMLElement $result */
        $result = $this->net->query($url, ['xml_request' => $schedule->asXML()]);

        return $result;
    }

    /**
     * @param array $orders
     * @param null $date
     * @param int $copies
     * @return array|SimpleXMLElement|string
     * @throws waException
     * @throws XmlException
     * @throws Exception
     */
    public function ordersPrint(array $orders, $date = null, $copies = 4)
    {
        if (!$orders) {
            throw new waException('Не указаны номера накладных для печати');
        }

        if (!($date instanceof DateTimeImmutable)) {
            $date = new DateTimeImmutable($date);
        }

        $request['@Date'] = $date->format('Y-m-d');
        $request['@OrderCount'] = count($orders);
        $request['@CopyCount'] = $copies;

        foreach ($orders as $o) {
            $request['Order'][] = array(
                '@DispatchNumber' => $o
            );
        }

        $request['@Account'] = $this->auth;
        $request['@Secure'] = shopSdekintPluginHelper::getSecureKey($request['@Date'], $this->key, 'Y-m-d');

        $response = $this->net
            ->setFormat(waNet::FORMAT_RAW, 'default', '*/*')
            ->query(
                $this->api_host . '/orders_print.php',
                ['xml_request' => Xml::build(['OrdersPrint' => $request])->asXML()],
                waNet::METHOD_POST
            );

        $type = $this->net->getResponseHeader('Content-Type');
        if ($type == 'application/pdf') {
            return $response;
        }

        if (substr($type, 0, 8) == 'text/xml') {
            $xml_err = new SimpleXMLElement($response);
            $msgs = array();
            foreach ($xml_err->xpath('//response/Order') as $err) {
                $msgs[] = $err['Msg'];
            }
            throw new waException(implode("\n", $msgs));
        }

        throw new waException(sprintf('Не удалось распознать тип ответа: %s', $type));
    }

    /**
     * @param array $orders
     * @param DateTimeImmutable $date
     * @param string $format
     * @param int $copy_count
     * @return string
     * @throws XmlException
     * @throws waException
     */
    public function ordersPackagesPrint(array $orders, DateTimeImmutable $date, $format = 'A6', $copy_count = 1)
    {
        $orders_count = count($orders);
        $request = array('OrdersPackagesPrint' => array(
            '@Date'        => $date->format('Y-m-d'),
            '@Account'     => $this->auth,
            '@Secure'      => shopSdekintPluginHelper::getSecureKey($date->format('Y-m-d'), $this->key, 'Y-m-d'),
            '@OrderCount'  => $orders_count,
            '@CopyCount'   => $copy_count,
            '@PrintFormat' => $format,
            'Order'        => array()
        ));

        foreach ($orders as $order) {
            $request['OrdersPackagesPrint']['Order'][] = ['@DispatchNumber' => $order];
        }

        $response = $this->net
            ->setFormat(waNet::FORMAT_RAW, 'default', '*/*')
            ->query(
                $this->api_host . '/ordersPackagesPrint',
                ['xml_request' => Xml::build($request)->asXML()],
                waNet::METHOD_POST
            );

        $type = $this->net->getResponseHeader('Content-Type');
        if ($type == 'application/pdf') {
            return $response;
        }

        if (substr($type, 0, 8) == 'text/xml') {
            $xml_err = new SimpleXMLElement($response);
            $msgs = array();
            foreach ($xml_err->xpath('//response/Order') as $err) {
                $msgs[] = $err['Msg'];
            }
            throw new waException(implode("\n", $msgs));
        }

        throw new waException(sprintf('Не удалось распознать тип ответа: %s', $type));
    }

    /**
     * Возвращает список ПВЗ
     *
     * @param array $sdek_params
     * @return SimpleXMLElement
     * @throws waException
     */
    public function pvzList(array $sdek_params = array())
    {
        $url = $this->api_host . '/pvzlist.php';

        /** @var SimpleXMLElement */
        $response = $this->net->query($url, $sdek_params, waNet::METHOD_GET);

        if (!$response->Pvz) {
            throw new waException('Ошибка получения списка ПВЗ с сервера СДЭК');
        }

        return $response->Pvz;
    }


    /**
     * @param array $orders
     * @param array $options
     * @param bool $raw
     * @return array|SimpleXMLElement
     * @throws waException
     */
    public function statusReport(array $orders, array $options = [], $raw = false)
    {
        $orders = array_filter($orders, function ($o) {
            return is_scalar($o) || (is_array($o) && !empty($o['number']) && !empty($o['date']));
        });

        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" ?><StatusReport />');

        if (empty($orders)) {
            return $raw ? $xml : $orders;
        }

        if ($raw) {
            $this->net->setFormat(waNet::FORMAT_RAW, 'default', 'text/xml,application/xml');
        } else {
            $this->net->setFormat(waNet::FORMAT_XML, 'default', 'text/xml,application/xml');
        }

        $options += ['show_history' => false, 'show_return' => false, 'show_return_history' => false, 'change_period' => array()];
        $options = shopSdekintPluginHelper::typecastScalarArrayValues($options, ['show_history' => 'bool', 'show_return' => 'bool', 'show_return_history' => 'bool']);

        $xml['Date'] = (new DateTimeImmutable)->format('Y-m-d');
        $xml['Account'] = $this->auth;
        $xml['Secure'] = shopSdekintPluginHelper::getSecureKey((string)$xml['Date'], $this->key, 'Y-m-d');
        $xml['ShowHistory'] = intval($options['show_history']);
        $xml['ShowReturnOrder'] = intval($options['show_return']);
        $xml['ShowReturnOrderHistory'] = intval($options['show_return_history']);

        if ($options['change_period'] && !empty($options['change_period']['from']) && !empty($options['change_period']['to'])) {
            $cp = $xml->addChild('ChangePeriod');
            $cp['DateFirst'] = $options['change_period']['from'];
            $cp['DateLast'] = $options['change_period']['to'];
        }

        foreach ($orders as $o) {
            $order = $xml->addChild('Order');
            if (is_scalar($o)) {
                $order['DispatchNumber'] = $o;
            } else {
                $order['Number'] = $o['number'];
                $order['Date'] = $o['date'];
            }
        }

        $response = $this->net->query($this->api_host . '/status_report_h.php', ['xml_request' => $xml->asXML()], waNet::METHOD_POST);
        if ($raw) {
            $use_errors = libxml_use_internal_errors(true);
            $result = new SimpleXMLElement($response);
            $errors = array();
            if ($result === false) {
                $errors = libxml_get_errors();
                libxml_clear_errors();
            }
            libxml_use_internal_errors($use_errors);
            if ($errors) {
                throw new waException('[API:statusReport] Cannot parse XML response');
            }

            return $result;
        }

        $result = array(
            'from'   => (string)$response['DateFirst'],
            'to'     => (string)$response['DateLast'],
            'orders' => []
        );

        /** @var SimpleXMLElement $o */
        foreach ($response->Order as $o) {
            $order = array(
                'act_number'      => (string)$o['ActNumber'],
                'number'          => (string)$o['Number'],
                'dispatch_number' => (string)$o['DispatchNumber']
            );

            if ($o['DeliveryDate']) {
                $order['delivery_date'] = (string)$o['DeliveryDate'];
            }
            if ($o['RecipientName']) {
                $order['recipient_name'] = (string)$o['RecipientName'];
            }
            if ($o['ReturnDispatchNumber']) {
                $order['return_dispatch_number'] = (string)$o['ReturnDispatchNumber'];
            }
            $order['status'] = array(
                'date'        => (string)$o->Status['Date'],
                'code'        => (int)$o->Status['Code'],
                'description' => (string)$o->Status['Description'],
                'city_code'   => (int)$o->Status['CityCode'],
                'city_name'   => (string)$o->Status['CityName'],
            );

            // История статусов будет, только если мы ее специально попросили
            $order['history'] = array();
            if ((bool)$o->Status->State) {
                foreach ($o->Status->State as $s) {
                    $order['history'][] = array(
                        'date'        => (string)$s['Date'],
                        'code'        => (int)$s['Code'],
                        'description' => (string)$s['Description'],
                        'city_code'   => (int)$s['CityCode'],
                        'city_name'   => (string)$s['CityName'],
                    );
                }
                usort($order['history'], [$this, 'dateFieldComparer']);
            }

            // текущий дополнительный статус, но я не уверен, что он всегда есть, хотя документация уверждает, что всегда
            $order['reason'] = array();
            if ($o->Reason) {
                $order['reason'] = array(
                    'date'        => (string)$o->Reason['Date'],
                    'code'        => (string)$o->Reason['Code'],
                    'description' => (string)$o->Reason['Description']
                );
            }

            // текущая причина задержки. В документации опять помечена, как обязательная, но меня опять мучают сомнения
            $order['delay_reason'] = array();
            if ($o->DelayReason) {
                $order['delay_reason'] = array(
                    'date'        => (string)$o->DelayReason['Date'],
                    'code'        => (string)$o->DelayReason['Code'],
                    'description' => (string)$o->DelayReason['Description']
                );
                $order['delay_reason']['history'] = array();
                foreach ($o->DelayReason as $dr) {
                    $hi = ['date' => (string)$dr['Date']];
                    if ((bool)$dr['Code']) {
                        $hi['code'] = (string)$dr['Code'];
                    }
                    if ((bool)$dr['Description']) {
                        $hi['description'] = (string)$dr['Description'];
                    }
                    $order['delay_reason']['history'][] = $hi;
                }
                usort($order['delay_reason']['history'], [$this, 'dateFieldComparer']);
            }

            // Package и Item присутствуют только при полном вручении заказа (в конечном статусе «Вручен») и
            // при частичной доставке в конечном статусе «Не вручен» и
            // дополнительном статусе «Частичная доставка»
            $order['packages'] = array();
            if ($o->Package) {
                foreach ($o->Package as $p) {
                    $pkg = array('number' => (string)$p['Number'], 'items' => []);
                    if ($p->Item) {
                        foreach ($p->Item as $i) {
                            $pkg['items'][] = array('sku' => (string)$i['WareKey'], 'delivered_qty' => (int)$i['DelivAmount']);
                        }
                    }
                }
            }

            // Attempt присутсвует только в случае, если по условиям договора, ИМ самостоятельно предоставляет расписание
            // доставки для СДЭК. Тэг содержит данные по неудачным попыткам доставки в разрезе
            // предоставленного ИМ расписания доставки
            $order['attempt'] = array();
            if ((bool)$o->Attempt) {
                foreach ($o->Attempt as $a) {
                    $order['attempt'][] = array(
                        'id'          => (int)$a['ID'],
                        'code'        => (int)$a['ScheduleCode'],
                        'description' => (string)$a['ScheduleDescription']
                    );
                }
            }

            // История прозвонов получателя
            $order['calls'] = array();
            if ($o->Call) {
                $order['calls']['good'] = array();
                $order['calls']['fail'] = array();
                $order['calls']['delayed'] = array();
                if ($o->Call->CallGood) {
                    foreach ($o->Call->CallGood->Good as $c) {
                        $order['calls']['good'][] = ['date' => (string)$c['Date'], 'date_deliv' => (string)$c['DateDeliv']];
                    }
                    usort($order['calls']['good'], [$this, 'dateFieldComparer']);
                }
                if ($o->Call->CallFail) {
                    foreach ($o->Call->CallFail->Fail as $c) {
                        $order['calls']['fail'][] = array(
                            'date'        => (string)$c['Date'],
                            'code'        => (int)$c['ReasonCode'],
                            'description' => (string)$c['ReasonDescription']
                        );
                    }
                    usort($order['calls']['fail'], [$this, 'dateFieldComparer']);
                }
                if ($o->Call->CallDelay) {
                    foreach ($o->Call->CallDelay->Delay as $c) {
                        $order['calls']['delay'][] = ['date' => (string)$c['Date'], 'next' => (string)$c['DateNext']];
                    }
                    usort($order['calls']['delayed'], [$this, 'dateFieldComparer']);
                }
            }

            if ($o->ReturnOrder) {
                $order['return'] = array(
                    'return_number'   => (string)$o->ReturnOrder['ReturnOrderNumber'],
                    'number'          => (string)$o->ReturnOrder['Number'],
                    'dispatch_number' => (string)$o->ReturnOrder['DispatchNumber']
                );
                if ($o->ReturnOrder['DeliveryDate']) {
                    $order['return']['delivery_date'] = (string)$o->ReturnOrder['DeliveryDate'];
                }
                if ($o->ReturnOrder['RecipientName']) {
                    $order['return']['recipient_name'] = (string)$o->ReturnOrder['RecipientName'];
                }

                $order['return']['status'] = array('history' => []);
                if ($o->ReturnOrder->Status) {
                    $order['return']['status'] += array(
                        'date'        => (string)$o->ReturnOrder->Status['Date'],
                        'code'        => (int)$o->ReturnOrder->Status['Code'],
                        'description' => (string)$o->ReturnOrder->Status['Description'],
                        'city_code'   => (int)$o->ReturnOrder->Status['CityCode'],
                        'city_name'   => (string)$o->ReturnOrder->Status['CityName']
                    );

                    $order['return']['status']['history'] = array();
                    if ($o->ReturnOrder->Status->State) {
                        foreach ($o->ReturnOrder->Status->state as $s) {
                            $order['return']['status']['history'][] = array(
                                'date'        => (string)$s['Date'],
                                'code'        => (int)$s['Code'],
                                'description' => (string)$s['Description'],
                                'city_code'   => (int)$s['CityCode'],
                                'city_name'   => (string)$s['CityName']
                            );
                        }
                        usort($order['return']['status']['history'], [$this, 'dateFieldComparer']);
                    }
                }

                if ($o->ReturnOrder->Reason) {
                    $order['return']['reason'] = array(
                        'date' => (string)$o->ReturnOrder->Reason['Date']
                    );
                    if ($o->ReturnOrder->Reason['Code']) {
                        $order['return']['reason']['code'] = (int)$o->ReturnOrder->Reason['Code'];
                    }
                    if ($o->ReturnOrder->Reason['Description']) {
                        $order['return']['reason']['description'] = (string)$o->ReturnOrder->Reason['Description'];
                    }
                }

                if ($o->ReturnOrder->DelayReason) {
                    $order['return']['delay_reason'] = array(
                        'date' => (string)$o->ReturnOrder->DelayReason['Date']
                    );
                    if ($o->ReturnOrder->DelayReason['Code']) {
                        $order['return']['delay_reason']['code'] = (int)$o->ReturnOrder->DelayReason['Code'];
                    }
                    if ($o->ReturnOrder->DelayReason['Description']) {
                        $order['return']['delay_reason']['description'] = (string)$o->ReturnOrder->DelayReason['Description'];
                    }
                }
            }

            $result['orders'][$order['dispatch_number']] = $order;
        }

        return $result;
    }

    public static function findMainStateName($id)
    {
        $states = self::listMainStates();
        if (array_key_exists($id, $states)) {
            return $states[$id];
        }

        return '';
    }

    /**
     * Возвращает список основных статусов заказа в учетной системе СДЭК
     *
     * @return array
     */
    public static function listMainStates()
    {
        return array(
            3  => 'Принят на склад отправителя',
            4  => 'Вручен',
            5  => 'Не вручен',
            6  => 'Выдан на отправку в городе-отправителе',
            7  => 'Сдан перевозчику в городе-отправителе',
            8  => 'Отправлен в город-получатель',
            9  => 'Встречен в город-получателе',
            10 => 'Принят на склад доставки',
            11 => 'Выдан на доставку',
            12 => 'Принят на склад до востребования',
            13 => 'Принят на склад транзита',
            16 => 'Возвращен на склад отправителя',
            17 => 'Возвращен на склад транзита',
            18 => 'Возвращен на склад доставки',
            19 => 'Выдан на отправку в городе-транзите',
            20 => 'Сдан перевозчику в городе-транзите',
            21 => 'Отправлен в город-транзит',
            22 => 'Встречен в город-транзите',

        );
    }

    /**
     * @param null|string $ua
     */
    public function setUserAgent($ua = null)
    {
        if ($this->net instanceof waNet) {
            $this->net->userAgent($ua);
        }
    }

    /**
     * @param $a
     * @param $b
     * @return int
     * @throws Exception
     */
    private function dateFieldComparer($a, $b)
    {
        $aDate = new DateTimeImmutable($a['date']);
        $bDate = new DateTimeImmutable($b['date']);

        return $aDate === $bDate ? 0 : ($aDate > $bDate ? 1 : -1);
    }
}

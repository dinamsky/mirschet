<?php

use SergeR\CakeUtility\Hash;

/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @version
 * @copyright Serge Rodovnichenko, 2018
 * @license
 */

class shopSdekintPluginLegacyWidgetController extends waJsonController
{
    /** @var shopSdekintPlugin */
    protected $plugin;

    public function execute()
    {
        try {
            $this->plugin = wa('shop')->getPlugin('sdekint');
        } catch (waException $e) {
            $this->setError($e->getMessage());
            return;
        }

        $action = $this->getRequest()->request('isdek_action', '', waRequest::TYPE_STRING);
        if (empty($action)) {
            $this->setError('Bad request');
            return;
        }

        $action .= 'WidgetAction';

        if (!method_exists($this, $action)) {
            $this->setError('Bad request');
            return;
        }

        try {
            $this->$action();
        } catch (waException $e) {
            $this->setError($e->getMessage());
        }
    }

    /**
     * Handler for `getPVZ` request
     */
    public function getPVZWidgetAction()
    {
        $Pvz = new shopSdekintPluginPvzModel();
        $conditions = array();
        $country = $this->getRequest()->request('country', '', waRequest::TYPE_STRING_TRIM);
        if (!empty($country)) {
            $conditions['country_name_sdek'] = $country;
        }

        $points = $Pvz->findQuery($conditions);

        $arList = array('PVZ' => array(), 'CITY' => array(), 'REGIONS' => array(), 'CITYFULL' => array(), 'COUNTRIES' => array());

        foreach ($points as $point) {
            $cityCode = $point['city_code'];
            $type = $point['point_type'];
            $city = $point["city"];
            if (strpos($city, '(') !== false)
                $city = trim(substr($city, 0, strpos($city, '(')));
            if (strpos($city, ',') !== false)
                $city = trim(substr($city, 0, strpos($city, ',')));
            $code = $point["code"];

            $arList[$type][$cityCode][$code] = array(
                'Name'           => $point['name'],
                'WorkTime'       => $point['work_time'],
                'Address'        => $point['address'],
                'Phone'          => $point['phone'],
                'Note'           => $point['note'],
                'cX'             => $point['coord_x'],
                'cY'             => $point['coord_y'],
                'Dressing'       => $point['dressing_room'] ? 'есть' : 'нет',
                'Cash'           => $point['have_cashless'] ? 'есть' : 'нет',
                'Station'        => $point['nearest_station'],
                'Site'           => $point['site'],
                'Metro'          => $point['metro'],
                'AddressComment' => $point['address_comment'],
            );

            if (!is_null($point['max_weight']) || !is_null($point['min_weight'])) {
                $arList[$type][$cityCode][$code]['WeightLim'] = array(
                    'MIN' => shopSdekintPluginHelper::toFloat($point['min_weight']),
                    'MAX' => shopSdekintPluginHelper::toFloat($point['max_weight'])
                );
            }

            $raw_data = array();
            if (!empty($point['raw_data'])) {
                $raw_data = shopHelper::jsonDecode($point['raw_data'], true);
            }

            $arImgs = array();
            foreach ((array)ifset($raw_data, 'office_image', array()) as $img) {
                if (strstr($_tmpUrl = (string)$img[0], 'http') === false) {
                    continue;
                }
                $arImgs[] = (string)$img[0];
            }

            if (count($arImgs = array_filter($arImgs))) {
                $arList[$type][$cityCode][$code]['Picture'] = $arImgs;
            }
            $ohg = (array)ifempty($raw_data, 'office_how_go', array());
            if (count($ohg)) {
                $arList[$type][$cityCode][$code]['Path'] = $ohg[0];
            }

            if (!array_key_exists($cityCode, $arList['CITY'])) {
                $arList['CITY'][$cityCode] = $city;
                $arList['CITYFULL'][$cityCode] = $point['country_name_sdek'] . ' ' . $point['region_name_sdek'] . ' ' . $city;
                $arList['REGIONS'][$cityCode] = implode(', ', array_filter(array($point['region_name_sdek'], $point['country_name_sdek'])));
            }
        }

        krsort($arList['PVZ']);

        $this->response['pvz'] = $arList;

    }

    /**
     * Handler for `getLang` request
     */
    public function getLangWidgetAction()
    {
        $this->response['LANG'] = array(
            'YOURCITY'   => 'Ваш город',
            'COURIER'    => 'Курьер',
            'PICKUP'     => 'Самовывоз',
            'TERM'       => 'Срок',
            'PRICE'      => 'Стоимость',
            'DAY'        => 'дн.',
            'RUB'        => 'руб.',
            'NODELIV'    => 'Нет доставки',
            'CITYSEATCH' => 'Поиск города',
            'CITYSEARCH' => 'Поиск города',
            'ALL'        => 'Все',
            'PVZ'        => 'Пункты выдачи',
            'MOSCOW'     => 'Москва',
            'RUSSIA'     => 'Россия',
            'COUNTING'   => 'Идет расчет',

            'NO_AVAIL'          => 'Нет доступных способов доставки',
            'CHOOSE_TYPE_AVAIL' => 'Выберите способ доставки',
            'CHOOSE_OTHER_CITY' => 'Выберите другой населенный пункт',

            'EST' => 'есть',

            'L_ADDRESS' => 'Адрес пункта выдачи заказов',
            'L_TIME'    => 'Время работы',
            'L_WAY'     => 'Как к нам проехать',
            'L_CHOOSE'  => 'Выбрать',

            'H_LIST'    => 'Список пунктов выдачи заказов',
            'H_PROFILE' => 'Способ доставки',
            'H_CASH'    => 'Расчет картой',
            'H_DRESS'   => 'С примеркой',
            'H_SUPPORT' => 'Служба поддержки',
        );
    }

    /**
     * Handler for `getLang` request
     *
     * @todo cache
     * @todo use our db instead of remote service
     */
    public function getCityWidgetAction()
    {
        $term = $this->getRequest()->request('city', '', waRequest::TYPE_STRING_TRIM);

        try {
            $city = $this->suggestCity($term);
        } catch (waException $e) {
            $this->setError($e->getMessage());
            return;
        }

        $this->response = array(
            'id'      => $city['id'],
            'city'    => $city['cityName'],
            'region'  => $city['regionName'],
            'country' => $city['countryName']
        );
    }

    public function calcWidgetAction()
    {
        $shipment = (array)$this->getRequest()->request('shipment', [], waRequest::TYPE_ARRAY);
        $config_id = (int)$this->getRequest()->param('config_id');

        if (!$config_id) {
            $this->setError('Не указан идентификатор конфигураци');
            return;
        }

        $widget_config = (new shopSdekintPluginWidgetSettingsModel)->findById($config_id);
        if (empty($widget_config) || (Hash::get($widget_config, 'type', '') !== 'legacy')) {
            $this->setError('Неверный идентификатор конфигурации или тип виджета');
            return;
        }

        $shipment_type = (string)ifset($shipment, 'type', 'courier');
        if (!in_array($shipment_type, ['courier', 'pickup'])) {
            $this->setError('Неизвестный тип доставки');
            return;
        }

        $shipping_id = (int)Hash::get($widget_config, 'settings.method', 0);
        if (!$shipping_id) {
            $this->setError('В конфигурации указан неверный способ доставки');
            return;
        }

        $cityToId = ifempty($shipment, 'cityToId', 0);

        try {
            if (!$cityToId) {
                $city = $this->suggestCity((string)ifempty($shipment, 'cityTo', ''));
                $cityToId = ifempty($city, 'id', 0);
            }
            if (!$cityToId) {
                throw new waException('City to not found');
            }

            // Shipping Plugin cannot calc by cityId, so we fetch correct city requisites from our db by city id
            $city = (new shopSdekintPluginCityModel)->getCityByCode($cityToId);
            if (empty($city)) {
                throw new waException('City to not found');
            }
        } catch (waException $e) {
            $this->setError($e->getMessage());
            return;
        }

        $shipping_address = array('country' => $city['country_iso3'], 'region' => $city['region_code'], 'city' => trim($city['name']));

        $items = (array)ifempty($shipment, 'goods', array(['weight' => 0.5, 'length' => 5, 'width' => 5, 'height' => 5, 'price' => 0.0]));
        $items = array_values($items);
        array_walk($items, function (&$item, $key) {
            if (!is_array($item)) {
                $item = array();
            }
            $item = shopSdekintPluginHelper::typecastScalarArrayValues($item, ['weight' => 'float', 'width' => 'int', 'height' => 'int', 'length' => 'int', 'price' => 'float']);
            $item['weight'] = max(0.1, shopSdekintPluginHelper::toFloat(ifempty($item, 'weight', 0.1)));
            $item['width'] = (int)max(1, (int)ifempty($item, 'width', 1));
            $item['height'] = (int)max(1, (int)ifempty($item, 'height', 0.0));
            $item['length'] = (int)max(1, (int)ifempty($item, 'length', 0.0));
            $item['price'] = round(max(0.0, shopSdekintPluginHelper::toFloat(ifempty($item, 'price', 0.1))), 2);
            $item['id'] = (string)ifempty($item, 'id', $key);
            $item['name'] = (string)ifempty($item, 'name', "Product $key");
        });

        try {
            $shipping_method = $this->getShippingMethod($shipping_id);
            $rates = $shipping_method->getRates($items, $shipping_address);
        } catch (waException $e) {
            $this->setError($e->getMessage());
            return;
        }

        $rate = null;
        foreach ($rates as $key => $r) {
            if ((strstr($key, 'TO_DOOR') !== false) && ($shipment_type == 'courier')) {
                $rate = $r;
                break;
            }
            if ((strstr($key, 'TO_DOOR') === false) && ($shipment_type == 'pickup')) {
                $rate = $r;
                break;
            }
        }
        $this->response = array();

        $this->response['type'] = $shipment_type;
        if (isset($shipment['timestamp']) && $shipment['timestamp']) {
            $this->response['timestamp'] = $shipment['timestamp'];
        }

        if (!empty($rate)) {
            $this->response['result'] = array(
                'price'             => round($rate['rate'], 2),
                'priceByCurrency'   => round($rate['rate'], 2),
                'deliveryPeriodMin' => (string)ifset($rate, 'custom_data', 'deliveryPeriodMin', '1'),
                'deliveryPeriodMax' => (string)ifset($rate, 'custom_data', 'deliveryPeriodMax', '1'),
                'deliveryDateMin'   => (string)ifset($rate, 'custom_data', 'deliveryDateMin', ''),
                'deliveryDateMax'   => (string)ifset($rate, 'custom_data', 'deliveryDateMax', ''),
                'tariffId'          => (string)ifset($rate, 'custom_data', 'tariffId', ''),
            );
        } else {
            $this->setError('Недоступно', ['code' => 100500]);
            return;
        }
    }

    /**
     * @param int $id
     * @return sydsekShipping
     * @throws waException
     */
    protected function getShippingMethod($id)
    {
        $methods = (new shopPluginModel)->listPlugins(shopPluginModel::TYPE_SHIPPING, ['id' => $id]);
        $method = ifempty($methods, $id, []);

        if (empty($method)) {
            throw new waException("Shipping method $id not found");
        }

        $plugin = ifempty($method, 'plugin', '');
        if (empty($plugin || ($plugin !== 'sydsek'))) {
            throw new waException("Incompatible calc plugin (ID='$plugin')");
        }

        if (!ifset($method, 'status', '0')) {
            throw new waException("Shipping method is switched off");
        }

        /** @var sydsekShipping $shipping_method */
        $shipping_method = shopShipping::getPlugin('sydsek', $id);
        return $shipping_method;
    }

    /**
     * Overloaded to be compatible with widget requests/answers
     */
    public function display()
    {
        if (waRequest::isXMLHttpRequest()) {
            $this->getResponse()->addHeader('Content-Type', 'application/json');
        }
        $this->getResponse()->sendHeaders();
        if (!$this->errors) {
            $data = $this->response;
            echo json_encode($data);
        } else {
            $type = ifset($this->response, 'type', null);
            $timestamp = ifset($this->response, 'timestamp', null);
            $errors = array('error' => $this->errors);
            if ($type) {
                $errors['type'] = $type;
            }
            if ($timestamp) {
                $errors['timestamp'] = $timestamp;
            }
            echo json_encode($errors);
        }
    }

    public function setError($message, $data = array())
    {
        if (empty($data)) {
            $this->errors[] = $message;
        } else {
            $this->errors[] = array(array('text' => $message) + $data);
        }
    }

    /**
     * @param string $term
     * @param null|int $count
     * @return mixed
     * @throws waException
     */
    protected function suggestCity($term, $count = null)
    {
        $cities = $this->plugin->getCalculatorApiClient()->cityGetListByTerm($term);

        if (!$cities->count()) {
            throw new waException('No cities found');
        }

        /** @var \SergeR\Webasyst\CdekSDK\Type\Geoname $city */
        $cities->getIterator()->rewind();
        $city = $cities->getIterator()->current();

        return array(
            'id'            => $city->getId(),
            'postCodeArray' => $city->getPostCodeArray(),
            'cityName'      => $city->getCityName(),
            'regionId'      => $city->getRegionId(),
            'regionName'    => $city->getRegionName(),
            'countryId'     => $city->getCountryId(),
            'countryName'   => $city->getCountryName(),
            'countryIso'    => $city->getCountryIso(),
            'name'          => $city->getCityName()
        );
    }
}

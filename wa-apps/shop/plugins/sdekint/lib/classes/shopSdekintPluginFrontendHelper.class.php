<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2017
 * @license Webasyst
 */

use SergeR\CakeUtility\Hash;
use SergeR\Webasyst\CdekSDK\API\Calculator\Request\CalculationRequest;

/**
 * Helper for frontend templates
 */
class shopSdekintPluginFrontendHelper
{
    /** @var shopSdekintPlugin */
    private $plugin;

    /**
     * shopSdekintPluginFrontendHelper constructor.
     * @param shopSdekintPlugin|null $plugin
     * @throws waException
     */
    function __construct(shopSdekintPlugin $plugin = null)
    {
        if (is_null($plugin) || !is_object($plugin) || !($plugin instanceof shopSdekintPlugin)) {
            $plugin = wa('shop')->getPlugin('sdekint');
        }

        $this->plugin = $plugin;
    }

    /**
     * Расчет срока и стоимости доставки
     *
     * @param array $params
     * @return array
     * @throws waException
     */
    public function calc(array $params = array())
    {
        $params += ['from' => $this->plugin->sender_city, 'to' => [], 'tariff' => [136, 137], 'weight' => 0.5, 'size' => [5, 5, 5]];
        $params = array_intersect_key($params, array_flip(['from', 'to', 'tariff', 'weight', 'size']));

        /**
         * @var int|string|array $from
         * @var int|string|array $to
         * @var int|string|array $tariff
         * @var string|float $weight
         * @var array $size
         */
        extract($params, EXTR_OVERWRITE);

        if (is_scalar($from)) {
            $from = ['id' => $from];
        }
        if (empty($from['id'])) {
            $from = $this->city($from);
            $from['id'] = ifset($from, 'sdek_id', 0);
        }
        if (empty($from) || !ifset($from, 'id', 0)) {
            return [];
        }

        if (is_scalar($to)) {
            $to = ['id' => $to];
        }
        if (empty($to['id'])) {
            $to = $this->city($to);
            if (empty($to)) {
                return [];
            }
            $to['id'] = $to['sdek_id'];
        }
        if (empty($to) || !ifset($to, 'id', 0)) {
            return [];
        }

        $tariff = (array)$tariff;
        array_walk($tariff, function (&$t) {
            if (is_string($t) && in_array(strtolower($t), ['pvz', 'point', 'courier', 'door-door', 'door-point', 'point-door', 'point-point'])) {
                switch ($t) {
                    case 'pvz':
                    case 'point':
                    case 'point-point':
                        $t = 136;
                        break;
                    case 'courier':
                    case 'point-door':
                        $t = 137;
                        break;
                    case 'door-point':
                        $t = 138;
                        break;
                    case 'door-door':
                        $t = 139;
                        break;
                }
            }
            if (is_scalar($t)) {
                $t = intval($t);
            }
        });
        /** @var shopSdekintPluginTariffModel $Tariff */
        $Tariff = $this->getCachedObject('shopSdekintPluginTariffModel');
        $tariff = array_unique(
            array_filter($tariff, function ($t) use ($Tariff) {
                return is_int($t) && !empty($Tariff->getById($t));
            })
        );
        if (empty($tariff)) {
            return [];
        }

        $weight = is_scalar($weight) ? $this->plugin->helper->toFloat($weight) : 0.5;
        if (!is_array($size) || count($size) !== 3) {
            $size = [5, 5, 5];
        }
        array_walk($size, function (&$s) {
            $s = max(1, intval($s));
        });

        $calculator = $this->plugin->getCalculatorApiClient();
        $Cache = $this->plugin->getCache();
        $result = [];
        foreach ($tariff as $t) {
            $hash = $this->getCalcResultHash($from, $to, $t, $weight, $size);
            $r = $Cache->get($hash, 'sdekint/frontend/calc');
            if (!$r || !is_array($r)) {
                try {
                    $request = new CalculationRequest();
                    $request->setSenderCityId($from['id'])
                        ->setReceiverCityId($to['id'])
                        ->setDateExecute(new DateTimeImmutable())
                        ->setGoods(array([
                            'weight' => $weight,
                            'length' => $size[0],
                            'width'  => $size[1],
                            'height' => $size[2]
                        ]))
                        ->setTariff($t);

                    $r = $calculator->calc($request);

                    if (!$r->isError()) {
                        $r = $r->toArray();
                        $Cache->set($hash, $r, 12 * 60 * 60, 'sdekint/frontend/calc');
                    } else {
                        $r = null;
                    }
                } catch (Exception $e) {
                    $r = null;
                }
            }
            if ($r) {
                $r['min_days'] = (int)ifempty($r, 'deliveryPeriodMin', 1);
                $r['max_days'] = (int)ifempty($r, 'deliveryPeriodMax', 1);
                $r['min_date'] = ifempty($r, 'deliveryDateMin', null);
                $r['max_date'] = ifempty($r, 'deliveryDateMax', null);
                $r['price'] = $r['priceByCurrency'];
                unset($r['deliveryPeriodMin'], $r['deliveryPeriodMax'], $r['priceByCurrency'], $r['deliveryDateMin'], $r['deliveryDateMax']);
                $result[$r['tariffId']] = $r;
            }
        }

        if (empty($result)) {
            return $result;
        }
        $tariff_ids = Hash::extract($result, '{n}.tariffId');
        $tariffs = (new shopSdekintPluginTariffModel())->find(['id' => $tariff_ids]);
        array_walk($result, function (&$r) use ($tariffs) {
            $t = Hash::extract($tariffs, "{n}[id={$r['tariffId']}]", []);
            if (!empty($t)) {
                $t = array_shift($t);
                unset($t['hidden']);
            }
            $r['tariff'] = $t;
        });

        return $result;
    }

    /**
     * Возвращает информацию по городу СДЭК по его коду из реестра СДЭК
     *
     * @param int $code
     * @return array
     */
    public function cdekCityByCode($code)
    {
        $code = (int)$code;
        if (!$code) {
            return array();
        }

        try {
            $result = (array)$this->getCachedObject('shopSdekintPluginCityModel')->getCityByCode($code);
        } catch (Exception $e) {
            $result = array();
        }
        if (empty($result)) {
            return array();
        }

        $chunks = array(
            'name'    => $result['name'],
            'region'  => ifempty($result, 'Region', 'name', ''),
            'country' => ifempty($result, 'Country', 'name', '')
        );

        if (mb_strtolower($chunks['name']) === mb_strtolower($chunks['region'])) {
            unset($chunks['region']);
        }

        $chunks = array_filter($chunks);

        $result['long_full'] = implode(', ', $chunks);
        unset($chunks['country']);
        $result['short_full'] = implode(', ', $chunks);

        return $result;
    }

    /**
     * Список ПВЗ
     *
     * @param array $params
     * @return array
     * @throws waException
     */
    public function points(array $params = [])
    {
        $params += ['country' => 'rus', 'region' => null, 'city' => null];
        $city = $this->city($params);

        if (empty($city)) {
            return array();
        }

        $result = (array)$this->getCachedObject('shopSdekintPluginPvzModel')->find(['city_code' => $city['sdek_id']]);
        if (empty($result)) {
            return array();
        }

        $points = array_map(function ($p) use ($city) {
            $point = array_intersect_key($p, array_flip(['code', 'name', 'work_time', 'address', 'phone', 'note', 'min_weight', 'max_weight', 'owner']));
            $point['type'] = $p['point_type'];
            $point['geo']['lat'] = $p['coord_y'];
            $point['geo']['lon'] = $p['coord_x'];

            $point += array(
                'country' => $city['country'],
                'region'  => $city['region'],
                'city'    => ['name' => $city['name'], 'sdek_id' => $city['sdek_id'], 'max_cod' => $city['max_cod']]
            );

            return $point;
        }, $result);

        return $points;
    }

    /**
     * @param array $params
     * @return string
     */
    public function pointsHtml(array $params = [])
    {
        $points = $this->points($params);
        $view = wa()->getView();
        $view->assign('points', $points);
        $template = wa('shop')->getConfig()->getPluginPath('sdekint') . '/templates/helpers/frontend_helper_points.html';

        return $view->fetch($template);
    }

    /**
     * Город из базы городов СДЭК
     *
     * @param array $params
     * @return array
     * @throws waException
     */
    public function city(array $params)
    {
        $params += ['name' => null, 'region' => null, 'country' => 'rus'];
        if (!$params['name'] || !$params['country']) {
            return array();
        }

        $result = $this->getCachedObject('shopSdekintPluginCityModel')->findByCountryRegionName($params['country'], $params['region'], $params['name']);
        $result = (array)array_shift($result);
        $country_data = $this->getCountry($params['country']);

        $city = array(
            'name'      => ifempty($result, 'name', ''),
            'sdek_id'   => empty($result['sdek_id']) ? null : (int)$result['sdek_id'],
            'is_center' => (bool)ifempty($result, 'is_center', false),
            'max_cod'   => array_key_exists('pod_max', $result) ? is_null($result['pod_max']) ? null : round($result['pod_max'], 2) : 0.0,
            'country'   => ['iso3' => $params['country'], 'name' => ifempty($country_data, 'name', '')]
        );

        $region = array();
        if (array_key_exists('region_count', $country_data) && $country_data['region_count'] && ifempty($result, 'region_code', null)) {
            $region = (array)$this->getCachedObject('waRegionModel')->get($params['country'], $result['region_code']);
        }

        $region += ['code' => '', 'name' => ''];
        $city['region'] = ['code' => $region['code'], 'name' => $region['name']];

        return $city;
    }

    /**
     * Список стран, которые используются в плагине
     *
     * @return array
     */
    public function sdekCountries()
    {
        return array_map(function ($c) {
            return $this->getCountry($c);
        }, ['rus', 'blr', 'kaz', 'arm', 'kgz', 'ukr']);
    }

    /**
     * Список регионов страны по коду iso3
     *
     * @param $country_iso3
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function regionByCountry($country_iso3)
    {
        if (!is_string($country_iso3)) {
            throw new InvalidArgumentException('Код страны должен быть строкой');
        }
        static $regions;
        if (!is_array($regions)) {
            $regions = array();
        }

        if (isset($regions[$country_iso3])) {
            return $regions[$country_iso3];
        }

        $regions[$country_iso3] = (array)(new waRegionModel)->getByCountry($country_iso3);
        return $regions[$country_iso3];
    }

    /**
     * Отображает информацию о пункте выдачи (или выдает массив с информацией)
     *
     * @formatter:off
     * Ключи для $options
     *
     * - format: строка 'html'или 'raw' выдавать отрендеренный шаблон или массив с информацией. По умолчанию 'html'
     * - code: строка если указан, то будет выбрана информация о ПВЗ с указанным кодом
     * - params: массив (array) с данными о заказе, если не указан `code` ищем код ПВЗ в заказе
     * - exclude_state_id: список (array) id статусов заказа, в котором информацию не показывать. Работает только если
     *     код пункта выдачи ищется в заказе (т.е. не указан напрямую). По умолчанию равен ['completed', 'refunded',
     *     'deleted'] то есть доп информация не показывается для выполненных заказов, возвратов и отмен.
     * - no_map true/false если установить в true то при рендеринге шаблона не будет добавлена карта. По умолчанию
     *     false
     * @formatter:on
     *
     * @param array $options
     * @return string|array
     * @throws waException
     */
    public function pointInfo($options = array())
    {
        if (!is_array($options) || empty($options)) {
            return '';
        }

        $defaults = array('format' => 'html', 'exclude_state_id' => array('completed', 'refunded', 'deleted'), 'state_id' => null);
        $options += $defaults;

        $point_code = !empty($options['code']) ? $options['code'] : null;
        $params = !empty($options['params']) ? $options ['params'] : null;
        if (!empty($params)) {
            $state_id = $options['state_id'];
            if (!$state_id || in_array($state_id, $options['exclude_state_id'])) {
                return '';
            }

            $delivery_type = null;
            if (isset($params['tariff']) && !empty($params['tariff']['to'])) {
                $delivery_type = $params['tariff']['to'];
            } elseif (isset($params['to']) && !empty($params['to']['delivery_type'])) {
                $delivery_type = $params['to']['delivery_type'];
            }

            switch ($delivery_type) {
                case 'postomat':
                case 'to-postomat' :
                    if (!empty($params['address']) && !empty($params['address']['stock_id'])) {
                        $point_code = $params['address']['stock_id'];
                    } elseif (!empty($params['to']) && !empty($params['to']['postomat_id'])) {
                        $point_code = $params['to']['postomat_id'];
                    }
                    break;
                case 'stock':
                case 'to-stock' :
                    if (!empty($params['address']) && !empty($params['address']['stock_id'])) {
                        $point_code = $params['address']['stock_id'];
                    } elseif (!empty($params['to']) && !empty($params['to']['stock_id'])) {
                        $point_code = $params['to']['stock_id'];
                    }
                    break;
            }
        }

        if (!$point_code) {
            return '';
        }

        $info = $this->getCachedObject('shopSdekintPluginPvzModel')->getByCode($point_code);

        if (!$info) {
            return '';
        }

        $point_type = ifset($info, 'point_type', null);
        switch (mb_strtolower($point_type)) {
            case 'postomat' :
                $info['point_type_human'] = 'Постомат';
                break;
            case 'pvz' :
                $info['point_type_human'] = 'Пункт выдачи заказов (ПВЗ)';
                break;
            default:
                $info['point_type_human'] = '';
                break;
        }

        $info['min_weight'] = floatval(ifset($info, 'min_weight', 0));
        $info['max_weight'] = floatval(ifset($info, 'max_weight', 0));

        if ($options['format'] == 'raw') {
            return $info;
        }
        $no_map = ifset($options, 'no_map', true);
        $template_file = wa()->getAppPath('plugins/sdekint/templates/helpers/pointInfo.html', 'shop');
        $view = wa()->getView();
        $view->assign(compact('info', 'no_map'));

        return $view->fetch($template_file);
    }

    /**
     * @param $className
     * @return mixed|shopSdekintPluginCityModel|waCountryModel|waRegionModel|shopSdekintPluginPvzModel
     */
    private function getCachedObject($className)
    {
        static $objects;
        if (!is_array($objects)) {
            $objects = array();
        }

        if (empty($objects[$className]) || !is_object($objects[$className]) || !($objects[$className] instanceof $className)) {
            $objects[$className] = new $className;
        }

        return $objects[$className];
    }

    /**
     * Возвращает страну и количество регионов в ней по iso3 коду
     *
     * @param $iso3
     * @return array
     * @throws waException
     */
    private function getCountry($iso3)
    {
        static $countries;
        if (!is_array($countries)) {
            $countries = array();
        }

        if (!isset($countries[$iso3])) {
            $c = $this->getCachedObject('waCountryModel')->get($iso3);
            if (!empty($c)) {
                $c['region_count'] = (int)$this->getCachedObject('waRegionModel')->countByField('country_iso3', $iso3);
            }
            $countries[$iso3] = $c;
        }

        return (array)$countries[$iso3];
    }

    private function getCalcResultHash($from, $to, $tariff, $weight, $size)
    {
        return md5($from['id'] . '|' . $to['id'] . '|' . (string)$tariff . '|' . sprintf('%0.3F', $weight) . '|' . implode(',', $size));
    }
}

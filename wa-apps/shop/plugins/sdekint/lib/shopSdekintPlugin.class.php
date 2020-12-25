<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @version 3.2.0
 * @copyright Serge Rodovnichenko, 2015-2017
 * @license http://www.webasyst.com/terms/#eula Webasyst
 */

use Psr\Log\LogLevel;
use SergeR\CakeUtility\Hash;
use SergeR\Webasyst\CdekSDK\API\Order\Request\PvzList;
use SergeR\Webasyst\CdekSDK\Client\WebasystCalculatorClient;
use SergeR\Webasyst\CdekSDK\Client\WebasystOrderClient;
use Syrnik\WaLogger;

/**
 * Основной класс плагина
 *
 * @property-read string $api_auth
 * @property-read string $api_key
 * @property-read int $api_timeout
 * @property-read string $address_field_street
 * @property-read string $address_field_house
 * @property-read string $address_field_flat
 * @property-read string $appraised_price
 * @property-read int $auto_change_states
 * @property-read string $contract_currency
 * @property-read int $copy_count
 * @property-read int $drop_quot_amp
 * @property-read string $fix_appraised_price
 * @property-read string $item_template
 * @property-read bool $overwrite_paid
 * @property-read string $print_version
 * @property-read string $reduce_count
 * @property-read string $reduce_template
 * @property-read string $ru_vat_product
 * @property-read string $seller_name
 * @property-read array $sellers
 * @property-read array $sender_city
 * @property-read array $service
 * @property-read bool $schedule_enabled
 * @property-read array $ship_methods
 * @property-read int $stockupdate
 * @property-read array $tariffs
 * @property-read string $ru_vat_delivery
 * @property-read bool $track_suggest
 * @property-read bool $use_sku_name
 * @property-read bool $debug_requests
 * @property-read bool $ru_phone_normalize
 * @property-read string $barcode_format
 * @property-read int $barcode_copy_count
 * @property-read float $default_weight
 * @property-read bool $_iddqd_
 */
class shopSdekintPlugin extends shopPlugin
{
    /** @var shopSdekintPluginHelper */
    public $helper;

    /**
     * shopSdekintPlugin constructor.
     * @param $info
     */
    public function __construct($info)
    {
        parent::__construct($info);
        $this->helper = new shopSdekintPluginHelper();
        $this->registerAutoloader();
    }

    /**
     * Settings getter
     *
     * @param $name
     * @return array|bool|int|mixed|string
     */
    public function __get($name)
    {
        $value = $this->getSettings($name);
        if (in_array($name, ['barcode_copy_count'])) {
            $value = (int)$value;
        } elseif ($name === 'api_timeout') {
            return max(7, (int)$value);
        } elseif ($name === 'default_weight') {
            $value = (float)str_replace(',', '.', trim($value));
            $value = $value > 0 ? $value : 0.1;
        }

        return $value;
    }

    /**
     * @return shopSdekintPluginSdekOrderApi
     * @throws waException
     * @deprecated
     */
    public function getOrdersApi()
    {
        $api = new shopSdekintPluginSdekOrderApi($this->api_auth, $this->api_key, $this->getNet());
        if (waSystemConfig::isDebug() && $this->debug_requests) {
            $api->setLogger(new WaLogger('shop/plugins/sdekint/requests.log', LogLevel::DEBUG));
        }
        return $api;
    }

    /**
     * @param null $api_auth
     * @param null $api_key
     * @param array $options
     * @return WebasystOrderClient
     */
    public function getOrdersApiClient($api_auth = null, $api_key = null, array $options = [])
    {
        $api_auth = $api_auth ?: $this->api_auth;
        $api_key = $api_key ?: $this->api_key;
        $client = new WebasystOrderClient($api_auth, $api_key);
        $options = (array)Hash::get($options, 'wanet') + ['timeout' => 120];
        $client->setDefaultWanetOptions($options);
        if (waSystemConfig::isDebug() && $this->debug_requests) {
            $client->setLogger(new WaLogger('shop/plugins/sdekint/requests.log', LogLevel::DEBUG));
            $client->setLogLevel(LogLevel::DEBUG);
        }

        return $client;
    }

    /**
     * @return WebasystCalculatorClient
     */
    public function getCalculatorApiClient()
    {
        $client = new WebasystCalculatorClient($this->api_auth, $this->api_key);
        $client->setDefaultWanetOptions(['timeout' => $this->api_timeout]);
        if (waSystemConfig::isDebug() && $this->debug_requests) {
            $client->setLogger(new WaLogger('shop/plugins/sdekint/requests.log', LogLevel::DEBUG));
            $client->setLogLevel(LogLevel::DEBUG);
        }

        return $client;
    }

    /**
     * @return shopSdekintPluginSdekCalcApi
     * @throws waException
     * @deprecated
     */
    public function getCalculatorApi()
    {
        return new shopSdekintPluginSdekCalcApi($this->api_auth, $this->api_key, $this->getNet(waNet::FORMAT_JSON, waNet::FORMAT_JSON));
    }

    /**
     * @param string $format
     * @param string $request_format
     * @return shopSdekintPluginNet
     * @throws waException
     */
    public function getNet($format = waNet::FORMAT_XML, $request_format = 'default')
    {
        $net = new shopSdekintPluginNet(['format' => $format, 'request_format' => $request_format, 'timeout' => $this->api_timeout]);
        $net->userAgent(sprintf(
            'CDEK Integration Plugin v.%s for Shop-Script/%s',
            $this->getVersion(),
            wa()->getVersion('shop')
        ));

        return $net;
    }

    /**
     * @return waCache
     * @throws waException
     */
    public function getCache()
    {
        $cache = wa('shop')->getCache('default');
        if ($cache) {
            return $cache;
        }

        return new waCache(new waFileCacheAdapter([]), 'shop');
    }

    /**
     * Возвращает размеры упаковок, отсортированные по весу, которые заданы в настройках.
     *
     * @return array
     */
    public function getPackageDimensions()
    {
        $setting = $this->getSettings('package_size');
        return (array)Hash::get($setting, 'table');
    }

    /**
     * @param null $name
     * @return array|bool|int|mixed|string
     */
    public function getSettings($name = null)
    {
        $settings = parent::getSettings($name);

        if ($name === null) {
            $settings = $this->_typecastSettings($settings);
        } else {
            $settings = $this->_typecastSettings([$name => $settings])[$name];
        }

        return $settings;
    }

    /**
     * @param array $settings
     * @return array
     */
    protected function _typecastSettings(array $settings)
    {
        foreach ($settings as $key => $data) {
            switch ($key) {
                case 'auto_change_states':
                case 'stockupdate':
                case 'copy_count' :
                case 'update_time':
                case 'barcode_copy_count':
                case 'reduce_count':
                    $data = intval($data);
                    break;
                case 'schedule_enabled':
                case 'overwrite_paid':
                case 'track_suggest':
                case 'use_sku_name':
                case 'debug_requests':
                case 'drop_quot_amp':
                case 'ru_phone_normalize':
                    $data = boolval($data);
                    break;
                case 'ship_methods':
                    $data = (array)$data;
                    $data = array_map(function ($v) {
                        return (int)$v;
                    }, array_unique($data));
                    $data = array_values($data);
                    break;
                case 'tariffs':
                    $data = array_values((array)$data);
                    $t = (new shopSdekintPluginTariffModel)->findOptions();
                    if (Hash::maxDimensions($data) == 1) {
                        array_walk($t, function (&$v) use ($data) {
                            $v['enabled'] = in_array($v['id'], $data);
                        });
                        $data = $t;
                    } else {
                        //отфильтруем тарифы, которых больше нет
                        $all = array_column($t, 'id');
                        $data = array_filter($data, function ($v) use ($all) {
                            return in_array($v['id'], $all);
                        });
                        //добавим тарифы, которые добавились
                        $all = array_column($data, 'id');
                        foreach ($t as $item) {
                            if (!in_array($item['id'], $all)) {
                                $data[] = $item;
                            }
                        }
                    }
                    break;
                case 'service':
                    $data = array_values((array)$data);
                    $data = array_map(function ($v) {
                        return (int)$v;
                    }, $data);
                    break;
                case 'sender_city':
                    $data = (array)$data + ['id' => null, 'name' => null];
                    if (!$data['id']) {
                        $data['name'] = null;
                    }
                    break;
                case 'address_field_street':
                case 'address_field_house':
                case 'address_field_flat':
                    $data = $this->helper->trim((string)$data);
                    break;
                case 'api_timeout' :
                    $data = intval($data);
                    if ($data < 15) {
                        $data = 15;
                    }
                    break;
                case '_iddqd_':
                    $data = intval($data);
                    $data = $data === 42;
                    break;
                case 'default_weight':
                    $data = round($this->helper->toFloat($data), 3);
                    break;
                case 'fix_appraised_price':
                    $data = round($this->helper->toFloat($data), 2);
                    break;
                case 'package_size':
                    $table = (array)Hash::get($data, 'table');
                    $table = array_filter(
                        array_map(function ($size) {
                            return is_array($size) ?
                                shopSdekintPluginHelper::typecastScalarArrayValues(
                                    $size,
                                    ['min_weight' => 'float', 'width' => 'int', 'height' => 'int', 'length' => 'int']
                                ) : array();
                        }, $table)
                    );

                    usort($table, function ($a, $b) {
                        if ($a['min_weight'] == $b['min_weight']) {
                            return 0;
                        }
                        return $a['min_weight'] > $b['min_weight'] ? 1 : -1;
                    });
                    $data['table'] = $table;
                    break;
                case 'sellers':
                    $data = (array)$data;
                    $default_idx = -1;
                    foreach ($data as $index => $seller) {
                        $data[$index] = array(
                            'address'        => shopSdekintPluginHelper::trim((string)Hash::get($seller, 'address')),
                            'name'           => shopSdekintPluginHelper::trim((string)Hash::get($seller, 'name')),
                            'inn'            => shopSdekintPluginHelper::trim((string)Hash::get($seller, 'inn')),
                            'phone'          => shopSdekintPluginHelper::trim((string)Hash::get($seller, 'phone')),
                            'ownership_form' => (int)Hash::get($seller, 'ownership_form'),
                            '_is_default'    => (bool)Hash::get($seller, '_is_default'),
                            'id'             => Hash::get($seller, 'id', waString::uuid())
                        );
                        if ($data[$index]['_is_default']) {
                            if ($default_idx === -1) {
                                $default_idx = $index;
                            } else {
                                $data[$index]['_is_default'] = false;
                            }
                        }
                    }
                    if (($default_idx === -1) && Hash::check($data, 0)) {
                        $data[0]['_is_default'] = true;
                    }
                    break;
            }
            $settings[$key] = $data;
        }

        return $settings;
    }

    /**
     * @return array
     */
    public function handlerBackendMenu()
    {
        $this->addCss('css/plugin.sdekint.backend.css');
        return array(
            'core_li' => '<li class="no-tab s-plugin-sdekint"><a href="?plugin=sdekint">СДЭК</a></li>'
        );
    }

    /**
     * Возвращает инстанс хелпера фронтенда
     *
     * @return shopSdekintPluginFrontendHelper
     * @throws waException
     */
    public static function helper()
    {
        static $frontend_helper = null;
        if (!$frontend_helper) {
            /** @var shopSdekintPlugin $plugin */
            $plugin = wa('shop')->getPlugin('sdekint');
            $frontend_helper = new shopSdekintPluginFrontendHelper($plugin);
        }

        return $frontend_helper;

    }

    /**
     * Handler for backend_order hook
     *
     * @param array|shopOrder $order
     * @return array
     * @throws SmartyException
     * @throws waException
     */
    public function handlerBackendOrder($order)
    {
        if (!ifempty($order, 'params', 'sdekint_plugin.dispatch_number', null)) {
            return array();
        }

        $sdekint_info = $this->helper->extractOrderParameters($order['params']);
        $sdekint_print = $this->print_version;

        $view = wa()->getView();
        $view->assign(compact('sdekint_info', 'sdekint_print'));

        return array('info_section' => $view->fetch($this->path . '/templates/hooks/backend_order_info_section.html'));
    }

    /**
     * @return array
     * @throws SmartyException
     * @throws waException
     */
    public function handlerBackendOrders()
    {
        $view = wa('shop')->getView();
        $this->addJs('js/orders.js');
        return array(
            'sidebar_section' => $view->fetch($this->path . '/templates/hooks/backend_orders_sidebar_section.html')
        );
    }

    /**
     * Handler for frontend_my_order hook
     * @see https://developers.webasyst.ru/hooks/shop/frontend_my_order/
     *
     * @throws waException
     */
    public function handlerFrontendMyOrder($order)
    {
        $setting = $this->getSettings('front_my_order_point');
        if (($setting == 'off') || !in_array($setting, array('info', 'infomap'))) {
            return '';
        }
        $params = $this->helper->extractOrderParameters($order['params']);

        $no_map = $setting == 'info' ? true : false;
        $result = self::helper()->pointInfo(array(
            'format'   => 'html',
            'params'   => $params,
            'no_map'   => $no_map,
            'state_id' => !empty($order['state_id']) ? $order['state_id'] : null
        ));
        if ($result) {
            wa()->getResponse()->addJs('https://api-maps.yandex.ru/2.1/?lang=ru_RU', false);
        }

        return $result;
    }

    /**
     * @param array $param
     * @throws waDbException
     * @throws waException
     * @deprecated
     */
    public function handlerOrderAction(array $param)
    {
        if (!waSystemConfig::isDebug()) {
            return;
        }
        if (empty($param['order_id']) || (Hash::get($param, 'action_id') !== 'editshippingdetails')) {
            return;
        }

        if (!(bool)waRequest::post('sdekint_plugin_clear')) {
            return;
        }

        $OrderParams = new shopOrderParamsModel();
        $OrderParams->exec('DELETE FROM `' . $OrderParams->getTableName() . '` WHERE `order_id`=i:id AND `name` LIKE \'sdekint_plugin.%\'', ['id' => $param['order_id']]);

        return;
    }

    /**
     * @param array $param
     * @return string
     * @throws waDbException
     * @throws waException
     * @deprecated
     */
    public function handlerOrderActionForm(array $param)
    {
        if (!waSystemConfig::isDebug()) {
            return null;
        }

        if (empty($param['order_id']) || (Hash::get($param, 'action_id') !== 'editshippingdetails')) {
            return null;
        }

        if (!(new shopOrderParamsModel)->getOne($param['order_id'], 'sdekint_plugin.dispatch_number')) {
            return null;
        }

        return '<div class="block not-padded top-padded bottom-padded sdekint-order-action-form"><input style="vertical-align: bottom" type="checkbox" name="sdekint_plugin_clear" value="1"> Очистить данные накладной СДЭК</div>';
    }

    /**
     * Handler for reset hook
     * @see https://developers.webasyst.ru/hooks/shop/reset/
     */
    public function handlerReset()
    {
        (new shopSdekintPluginOrderActionsModel)->truncate();
        (new shopSdekintPluginCourierCallsModel)->truncate();
    }

    /**
     * @param array &$data
     * @param string $event
     * @throws waException
     */
    public function handlerSyrnikShipping(&$data, $event)
    {
        if (ifset($data, 'id', '') !== 'sydsek') {
            return;
        }

        $evt = explode('.', $event);
        if (empty($evt[1])) {
            return;
        }

        $evt = strtolower($evt[1]);
        (new shopSdekintPluginSyrnikShippingProcessor)->process($data, $evt);
    }

    /**
     * @return array
     * @throws waException
     */
    public function optionsAddressFields()
    {
        return array_merge(
            array(['value' => '', 'title' => '- нет такого поля -']),
            array_values(
                array_map(
                    function (waContactField $f) {
                        return ['value' => $f->getId(), 'title' => $f->getName()];
                    },
                    waContactFields::get('address')->getFields()
                )
            )
        );
    }

    /**
     * @param mixed [string] $settings Array of settings key=>value
     * @return array|void
     * @throws waException
     */
    public function saveSettings($settings = array())
    {
        $api_auth = isset($settings['api_auth']) ? $settings['api_auth'] : $this->getSettings('api_auth');
        $api_key = isset($settings['api_key']) ? $settings['api_key'] : $this->getSettings('api_key');

        if ($api_key && $api_auth) {
            try {
                (new shopSdekintPluginPvzModel)->loadFromResponse($this->getOrdersApiClient(
                    $api_auth,
                    $api_key, ['wanet' => ['timeout' => 120]]
                )->pvzList(PvzList::fromArray(['type' => 'ALL'])));
                shopSdekintPluginHelper::log('Справочник ПВЗ обновлен');
                $settings['stockupdate'] = time();
            } catch (waException $e) {
                shopSdekintPluginHelper::log('Ошибка обновления списка ПВЗ: ' . $e->getMessage());
            }
        }

        $wf_sdekint = include($this->path . '/lib/config/workflow.php');
        $wf_config = shopWorkflow::getConfig();
        $is_config_changed = false; // чтоб не сохранять конфиг, если он не изменился

        // Здесь мы только проверим наличие нужных действий в workflow, но не будем их привязывать к статусам
        // потому, что это просто код для восстановления после неразумных действий пользователя и назначить
        // действия статусам он может сам. Главное, чтоб действия были
        // Возможно это отдельным методом реализовать надо. Или не надо. Все равно больше ниоткуда не вызывается
        foreach ($wf_sdekint['actions'] as $action_id => $action) {
            if (!array_key_exists($action_id, $wf_config['actions'])) {
                $is_config_changed = true;
                $wf_config['actions'][$action_id] = $action;
            }
        }

        if ($is_config_changed) {
            if (!shopWorkflow::setConfig($wf_config)) {
                shopSdekintPluginHelper::log('Ошибка сохранения workflow с добавленными действиями');
            }
        }

        if (isset($settings['package_size']) && isset($settings['package_size']['table']) && is_array($settings['package_size']['table'])) {
            array_walk($settings['package_size']['table'], function (&$v) {
                $v['min_weight'] = $this->helper->toFloat($v['min_weight']);
                foreach (['height', 'width', 'length'] as $d) {
                    $v[$d] = (int)max(1, (int)$v[$d]);
                }
            });
            usort($settings['package_size']['table'], function ($a, $b) {
                if ($a['min_weight'] == $b['min_weight']) {
                    return 0;
                }

                return $a['min_weight'] > $b['min_weight'] ? 1 : -1;
            });
        }

        parent::saveSettings($settings);
    }

    /**
     *
     */
    private function registerAutoloader()
    {
        require_once __DIR__ . '/vendors/autoload.php';
    }
}

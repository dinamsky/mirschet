<?php

use SergeR\CakeUtility\Hash;

/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2015-2019
 * @license http://www.webasyst.com/terms/#eula Webasyst
 * @package sdekint.controller
 */
class shopSdekintPluginSettingsAction extends waViewAction
{
    /**
     * @throws waException
     * @throws Exception
     */
    public function execute()
    {
        /** @var shopSdekintPlugin */
        $plugin = wa('shop')->getPlugin('sdekint');

        $stocks_last_updated = (int)(new waAppSettingsModel)->get('shop.sdekint', 'stockupdate', 0);
        $Point = new shopSdekintPluginPvzModel;

        $info = [
            'name'                  => $plugin->getName(),
            'version'               => $plugin->getVersion(),
            'is_city_list_empty'    => $this->isCityTableEmpty(),
            'cron_command'          => 'php ' . wa()->getConfig()->getRootPath() . '/cli.php shop sdekintPlugin',
            'tariffs'               => (new shopSdekintPluginTariffModel)->findList(),
            'currencies'            => array_values(self::currenciesOptions()),
            'zero_vat_rate_options' => self::zeroVatRateOptions(),
            'address_fields'        => self::optionsAddressFields(['zip', 'country', 'region', 'city', 'street', 'lat', 'lng']),
            'shipping_methods'      => self::deliveryMethodsOptions(),
            'ownership_forms'       => $this->_getOwnershipForms(),
            'stocks_last_updated'   => ($stocks_last_updated ? waDateTime::format('humandatetime', $stocks_last_updated) : 'не обновлялись'),
            'stocks_count'          => $Point->countAll()
        ];

        $info['stocks_by_country'] = $info['stocks_count'] ? array_values($Point->query('select country_name_sdek, count(DISTINCT code) as cnt FROM shop_sdekint_pvz GROUP BY country_name_sdek order by country_name_sdek')->fetchAll()) : [];

        $settings = $plugin->getSettings();

        // Отфильтруем неизвестные способы
        $valid_deliveries = array_column($info['shipping_methods'], 'value');
        $settings['ship_methods'] = array_filter($settings['ship_methods'], function ($v) use ($valid_deliveries) {
            return in_array($v, $valid_deliveries);
        });

        $this->view->assign(compact('settings', 'info'));
    }

    /**
     * @param $name
     * @param array $params
     * @return string
     * @throws Exception
     * @deprecated
     */
    public function controlCitySelectorMulti($name, $params = array())
    {
        $params = array_filter($params, function ($key) {
            return !strpos($key, 'wrapper');
        }, ARRAY_FILTER_USE_KEY);

        waHtmlControl::addNamespace($params, $name);

        $control = waHtmlControl::getControl(
            waHtmlControl::INPUT,
            'name',
            array(
                'title_wrapper'       => '',
                'description_wrapper' => '',
                'class'               => 'cdek-sender-city-name',
                'value'               => $params['value']['name']) + $params);

        $control .= waHtmlControl::getControl(
            waHtmlControl::INPUT,
            'id',
            array(
//                'control_wrapper'     => '<div class="empty region">%2$s<br>%3$s</div>',
                'control_wrapper'     => '<span >%2$s %3$s</span>',
                'title_wrapper'       => '',
                'description_wrapper' => '<span class="hint">%s</span>',
//                'readonly' => true,
                'style'               => 'width:4em;min-width:auto',
                'class'               => 'cdek-sender-city-id',
                'description'         => 'Не изменяйте самостоятельно код города, если не знаете, зачем это точно нужно',
                'value'               => $params['value']['id']) + $params
        );

        return $control;
    }

    /**
     * @param $name
     * @param array $params
     * @return string
     * @throws Exception
     * @deprecated
     */
    public function controlSortableGroupbox($name, $params = array())
    {
        $control = '';
        waHtmlControl::addNamespace($params, $name);
        $opts = $params['options'];

        if (!isset($params['value']) || !is_array($params['value'])) {
            $params['value'] = array();
        }

        $sorted_opts = array();
        foreach ($params['value'] as $v) {
            foreach ($opts as $key => $optv) {
                if ($optv['value'] == $v) {
                    $sorted_opts[] = $optv;
                    unset($opts[$key]);
                }
            }
        }
        $opts = array_merge($sorted_opts, $opts);

        foreach ($opts as $opt) {
            $control .= waHtmlControl::getControl(
                waHtmlControl::CHECKBOX,
                $name . '[]',
                array(
                    'value'           => $opt['value'],
//                    'namespace' => $params['namespace'],
                    'namespace'       => '',
                    'id'              => $params['id'] . '_' . $opt['value'],
                    'checked'         => in_array($opt['value'], $params['value']),
                    'title'           => $opt['title'],
                    'title_wrapper'   => '%s',
                    'control_wrapper' => '<li><i class="icon16 sort"></i> %2$s&nbsp;%1$s</li>',

                )
            );
        }

        return '<ul class="sdekint-sortable">' . $control . '</ul>';
    }

    /**
     * @param $name
     * @param array $params
     * @return string
     * @throws Exception
     * @deprecated
     */
    public function controlAssocGroupbox($name, $params = array())
    {
        $control = '';
        $options = isset($params['options']) ? (is_array($params['options']) ? $params['options'] : array($params['options'])) : array();
        if (!is_array($params['value'])) {
            $params['value'] = array();
        }
        waHtmlControl::addNamespace($params, $name);
        $wrappers = ifempty($params, 'options_wrapper', array()) + array(
                'title_wrapper'       => '&nbsp;%s',
                'description_wrapper' => '<span class="hint">%s</span>',
                'control_wrapper'     => '%2$s' . "\n" . '%1$s' . "\n" . '%3$s' . "\n",
                'control_separator'   => "<br>",

            );
        unset($params['options_wrapper']);
        $params = array_merge($params, $wrappers);
        $checkbox_params = $params;
        if (isset($params['options'])) {
            unset($checkbox_params['options']);
        }
        $id = 0;
        foreach ($options as $option) {
            //TODO check that $option is array
            $checkbox_params['value'] = empty($option['value']) ? $option['value'] : 1;
            $checkbox_params['checked'] = array_key_exists($option['value'], $params['value']) && $params['value'][$option['value']];
            $checkbox_params['title'] = empty($option['title']) ? null : $option['title'];
            $checkbox_params['description'] = ifempty($option, 'description', null);
            $control .= waHtmlControl::getControl(waHtmlControl::CHECKBOX, $option['value'], $checkbox_params);
            if (++$id < count($options)) {
                $control .= $params['control_separator'];
            }
        }
        return $control;
    }

    public static function tariffOptions()
    {
        $Tariff = new shopSdekintPluginTariffModel();

        return $Tariff->findList();
    }

    /**
     * @param array $params
     * @return array<{value:int, title:string}>
     * @throws waDbException
     * @throws waException
     */
    public static function deliveryMethodsOptions(array $params = [])
    {
        $ShopPlugin = new shopPluginModel();
        $plugins = $ShopPlugin->listPlugins('shipping', ['all' => true]);
        $methods = array();

        foreach ($plugins as $plugin) {
            $methods[] = array('value' => (int)$plugin['id'], 'title' => $plugin['name']);
        }

        if (($additional = (array)Hash::get($params, 'add'))) {
            if (Hash::maxDimensions($additional) === 1) {
                $methods[] = $additional;
            } else {
                array_merge($methods, $additional);
            }
        }

        return $methods;
    }

    /**
     * Список валют, из тех что есть в магазине, которые подойдут для
     * работы со СДЭКом
     *
     * @return array
     * @throws waException
     */
    public static function currenciesOptions()
    {
        /** @var shopConfig $config */
        $config = wa('shop')->getConfig();
        $currencies = $config->getCurrencies(['RUB', 'BYN', 'KZT', 'AMD', 'KGS', 'UAH']);

        return array_map(function ($c) {
            return ['value' => $c['code'], 'title' => $c['title']];
        }, $currencies);
    }

    /**
     * @return bool
     * @throws waDbException
     * @throws waException
     * @todo Move to the model
     */
    private function isCityTableEmpty()
    {
        $city_count = (new shopSdekintPluginCityModel)->countAll();
        return !($city_count > 0);
    }

    /**
     * Варианты настройки "Нулевая ставка налога"
     *
     * @return array
     */
    public static function zeroVatRateOptions()
    {
        return [
            ['value' => 'VATX', 'title' => 'Без НДС'],
            ['value' => 'VAT0', 'title' => 'НДС 0%']
        ];
    }

    /**
     * @param array $exclude
     * @return array
     * @throws waException
     */
    public static function optionsAddressFields(array $exclude = [])
    {
        return array_merge(
            array(['value' => '', 'title' => '- нет такого поля -']),
            array_filter(
                array_values(
                    array_map(
                        function (waContactField $f) {
                            return ['value' => $f->getId(), 'title' => $f->getName()];
                        },
                        waContactFields::get('address')->getFields()
                    )
                ),
                function ($v) use ($exclude) {
                    return !in_array($v['value'], $exclude);
                }
            )
        );
    }

    /**
     * @return array
     */
    protected function _getOwnershipForms()
    {
        try {
            $file = wa('shop')->getConfig()->getPluginPath('sdekint') . '/lib/config/data/ownership_forms.php';
        } catch (waException $e) {
            return [];
        }
        if (file_exists($file) && is_readable($file)) {
            return include($file);
        }
        return [];
    }
}

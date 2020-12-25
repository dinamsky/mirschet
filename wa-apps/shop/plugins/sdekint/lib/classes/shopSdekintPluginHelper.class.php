<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @version 3.1.0
 * @copyright Serge Rodovnichenko, 2015
 * @license http://www.webasyst.com/terms/#eula Webasyst
 */
use SergeR\CakeUtility\Hash;

/**
 * Class shopSdekintPluginHelper
 */
class shopSdekintPluginHelper
{
    public static function getSecureKey($date, $password, $date_format = 'c')
    {
        if (!($date instanceof DateTime) && !($date instanceof DateTimeImmutable)) {
            $date = new DateTime($date);
        }

        return md5($date->format($date_format) . '&' . $password);
    }

    /**
     * @param array $a
     * @param array $b
     * @return int
     * @deprecated
     */
    public static function stockComparator($a, $b)
    {
        if ($a['name'] == $b['name']) {
            return 0;
        }

        return $a['name'] < $b['name'] ? -1 : 1;
    }

    /**
     * Создает хэш из полей адреса доставки заказа. Поля адреса должны быть вида 'shipping_address.%'
     * Учитывает zip, country, region, city, street (именно в этом порядке, с образанием начальных и
     * конечных пробелов, разделенные вертикальной чертой |
     *
     * @param array $params
     * @return string
     */
    public static function getShippingAddressHash($params)
    {
        $zip = self::extractShippingAddressField('zip', $params);
        $country = self::extractShippingAddressField('country', $params);
        $region = self::extractShippingAddressField('region', $params);
        $city = self::extractShippingAddressField('city', $params);
        $street = self::extractShippingAddressField('street', $params);

        return hash('sha256', "$zip|$country|$region|$city|$street");
    }

    private static function extractShippingAddressField($field, $params)
    {
        $result = ifset($params, "shipping_address.$field", '');
        $result = preg_replace('/^[\pZ\pC]+|[\pZ\pC]+$/u', '', $result);

        return $result;
    }

    /**
     * @param $data
     * @param string $separator
     * @return array
     * @deprecated
     */
    public static function flatten($data, $separator = '.')
    {
        $result = array();
        $path = null;

        if (is_array($separator)) {
            extract($separator, EXTR_OVERWRITE);
        }

        if (!is_null($path)) {
            $path .= $separator;
        }

        foreach ($data as $key => $val) {
            if (is_array($val)) {
                $result += (array)self::flatten($val, array(
                    'separator' => $separator,
                    'path'      => $path . $key
                ));
            } else {
                $result[$path . $key] = $val;
            }
        }

        return $result;
    }

    public static function expand($data, $separator = '.')
    {
        $result = array();
        $stack = array();
        foreach ($data as $flat => $value) {
            $keys = explode($separator, $flat);
            $keys = array_reverse($keys);
            $child = array($keys[0] => $value);
            array_shift($keys);
            foreach ($keys as $k) {
                $child = array($k => $child);
            }
            $stack[] = array($child, &$result);
            while (!empty($stack)) {
                foreach ($stack as $curKey => &$curMerge) {
                    foreach ($curMerge[0] as $key => &$val) {
                        if (!empty($curMerge[1][$key]) && (array)$curMerge[1][$key] === $curMerge[1][$key] && (array)$val == $val) {
                            $stack[] = array(&$val, &$curMerge[1][$key]);
                        } elseif ((int)$key === $key && isset($curMerge[1][$key])) {
                            $curMerge[1][] = $val;
                        } else {
                            $curMerge[1][$key] = $val;
                        }
                    }
                    unset($stack[$curKey]);
                }
                unset($curMerge);
            }
        }

        return $result;
    }

    public static function boolval($str)
    {
        $str = mb_strtolower($str, 'UTF-8');
        return in_array($str, ['1', 'yes', 'есть', 'да', 'true']);
    }

    /**
     * Проверка наличия нужных расширений PHP
     *
     * @throws waException
     */
    public static function checkRequiredExtensions()
    {
        if (version_compare(PHP_VERSION, '5.6.0', '<')) {
            throw new waException(sprintf('Для работы плагина требуется PHP версии не ниже 5.6.0. Вы используете PHP версии %s. ', PHP_VERSION));
        }
    }

    /**
     * Возвращает список действий, доступных для заказа в указанном статусе
     * Фильтр "только известные" возвращает действия только те, которые мы знаем, с какими параметрами вызывать
     *
     * @param string $state_id
     * @param bool $only_known
     * @return array
     * @throws waException
     */
    public static function listWorkflowActionsForState($state_id, $only_known = true)
    {
        $workflow = new shopWorkflow();
        $known_classes = array('shopWorkflowAction', 'shopWorkflowCompleteAction', 'shopWorkflowDeleteAction',
            'shopWorkflowProcessAction', 'shopWorkflowShipAction');

        $state = $workflow->getStateById($state_id);
        $actions = array();
        if ($state && ($state instanceof waWorkflowState)) {
            /** @var waWorkflowAction|shopWorkflowAction $a */
            foreach ($state->getActions() as $a) {
                if (!$only_known || in_array(get_class($a), $known_classes)) {
                    $actions[$state_id][$a->getId()] = $a->getName();
                }
            }
        }

        return ifempty($actions, $state_id, array());
    }

    /**
     * Проверка наличия валюты с указанным кодом в системе
     *
     * @param string $code
     * @return bool
     */
    public function isCurrencyDefined($code)
    {
        $c = wa('shop')->getConfig()->getCurrencies($code);
        return !empty($c);
    }

    /**
     * @param string $message
     * @param bool|false $critical
     */
    public static function log($message, $critical = false)
    {
        if (waSystemConfig::isDebug() || $critical) {
            waLog::log($message, 'shop/plugins/sdekint/sdekint.log');
        }
    }

    /**
     * Multibyte trim
     *
     * @param string $str
     * @return string
     */
    public static function trim($str)
    {
        return mb_ereg_replace('^[[:space:]]*([\s\S]*?)[[:space:]]*$', '\1', $str);
    }

    public static function taxList()
    {
        return array_merge([['value' => '', 'title' => '-']], array_values(
            (new shopTaxModel())
                ->query("SELECT DISTINCT t.id AS `value`, t.name AS `title` FROM shop_tax AS t INNER JOIN shop_tax_regions str ON t.id=str.tax_id AND str.country_iso3='rus' ORDER BY `title`")
                ->fetchAll()
        ));
    }

    /**
     * Добавляет вес товарным позициям заказа. Если веса нет, добавляет дефолтное значение
     *
     * @param array $items
     * @param array $options
     * @return array
     * @throws waException
     */
    public function addOrderItemWeight($items, $options = array())
    {
        $default_options = array(
            'default_weight' => 10,
            'weight'         => 'g',
            'currency'       => wa('shop')->getConfig()->getCurrency(true), /* Валюта магазина поумолчанию */
        );
        foreach ($default_options as $k => $v) {
            if (!array_key_exists($k, $options)) {
                $options[$k] = $v;
            }
        }

        $dimension = shopDimension::getInstance();
        $default_weight = $dimension->convert($options['default_weight'], 'weight', $dimension->getBaseUnitCode('weight'), $options['weight']);

        $add_weight = function (&$item, $weight) {
            $item['weight'] = shopSdekintPluginHelper::toFloat($weight);
        };
        $items = $this->workupOrderItems($items, $options);
        foreach ($items as &$v) {
            if (isset($v['item'])) {
                $item = &$v['item'];
            } else {
                $item = &$v;
            }
            if ($item['type'] != 'product') {
                $add_weight($item, 0);
                continue;
            }
            if (!array_key_exists('weight', $item)) {
                $add_weight($item, $default_weight);
            }
            unset($item);
        }
        unset($v);

        return $items;
    }


    protected function workupOrderItems($items, $options = array())
    {
        $Feature = new shopFeatureModel();
        $weight_feature = $Feature->getByCode('weight');
        $product_ids = $sku_ids = array();

        foreach ($items as $item) {
            $product_ids[] = isset($item['product_id']) ? $item['product_id'] : $item['skus'][$item['sku_id']]['product_id'];
            $sku_ids[] = $item['sku_id'];
        }

        if (!$weight_feature) {
            $weight_values = array();
        } else {
            $FeatureValue = $Feature->getValuesModel($weight_feature['type']);
            $weight_values = $FeatureValue->getProductValues($product_ids, $weight_feature['id']);
        }

        $add_weight = function (&$item, $weight) {
            $item['weight'] = shopSdekintPluginHelper::toFloat($weight);
        };
        $dimension = shopDimension::getInstance();
        foreach ($items as &$v) {
            if (isset($v['item'])) {
                $item = &$v['item'];
            } else {
                $item = &$v;
            }
            if ($item['type'] != 'product') {
                $add_weight($item, 0);
                continue;
            }

            $product_id = isset($item['product_id']) ? $item['product_id'] : $item['skus'][$item['sku_id']]['product_id'];
            $sku_id = $item['sku_id'];

            // Если есть вес артикула, используем его, если нет, то вес товара. Если нет никакого веса, то 0
            if (isset($weight_values['skus'][$sku_id])) {
                $add_weight($item, $weight_values['skus'][$sku_id]);
            } elseif (isset($weight_values[$product_id])) {
                $add_weight($item, $weight_values[$product_id]);
            }

            // Сконвертируем вес из базового в нужный нам
            if (isset($item['weight'])) {
                $item['weight'] = $dimension
                    ->convert($item['weight'], 'weight', $options['weight']);
            }
            unset($item);
        }
        unset($v);
        /* Если есть класс , то обрабатываем через дробное */
        if (class_exists('shopZzzfractionalPlugin')) {
            if (!array_key_exists('collapse_quantity', $options)) {
                $options['collapse_quantity'] = 1; /* Настройка для конвертации всех позиций к количеству 1 и переносу реального количества в название */
            }
            $factory = new shopZzzfractionalPluginFactory();
            $strategy = $factory->getStrategy();
            $strategy->workupOrderItems($items, $options);
        }

        return $items;
    }

    /**
     * Из строки во float с учетом того, что разделитель может быть не только точка, но и запятая
     *
     * @param string $str
     * @return float
     */
    public static function toFloat($str)
    {
        return (float)preg_replace(['/,/u', '/[^\d^.]/u', '/\.(?=[^.]*\.)/u'], ['.'], $str);
    }

    /**
     * Высчитывает сумму налога для каждой товарной позиции
     *
     * @param array $items
     * @param array $options
     * @return array
     * @throws waException
     */
    public function applyOrderItemTaxes(array $items, $options = array())
    {
        $options += array(
            'allowed_rates'  => array(null, 0.0, 10.0, 18.0),
            'default_rate'   => null,
            'incorrect_rate' => null
        );

        foreach ($items as $k => $item) {
            $tax_percent = $this->getItemTaxPercent($item, $options);
            $tax_included = (bool)ifset($item, 'tax_included', true);
            $taxable_price = $item['price'] - $item['total_discount'] / $item['quantity'];
            if ($tax_percent) {
                if ($tax_included) {
//                $items[$k]['tax_value'] = round(ceil(($taxable_price - $taxable_price*(100+$tax_percent)/100)*100)/100, 2);
//                $items[$k]['tax_value'] = round(ceil(($taxable_price - $taxable_price / (100 + $tax_percent) * 100) * 100) / 100, 2);
                    $items[$k]['tax_value'] = round($taxable_price - $taxable_price / (100 + $tax_percent) * 100, 4);
                } else {
//                $items[$k]['tax_value'] = round(ceil($taxable_price * $tax_percent) / 100, 2);
                    $items[$k]['tax_value'] = round($taxable_price * $tax_percent / 100, 4);
                }
            } else {
                $items[$k]['tax_value'] = 0.0;
            }

            if (array_key_exists('services', $item) && is_array($item['services'])) {
                $items[$k]['services'] = $this->applyOrderItemTaxes($item['services'], $options);
            }
        }

        return $items;
    }

    /**
     * @param array $item
     * @param array $options
     * @return float|null
     * @throws waException
     */
    private function getItemTaxPercent(array $item, array $options)
    {
        if (!array_key_exists('tax_percent', $item)) {
            if (is_string($options['default_rate'])) {
                throw new waException("Не указана ставка налога для {$item['name']}");
            }
            $item['tax_percent'] = $options['default_rate'];
        }

        if (!empty($options['allowed_rates']) && !in_array($item['tax_percent'], $options['allowed_rates'])) {
            if (is_string($options['incorrect_rate'])) {
                switch ($options['incorrect_rate']) {
                    case 'exception' :
                        throw new waException(sprintf(
                            'Недопустимое значение процента налога (%0.2f%%) дл товара %s',
                            $item['tax_percent'],
                            $item['name']
                        ));

                        break;
                }
            } elseif (!in_array($options['incorrect_rate'], $options['allowed_rates'])) {
                throw new waException('Значение налога для некорректных значений должно быть в списке разрешенных значений');
            } else {
                $item['tax_percent'] = $options['incorrect_rate'];
            }
        }

        return $item['tax_percent'];
    }

    /**
     * Выбирает все услуги из общего массива и добавляет их в элемент services к тем товарам,
     * которым они назначены
     *
     * @param array $items
     * @return array
     */
    public function combineOrderItemServices(array $items)
    {
        return array_map(
            function ($p) use ($items) {
                return $p + ['services' => array_filter($items, function ($item) use ($p) {
                        return
                            isset($item['type']) &&
                            isset($item['parent_id']) &&
                            ($item['type'] == 'service') &&
                            ($item['parent_id'] == $p['id']);
                    })];
            },
            array_filter($items, function ($item) {
                return $item['type'] == 'product';
            })
        );
    }

    /**
     * Возвращает валюту страны по коду ISO3 страны
     *
     * @param string $iso3
     * @return null
     */
    public function currencyByCountry($iso3)
    {
        $currencies = ['rus' => 'RUB', 'blr' => 'BYN', 'kaz' => 'KZT', 'arm' => 'AMD', 'kgz' => 'KGS', 'ukr' => 'UAH'];
        if (array_key_exists($iso3, $currencies)) {
            return $currencies[$iso3];
        }

        return null;
    }

    public function paginator($page, $total, $per_page)
    {
        $page = max(1, $page);
        $pages = (int)ceil($total / $per_page);
        $page = max(1, min($page, $pages));

        $limit = array(
            ($page - 1) * $per_page,
            $per_page
        );

        return array(
            'total'    => $total,
            'per_page' => $per_page,
            'page'     => $page,
            'pages'    => $pages,
            'limit'    => $limit
        );
    }

    public function pagination(array $params)
    {
        $defaults = array(
            'total'         => 0,
            'per_page'      => 0,
            'page'          => 1,
            'pages'         => 1,
            'get'           => array(),
            'mode'          => 'get',
            'hash_divider'  => '/',
            'param_divider' => ':',
            'attrs'         => [],
            'nb'            => 1,
            'prev'          => '←',
            'next'          => '→',
            'page_var'      => 'page',
            'url'           => '',
            'url_before'    => ''
        );
        $params = array_merge($defaults, $params);

        if ($params['pages'] < 2) {
            return '';
        }

        if ($params['mode'] === 'get') {
            $params['param_divider'] = '=';
            $params['hash_divider'] = '&';
        }

        if (is_array($params['get'])) {
            array_walk($params['get'], function (&$item, $key, $divider) {
                if (!is_numeric($key)) {
                    $item = urlencode($key) . $divider . urlencode($item);
                }
            }, $params['param_divider']);

            $params['get'] = $params['mode'] === 'get' ? implode('&', $params['get']) : implode($params['hash_divider'], $params['get']);
        }

        $html = '<ul';
        foreach ($params['attrs'] as $k => $v) {
            $html .= ' ' . $k . '="' . $v . '"';
        }
        $html .= '>';

        if ($params['page'] > 1 && $params['prev']) {
            $page_url = implode(
                $params['hash_divider'],
                array_filter([$params['page'] == 2 ? '' : $params['page_var'] . $params['param_divider'] . $params['page'], $params['get']])
            );

            if ($params['url_before']) {
                $page_url = $params['url_before'] . $page_url;
            }

            $html .= '<li><a class="inline-link" href="' . $page_url . '">' . $params['prev'] . '</a></li>';
        }
        $p = 1;
        $n = 1;
        $total = $params['pages'];
        $nb = $params['nb'];
        $page = $params['page'];
        $url = $params['url'];

        while ($p <= $total) {
            if ($p > $nb && ($total - $p) > $nb && abs($page - $p) > $n && ($p < $page ? ($page - $n - $p > 1) : ($total - $nb > $p))) {
                $p = $p < $page ? $page - $n : $total - $nb + 1;
                $html .= '<li><span>&hellip;</span></li>';
            } else {
                $page_url = implode(
                    $params['hash_divider'],
                    array_filter([$p == 1 ? '' : $params['page_var'] . $params['param_divider'] . $p, $params['get']])
                );
                if ($url && $page_url) {
                    $page_url = $url . $params['mode'] === 'get' ? '?' . $page_url : '#' . $page_url;
                }
                if ($params['url_before']) {
                    $page_url = $params['url_before'] . $page_url;
                }
//                $page_url = ($url && $p == 1 ? ($url_params ? '?' . $url_params : '') : '?page=' . $p . ($url_params ? '&' . $url_params : ''));
                $html .= '<li' . ($p == $page ? ' class="selected"' : '') . '><a href="' . $page_url . '">' . $p . '</a></li>';
                $p++;
            }
        }

        if ($page < $total && $params['next']) {
            $page_url = implode(
                $params['hash_divider'],
                array_filter([$params['page_var'] . $params['param_divider'] . (string)($page + 1), $params['get']])
            );

            if ($params['url_before']) {
                $page_url = $params['url_before'] . $page_url;
            }

            $html .= '<li><a class="inline-link" href="' . $page_url . '">' . $params['next'] . '</a></li>';
        }

        $html .= '</ul>';

        return $html;
    }

    /**
     * Применяет заданный коэффициент к товарам, чтобы начислить общую скидку на товар
     *
     * @param array $items
     * @param float $coef число от 0 до 1. Если больше 1 наценка получится :-)
     * @return array
     */
    public function spreadOrderDiscount(array $items, $coef)
    {
        foreach ($items as $k => $v) {
            $items[$k]['total_discount'] += ($v['price'] * $v['quantity'] - $v['total_discount']) * $coef;
            if (array_key_exists('services', $v) && $v['services'] && is_array($v['services'])) {
                $items[$k]['services'] = $this->spreadOrderDiscount($v['services'], $coef);
            }
        }

        return $items;
    }

    /**
     * Возвращает только товары, к цене которых добавлены услуги
     *
     * @see combineOrderItemServices этот метод вкладывает услуги в товар
     * @see typecastOrderItems чтобы сложение было сложением
     * @param array $items товары с вложенными услугами
     * @return array
     * @throws waException
     */
    public function sumServicesWithProducts(array $items)
    {
        return array_map(
            function ($item) {
                if (array_key_exists('services', $item)) {
                    if (is_array($item['services'])) {
                        foreach ($item['services'] as $s) {
                            $price = ifset($s, 'price', 0.0);
                            $product_tax_percent = ifset($item, 'tax_percent', null);
                            $product_tax_included = ifset($item, 'tax_included', true);
                            $service_tax_percent = ifset($s, 'tax_percent', $product_tax_percent);
                            $service_tax_included = ifset($s, 'tax_included', $product_tax_included);

                            if (($product_tax_percent != $service_tax_percent) || ($product_tax_included != $service_tax_included) || ($s['quantity'] != $item['quantity'])) {
                                if ($service_tax_percent && !$service_tax_included) {
                                    $price += $price / $s['quantity'] * $service_tax_percent / 100;
                                }
                                if (($item['quantity'] != $s['quantity'])) {
                                    $price = $price * $s['quantity'] / $item['quantity'];
                                }
                                if (!$product_tax_included && $product_tax_percent) {
                                    $price = ($price - $s['total_discount'] / $s['quantity']) / (100 + $item['tax_percent']) * 100 + $s['total_discount'] / $s['quantity'];
                                }
                            }

                            $item['price'] += $price;
                            $item['purchase_price'] += ifset($s, 'purchase_price', 0.0);
                            $item['total_discount'] += ifset($s, 'total_discount', 0.0);
                        }
                    }
                    unset($item['services']);
                }
                return $item;
            },
            $items
        );
    }

    public function extractOrderParameters($params)
    {
        $params = array_filter($params, function ($p) {
            return mb_strpos($p, 'sdekint_plugin.', null, 'UTF-8') === 0;
        }, ARRAY_FILTER_USE_KEY);

        if (!$params) {
            return [];
        }
        $tariff_types = ['door' => 'дверь', 'stock' => 'склад', 'postomat' => 'постамат'];

        $params = $this->expand($params);
        $params = (array)array_shift($params);

        $result = array(
            'dispatch_number' => ifset($params, 'dispatch_number', ''),
            'order_id'        => ifset($params, 'order_id', ''),
        );

        $packages = (array)Hash::get($params, 'packages');
        array_walk($packages, function (&$p) {
            $p['size'] = (array)ifempty($p, 'size', []) + array_fill_keys(['width', 'length', 'height'], '?');
            $p['size_str'] = implode('×', $p['size']);
        });

        $result['packages'] = $packages;
        $result['services'] = array_map(function ($s) {
            $map = [3 => 'Доставка в выходной день', 30 => 'Примерка на дому', 36 => 'Частичная доставка', 37 => 'Осмотр вложения'];
            return array(
                'id'   => $s,
                'name' => ifset($map, $s, '')
            );
        }, (isset($params['services']) && is_array($params['services']) ? $params['services'] : []));

        $result['tariff']['id'] = ifempty($params, 'tariff_id', 0);
        $tariff = array();
        if ($result['tariff']['id']) {
            $tariff = (new shopSdekintPluginTariffModel)->getById($result['tariff']['id']);
        }
        if ($tariff) {
            $result['tariff'] += $tariff;
        } else {
            $result['tariff'] += array(
                'name'   => 'Неизвестный или устаревший',
                'from'   => '',
                'to'     => mb_substr(ifempty($params, 'to', 'delivery_type', ''), 3, null, 'UTF-8'),
                'hidden' => true
            );
        }

        $result['tariff']['full_name'] = sprintf('%s %s–%s (%s)', $result['tariff']['name'], ifset($tariff_types, $result['tariff']['from'], '?'), ifset($tariff_types, $result['tariff']['to'], '?'), $result['tariff']['id']);

        $result['contact'] = (array)ifempty($params, 'to', 'contact', []) + array_fill_keys(['name', 'email', 'phone'], '');
        $result['address'] = (array)ifempty($params, 'to', 'address', []) + array_fill_keys(['country', 'region', 'city', 'street', 'house', 'flat', 'zip'], '');
        if (isset($result['address']['city']) && is_array($result['address']['city'])) {
            $result['address']['city'] = ifempty($result, 'address', 'city', 'name', '');
        }

        $result['address']['oneline'] = implode(', ', array_filter([$result['address']['city'], $result['address']['street'], $result['address']['house'], $result['address']['flat']]));

        $result['address']['stock'] = array();
        if (($result['tariff']['to'] === 'stock') || ($result['tariff']['to'] === 'postomat')) {
            $stock_id = ifempty($params, 'to', 'address', 'stock_id', ifempty($params, 'to', 'stock_id', ''));
            $stock = array();
            if ($stock_id) {
                $stock = (new shopSdekintPluginPvzModel())->getByCode($stock_id);
            }
            if ($stock) {
                $result['address']['stock'] = $stock;
            } else {
                $result['address']['stock'] = array(
                    'code'       => ifempty($stock_id, ''),
                    'name'       => 'Неизвестный или закрытый',
                    'city'       => '',
                    'work_time'  => '',
                    'address'    => '',
                    'phone'      => '',
                    'note'       => '',
                    'point_type' => ''
                );
            }
        }

        $result['schedule'] = (array)ifempty($params, 'schedule', []);
        usort($result['schedule'], function ($a, $b) {
            if ($a['date'] === $b['date']) return 0;
            return $a['date'] > $b['date'] ? 1 : -1;
        });
        array_walk($result['schedule'], function (&$s) {
            $s['name'] = ifempty($s, 'name', '');
            $s['phone'] = ifempty($s, 'phone', '');
            $s['time_range'] = implode('–', [$s['from'], $s['to']]);
            $s['date_human'] = waDateTime::format('shortdate', strtotime($s['date']));
            $addr = ifempty($s, 'address', []);
            $address = array();
            foreach (['street', 'house', 'flat'] as $k) {
                if (ifempty($addr, $k, null)) {
                    $address[] = $addr[$k];
                }
            }
            $s['address_str'] = implode(', ', $address);
        });

        $result['pickup'] = (array)ifset($params, 'pickup', []);

        return $result;
    }

    /**
     * Приводит нужные поля к требуемому типу
     *
     * @param array $items
     * @return array
     */
    public function typecastOrderItems(array $items)
    {
        $typecast = array(
            'price'          => 'float',
            'total_discount' => 'float',
            'tax_percent'    => ['type' => 'float', 'null' => true],
            'purchase_price' => 'float',
            'quantity'       => class_exists('shopZzzfractionalPlugin') ? 'float' : 'int',
            'tax_included'   => 'bool'
        );

        foreach ($items as $k => $v) {
            $items[$k] = self::typecastScalarArrayValues($v, $typecast);
            if (array_key_exists('services', $v) && is_array($v['services'])) {
                $items[$k]['services'] = $this->typecastOrderItems($v['services']);
            }
        }

        return $items;
    }

    /**
     * @param array $arr
     * @param array $keys
     * @return array
     */
    public static function typecastScalarArrayValues(array $arr, array $keys)
    {
        foreach ($keys as $k => $v) {
            if (!is_array($v)) {
                $keys[$k] = ['type' => $v, 'null' => false];
            }
        }

        foreach ($arr as $k => $v) {
            if (array_key_exists($k, $keys)) {
                if (is_scalar($v) || is_null($v)) {
                    if ((!is_null($v) || !ifset($keys, $k, 'null', false))) {
                        switch ($keys[$k]['type']) {
                            case 'float' :
                                $arr[$k] = self::toFloat($v);
                                break;
                            case 'int':
                            case 'integer' :
                            case 'intval' :
                                $arr[$k] = intval($v);
                                break;
                            case 'bool':
                            case 'boolean':
                            case 'boolval':
                                $arr[$k] = boolval($v);
                        }
                    }
                }
            }
        }
        return $arr;
    }

    /**
     * @param array $ar
     * @param callable $func
     * @return array|mixed
     */
    public function arrayFindFirst(array $ar, callable $func)
    {
        foreach ($ar as $key => $val) {
            if (call_user_func($func, $val, $key)) {
                return $val;
            }
        }
        return [];
    }

    /**
     * @param string $phone
     * @param array $options
     * @return false|string
     */
    public static function normalizePhone($phone, $options = array())
    {
        $defaults = array('country' => 'rus');
        $options += $defaults;

        /**
         * @var string $country
         * @var callable $post_callback
         */
        extract($options);

        if (!($_phone = mb_ereg_replace('\D', '', $phone))) {
            return $phone;
        }

        $phone = $_phone;

        switch ($country) {
            case 'rus':
            case 'ru':
                if ((strlen($phone) == 11) && ($phone[0] == '8')) {
                    $phone[0] = '7';
                } elseif (strlen($phone) == 10) {
                    $phone = '7' . $phone;
                }
                break;
        }

        return $phone;
    }

    public static function findMainCdekStateName($id)
    {
        $states = self::listMainCdekStates();
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
    public static function listMainCdekStates()
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
}

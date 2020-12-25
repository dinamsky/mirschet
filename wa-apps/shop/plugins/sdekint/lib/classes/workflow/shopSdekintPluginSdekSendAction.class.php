<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @version 3.2.0
 * @copyright Serge Rodovnichenko, 2016-2017
 * @license http://www.webasyst.com/terms/#eula Webasyst
 */

use PhpUnitsOfMeasure\PhysicalQuantity\Length;
use PhpUnitsOfMeasure\PhysicalQuantity\Mass;
use SergeR\Webasyst\CdekSDK\API\Order\Request\DeliveryRequest;
use SergeR\Webasyst\CdekSDK\API\Order\Response\Type\OrderResult;
use SergeR\Webasyst\CdekSDK\Type\CourierCall;
use SergeR\Webasyst\CdekSDK\Type\CourierCallCollection;
use SergeR\Webasyst\CdekSDK\Type\OrderAddress;
use SergeR\Webasyst\CdekSDK\Type\OrderItem;
use SergeR\Webasyst\CdekSDK\Type\OrderPackage;
use SergeR\Webasyst\CdekSDK\Type\OrderSender;
use SergeR\Webasyst\CdekSDK\Type\ScheduleAttempt;
use SergeR\Webasyst\CdekSDK\Type\ShopOrder;
use SergeR\Webasyst\CdekSDK\Type\ShopOrderCollection;
use SergeR\Webasyst\CdekSDK\Type\StreetAddress;
use SergeR\CakeUtility\Hash;
use shopOrder as webasystShopOrder;

/**
 * Отправка/создание заказа в СДЭК
 */
class shopSdekintPluginSdekSendAction extends shopWorkflowAction
{
    public $errors = array();
    /** @var shopSdekintPlugin */
    protected $plugin;

    public function getButton()
    {
        // This makes the form appear above order instead of in the right sidebar
        return parent::getButton('data-container="#workflow-content"');
    }

    public function getDefaultOptions()
    {
        return array('html' => true) + parent::getDefaultOptions();
    }

    /**
     * @param array $order
     * @return bool
     */
    public function isAvailable($order)
    {
        // С пустым заказом оно из настроек вызывается
        if (is_null($order)) {
            return true;
        }

        $allowed_shipping = $this->plugin->ship_methods;
        if (is_object($order) && ($order instanceof webasystShopOrder)) {
            $order = $order->dataArray();
        }
        if (!($shipping_method = Hash::get($order, 'params.shipping_id'))) {
            return false;
        }

        // Проверка метода доставки, отмечен-ли в настройках?
        if (!in_array((int)$shipping_method, $allowed_shipping)) {
            return false;
        }

        return !ifempty($order, 'params', 'sdekint_plugin.dispatch_number', null);
    }

    /**
     * @param $order_id
     * @return null|string
     * @throws waException
     */
    public function getHTML($order_id)
    {
        $errors = array();
        $settings = array();

        $info = array(
            'stockupdate'   => $this->plugin->stockupdate,
            'stocks'        => [],
            'defaults'      => ['delivery_type' => null, 'pvz_code' => null],
            'package_sizes' => $this->plugin->getPackageDimensions(),
            'debug'         => wa()->getConfig()->isDebug() && $this->plugin->_iddqd_,
            'sellers'       => $this->plugin->sellers
        );
        $order = $this->order_model->getOrder($order_id);

        $packages = array(
            ['id' => (string)time(), 'number' => '1', 'barcode' => '', 'size' => ['length' => null, 'width' => null, 'height' => null], 'weight' => 0.0]
        );
        $products = array_values($this->getSdekItems($order, [
            'use_sku_name'        => $this->plugin->use_sku_name,
            'item_template'       => $this->plugin->item_template,
            'reduce_count'        => $this->plugin->reduce_count,
            'reduce_template'     => $this->plugin->reduce_template,
            'appraised_price'     => $this->plugin->appraised_price,
            'fix_appraised_price' => $this->plugin->fix_appraised_price,
            'drop_quot_amp'       => $this->plugin->drop_quot_amp,
            'packages'            => $packages
        ]));

        // Вес каждой упаковки посчитали как сумму весов товаров в ней (округлили вверх до 1 грамма)
        array_walk($packages, function (&$package) use ($products) {
            $package['weight'] = ceil(array_reduce($products, function ($weight, $product) use ($package) {
                        return $weight + ($package['id'] === $product['package'] ? (float)ifset($product, 'weight', 0.0) * $product['quantity'] : 0.0);
                    }, 0.0) * 1000) / 1000;
        });

        // Подобрали размеры упаковок исходя из их веса
        array_walk($packages, function (&$package) use ($info) {
            foreach ($info['package_sizes'] as $size) {
                if ($package['weight'] > $size['min_weight']) {
                    $package['size'] = ['length' => $size['length'], 'width' => $size['width'], 'height' => $size['height']];
                } else {
                    break;
                }
            }
        });

        // Если в заказе сохранены рассчитанные размеры и вес и у нас одна и только одна упаковка, то выставим
        // вес этой упаковки и её размеры равные рассчитанным значениям
        $total_package_weight = shopSdekintPluginHelper::toFloat((string)Hash::get($order, 'params.package_total_weight'));
        $total_package_length = shopSdekintPluginHelper::toFloat((string)Hash::get($order, 'params.package_total_length'));
        $total_package_width = shopSdekintPluginHelper::toFloat((string)Hash::get($order, 'params.package_total_width'));
        $total_package_height = shopSdekintPluginHelper::toFloat((string)Hash::get($order, 'params.package_total_height'));

        if (count($packages) === 1) {
            if ($total_package_length && $total_package_width && $total_package_height) {
                $total_package_width = (int)shopDimension::getInstance()->convert($total_package_width, 'length', 'cm');
                $total_package_length = (int)shopDimension::getInstance()->convert($total_package_length, 'length', 'cm');
                $total_package_height = (int)shopDimension::getInstance()->convert($total_package_height, 'length', 'cm');
                $packages[0]['size'] = ['width' => $total_package_width, 'length' => $total_package_length, 'height' => $total_package_height];
            }
            if ($total_package_weight) {
                $packages[0]['weight'] = $total_package_weight;
            }
        }

        $products = $this->mapTaxRate($products);

        $address = shopHelper::getOrderAddress($order['params'], 'shipping') +
            ['country' => '', 'city' => '', 'region' => '', 'stock_id' => null, 'street' => '', 'house' => '', 'flat' => ''];

        $address = $this->mapCustomAddressFields($address);
        array_walk($address, function (&$f) {
            $f = (string)$f;
        });
        if (empty($address['stock_id'])) {
            $address['stock_id'] = null;
        }

        $plugin_config = new shopSdekintPluginConfig();
        $cfg_from_door = (array)$plugin_config->get('from_door');

        $sender_city = $this->plugin->sender_city;
        $origin_city = (new shopSdekintPluginCityModel)->getCityByCode(Hash::get($sender_city, 'id', '44'));

        $sdek_order = array(
            'origin'      => array(
                'sender'  => $this->_getSeller($this->plugin->sellers),
                'contact' => [
                    'name'  => (string)Hash::get($cfg_from_door, 'contact.name'),
                    'phone' => (string)Hash::get($cfg_from_door, 'contact.phone')
                ],
                'address' => [
                    'city'    => ['id' => Hash::get($origin_city, 'code', '44'), 'name' => Hash::get($origin_city, 'name', 'Москва')],
                    'country' => Hash::get($origin_city, 'Country.iso3letter', 'rus'),
                    'street'  => (string)Hash::get($cfg_from_door, 'address.street', ''),
                    'house'   => (string)Hash::get($cfg_from_door, 'address.house', ''),
                    'flat'    => (string)Hash::get($cfg_from_door, 'address.flat', '')
                ],
                'courier' => ['date' => date('Y-m-d', strtotime('tomorrow')), 'time_beg' => '9:00', 'time_end' => '18:00'],
                'type'    => null
            ),
            'destination' => array(
                'contact' => array(
                    'name'  => (string)ifempty($order, 'contact', 'name', ''),
                    'email' => (string)ifempty($order, 'contact', 'email', ''),
                    'phone' => (string)ifempty($order, 'contact', 'phone', '')
                ),
                'address' => $address,
                'type'    => null
            ),
            'delivery'    => array(
                'tariff'      => '',
                'cost'        => round(shopSdekintPluginHelper::toFloat(ifempty($order, 'shipping', 0.0)), 2),
                'tax_percent' => $this->plugin->ru_vat_delivery
            ),
            'payment'     => array(
                'paid'              => (bool)ifempty($order, 'paid_date', null),
                'currency'          => ifempty($order, 'currency', 'RUB'),
                'contract_currency' => $this->plugin->contract_currency
            ),
            'items'       => array(
                'packages' => $packages,
                'products' => $products
            ),
            'schedule'    => array(),
            'services'    => array_map('intval', $this->plugin->service),
            'comment'     => '',
            'id'          => shopHelper::encodeOrderId($order['id'])
        );

        $sdek_order['destination']['address']['city'] = array('name' => $address['city'], 'id' => '');

        if ($this->plugin->ru_phone_normalize && $sdek_order['destination']['contact']['phone'] && ($sdek_order['destination']['address']['country'] == 'rus')) {
            $sdek_order['destination']['contact']['phone'] = shopSdekintPluginHelper::normalizePhone($sdek_order['destination']['contact']['phone']);
        }

        // попробуем найти город или города по стране, региону и названию
        $cities = (new shopSdekintPluginCityModel)->findByCountryRegionName(
            $sdek_order['destination']['address']['country'],
            $sdek_order['destination']['address']['region'],
            $sdek_order['destination']['address']['city']['name']
        );

        // по названию нашелся один город
        if ($cities && count($cities) == 1 && (bool)ifset($cities, 0, 'sdek_id', false)) {
            $info['stocks'] = (new shopSdekintPluginPvzModel)->find(['city_code' => $cities[0]['sdek_id']]);
            $sdek_order['destination']['address']['city']['id'] = $cities[0]['sdek_id'];

            // Проверим что за плагин доставки и если sydsek, найдем выбор покупателя по типу доставки
            $info['defaults'] = $this->getDefaultValues($order, $info);
        } else {
            $sdek_order['destination']['address']['city']['name'] = '';
        }

        $info['cities'] = $cities;
        $info['settings'] = ['overwrite_paid' => $this->plugin->overwrite_paid, 'schedule_enabled' => $this->plugin->schedule_enabled];
//        $info['sellers']

        $contract_currency = $this->plugin->contract_currency;
        $pay_currency = $this->plugin->helper->currencyByCountry($sdek_order['destination']['address']['country']);

        foreach (array_unique([$contract_currency, $pay_currency, $sdek_order['payment']['currency']]) as $c) {
            if (!$this->plugin->helper->isCurrencyDefined($c) && !$sdek_order['payment']['paid']) {
                $info['errors']['currency'][] = sprintf('В настройках магазина не задана валюта %s. Конвертация произведена с ошибками', $c);
            }
        }

        $order_currency = $sdek_order['payment']['currency'];
        $pay_currency_vaild = $this->plugin->helper->isCurrencyDefined($pay_currency);
        $contract_currency_valid = $this->plugin->helper->isCurrencyDefined($contract_currency);
        array_walk($sdek_order['items']['products'], function (&$p) use ($order_currency, $contract_currency, $pay_currency, $pay_currency_vaild, $contract_currency_valid) {
            if ($contract_currency_valid && ($order_currency !== $contract_currency)) {
                $p['price'] = round($this->plugin->helper->toFloat(shop_currency($p['price'], $order_currency, $contract_currency, false)), 2);
            }
            if ($pay_currency_vaild && ($order_currency !== $pay_currency)) {
                $p['cost'] = round($this->plugin->helper->toFloat(shop_currency($p['cost'], $order_currency, $pay_currency, false)), 2);
            }
        });
        if ($pay_currency_vaild && ($order_currency !== $pay_currency)) {
            $sdek_order['payment']['currency'] = $pay_currency;
            $sdek_order['delivery']['cost'] = round($this->plugin->helper->toFloat(shop_currency($sdek_order['delivery']['cost'], $order_currency, $pay_currency, false)), 2);
        }

        $this->getView()->assign(compact('info', 'sdek_order'));

        return $this->display();
    }

    /**
     * @param array $order
     * @param array $params
     * @return array<array{
     *     sku_id: string,
     *     sku: string,
     *     name: string,
     *     package: string,
     *     quantity: int,
     *     price: float,
     *     tax_percent: string,
     *     tax_value: float,
     *     cost: float,
     *     weight: float
     * }>
     * @throws waException
     */
    protected function getSdekItems(array $order, array $params = array())
    {
        $default_params = array(
            'use_sku_name'    => false,
            'item_template'   => '',
            'reduce_count'    => 0,
            'reduce_template' => '',
            'drop_quot_amp'   => false,
            'packages'        => array(['id' => (string)time(), 'number' => '1', 'barcode' => '', 'size' => ['length' => null, 'width' => null, 'height' => null], 'weight' => 0.0])
        );
        $params += $default_params;

        /**
         * @todo проверять уникальность кодов/id SKU
         *
         * @var bool $setting_use_sku_name
         * @var string $setting_item_template
         * @var int $setting_reduce_count
         * @var string $setting_reduce_template
         * @var string $setting_appraised_price
         * @var int|float|string $setting_fix_appraised_price
         * @var array $setting_packages
         * @var bool $setting_drop_quot_amp
         */
        extract($params, EXTR_PREFIX_ALL, 'setting');
        $setting_fix_appraised_price = shopSdekintPluginHelper::toFloat($setting_fix_appraised_price);
        $setting_item_template = shopSdekintPluginHelper::trim($setting_item_template);
        if (!empty($setting_item_template)) {
            $setting_item_template = 'string:' . $setting_item_template;
        }
        if (!empty($setting_reduce_template)) {
            $setting_reduce_template = shopSdekintPluginHelper::trim($setting_reduce_template);
        }

        $items = $this->plugin->helper->sumServicesWithProducts(
            $this->plugin->helper->typecastOrderItems(
                $this->plugin->helper->combineOrderItemServices($order['items'])
            )
        );

        $default_currency = wa('shop')->getConfig()->getCurrency(true);
        $items_options = array(
            'default_weight'    => $this->plugin->default_weight,
            'weight'            => 'kg',
            'currency'          => ifset($order['currency'], $default_currency),
            'collapse_quantity' => 1,  /* Настройка для конвертации всех позиций к количеству 1 и переносу реального количества в название */
        );
        $items = $this->plugin->helper->addOrderItemWeight($items, $items_options);

        // Fixes issue #83: weight with gramm decimals.
        array_walk($items, function (&$item) {
            if ($item['type'] !== 'product') {
                return;
            }
            if (isset($item['item'])) {
                $item['item']['weight'] = ceil(max($item['item']['weight'], 0.001) * 1000) / 1000;
            } else {
                $item['weight'] = ceil(max($item['weight'], 0.001) * 1000) / 1000;
            }
        });

        $items_discount = (float)array_reduce(
            $items,
            function ($discount, $item) {
                return $discount + (float)ifset($item, 'total_discount', 0.0);
            },
            0.0
        );

        // здесь осталась скидка на заказ в целом
        $order_discount = $order['discount'] - $items_discount;

        if (($order_discount > 0) && ($order['total'] + $order['discount'] - $order['shipping'] > 0)) {
            $coefficient = $order_discount / ($order['total'] + $order_discount - $order['shipping']);

            if ($coefficient) {
                $items = $this->plugin->helper->spreadOrderDiscount($items, $coefficient);
            }
        }

        // Сворачиваем в один товар если надо
        $same_tax_percent = (count(array_unique(array_column($items, 'tax_percent'))) == 1) && (count(array_unique(array_column($items, 'tax_included'))) == 1);
        if ((bool)$setting_reduce_count && (count($items) >= (int)$setting_reduce_count) && !empty($setting_reduce_template) && $same_tax_percent) {
            $reduced = reset($items);
            $reduced['weight'] = array_reduce($items, function ($sum, $item) {
                return $sum + $item['quantity'] * $item['weight'];
            }, 0.0);
            $reduced['price'] = array_reduce($items, function ($sum, $item) {
                return $sum + $item['quantity'] * $item['price'];
            }, 0.0);
            $reduced['total_discount'] = array_reduce($items, function ($sum, $item) {
                return $sum + $item['total_discount'];
            }, 0.0);
            $reduced['quantity'] = 1;
            $reduced['sku_code'] = '001';
            $reduced['name'] = $setting_reduce_template;
            $items = array($reduced);
        }

        array_walk($items, function (&$item) use ($setting_drop_quot_amp) {
            $item['name'] = htmlspecialchars_decode($item['name']);
            if ($setting_drop_quot_amp) {
                $item['name'] = mb_eregi_replace('\&', ' ', $item['name']);
                $item['name'] = mb_eregi_replace('\"', '', $item['name']);
                $item['name'] = mb_eregi_replace('^\s+|\s+$', '', $item['name']);
            }
        });

        $items = $this->plugin->helper->applyOrderItemTaxes($items);
        $order_paid = !empty($order['paid_date']);

        $items = array_map(
            function ($item) use ($order_paid, $setting_item_template, $setting_use_sku_name, $setting_packages) {
                $cost = 0.0;
                if (!$order_paid) {
                    $cost = $item['price'] - $item['total_discount'] / $item['quantity'];
                    $cost += (bool)ifset($item, 'tax_included', true) ? 0.0 : $item['tax_value'];
                    $cost = round($cost, 2);
                }

                $sku_code = $item['sku_code'] && $setting_use_sku_name ? $item['sku_code'] : $item['sku_id'];

                $name = $item['name'];
                if ($setting_item_template) {
                    $this->getView()->assign('sku', $sku_code);
                    $name = $this->getView()->fetch($setting_item_template);
                }
                $tax_percent = ifset($item, 'tax_percent', null);
                return array(
                    'key'         => waString::uuid(),
                    'sku'         => $sku_code,
                    'name'        => $name,
                    'package'     => ifset($setting_packages, 0, 'id', '1'),
                    'quantity'    => $item['quantity'],
                    'price'       => round($this->getItemPrice($item), 2),
                    'tax_percent' => is_null($tax_percent) ? '-1' : (string)intval($item['tax_percent']),
                    'tax_value'   => $item['tax_value'],
                    'cost'        => $cost,
                    'weight'      => $item['weight']
                );
            },
            $items
        );

        return $items;
    }

    /**
     * Возвращает значения по умолчанию если для расчета доставки использовался плагин sydsek
     *
     */
    private function getDefaultValues($order, $info)
    {
        $selected = array(
            'delivery_type' => 'to-door',
            'pvz_code'      => '',
            'package'       => array()
        );

        $shipping_plugin = ifset($order, 'params', 'shipping_plugin', '');
        if ($shipping_plugin != 'sydsek') {
            return $selected;
        }

        $shipping_variant_id = ifset($order, 'params', 'shipping_rate_id', '');
        if (empty($shipping_variant_id)) {
            return $selected;
        }

        if (substr($shipping_variant_id, 0, 7) != 'TO_DOOR') {
            if (isset($info['stocks']) && !empty($info['stocks'])) {
                foreach ($info['stocks'] as $stock) {
                    if ($stock['code'] == $shipping_variant_id) {
                        $selected['pvz_code'] = $shipping_variant_id;
                        if ($stock['point_type'] == 'PVZ') {
                            $selected['delivery_type'] = 'to-stock';
                        } elseif ($stock['point_type'] == 'POSTOMAT') {
                            $selected['delivery_type'] = 'to-postomat';
                        }
                    }
                }
            }
        }

        // Задел на будующее чтоб выдергивать размеры упаковки, настроенные в нашем расчетном плагине
//        if(isset($order['params']['shipping_id']) && !empty($order['params']['shipping_id'])) {
//            $ShopPlugin = new shopPluginModel();
//            $method_info = $ShopPlugin->getPlugin($order['params']['shipping_id'], shopPluginModel::TYPE_SHIPPING);
//            $shipping_method = shopShipping::getPlugin('sydsek', $order['params']['shipping_id']);
//        }

        return $selected;
    }

    /**
     * Does all the actual work this action needs to do.
     * (declared as public for historical reasons only)
     * @param mixed $params implementation-specific parameter passed to $this->run()
     * @return mixed null if this action failed; any data to pass to $this->postExecute() if completed successfully.
     */
    public function execute($params = null)
    {
        if (is_null($params)) {
            return $params;
        }

        $courier_call = ifset($params, 'courier_call', null);

        $order = (new ShopOrder())->fromArray(array(
            'Number'            => (string)Hash::get($params, 'order.id'),
            'SendCityCode'      => (int)Hash::get($params, 'order.origin.address.city.id'),
            'RecCityCode'       => (int)Hash::get($params, 'order.destination.address.city.id'),
            'RecipientName'     => (string)Hash::get($params, 'order.destination.contact.name'),
            'Phone'             => (string)Hash::get($params, 'order.destination.contact.phone'),
            'TariffTypeCode'    => (int)Hash::get($params, 'order.delivery.tariff'),
            'RecipientCurrency' => (string)Hash::get($params, 'order.payment.currency'),
            'ItemsCurrency'     => (string)Hash::get($params, 'payment.contract_currency', 'RUB')
        ));

        if (($seller = (array)Hash::get($params, 'order.origin.sender')) && Hash::get($seller, 'name')) {
            $order->setSeller($seller);
        }

        if ($mail = (string)Hash::get($params, 'order.destination.contact.email')) {
            $order->setRecipientEmail($mail);
        }

        if ($comment = Hash::get($params, 'order.comment')) {
            $order->setComment($comment);
        }

        $paid = (bool)Hash::get($params, 'order.payment.paid');
        $tax_percents = ['VAT0' => 0, 'VAT10' => 10, 'VAT18' => 18, 'VAT20' => 20];

        // Delivery cost when order isn't paid. Apply tax to delivery for Russia
        if (!$paid && (($delivery_cost = (float)Hash::get($params, 'order.delivery.cost')) > 0)) {
            $order->setDeliveryRecipientCost($delivery_cost);
            if (Hash::get($params, 'order.destination.address.country') === 'rus') {
                $tax = Hash::get($params, 'order.delivery.tax_percent', 'VATX');
                if (array_key_exists($tax, $tax_percents)) {
                    $order->setDeliveryRecipientVATRate($tax);
                    $percent = $tax_percents[$tax];
                    $order->setDeliveryRecipientVATSum(round($percent > 0 ? $delivery_cost - $delivery_cost / (100 + $percent) * 100 : 0, 2));
                }
            }
        }

        // Destination address
        $delivery_address = new OrderAddress();
        switch (Hash::get($params, 'order.destination.type')) {
            case 'to-door' :
                $delivery_address->setStreet((string)Hash::get($params, 'order.destination.address.street'))
                    ->setHouse((string)Hash::get($params, 'order.destination.address.house'));
                if ($flat = Hash::get($params, 'order.destination.address.flat')) {
                    $delivery_address->setFlat($flat);
                }
                break;
            case 'to-stock':
                $delivery_address->setPvzCode((string)Hash::get($params, 'order.destination.address.stock_id'));
                break;
        }
        $order->setAddress($delivery_address);

        // Упаковки и вложения
        $_packages = (array)Hash::get($params, 'order.items.packages');
        foreach ($_packages as $_p) {
            $products = array_filter((array)Hash::get($params, 'order.items.products'), function ($p) use ($_p) {
                return $p['package'] === $_p['id'];
            });
            if (!$products) {
                continue;
            }
            try {
                $package = OrderPackage::fromArray(array(
                    'Number'  => $_p['number'],
                    'BarCode' => (string)Hash::get($_p, 'barcode', $_p['number']),
                    'Weight'  => new Mass($_p['weight'], 'kg'),
                    'SizeA'   => new Length((int)Hash::get($_p, 'size.length'), 'cm'),
                    'SizeB'   => new Length((int)Hash::get($_p, 'size.width'), 'cm'),
                    'SizeC'   => new Length((int)Hash::get($_p, 'size.height'), 'cm')
                ));
            } catch (Exception $e) {
                continue;
            }
            foreach ($products as $product) {
                try {
                    $item = OrderItem::fromArray(array(
                        'WareKey' => $product['sku'],
                        'Comment' => $product['name'],
                        'Cost'    => round($product['price'], 2),
                        'Payment' => $paid ? 0.0 : round($product['cost'], 2),
                        'Weight'  => new Mass($product['weight'], 'kg'),
                        'Amount'  => (int)$product['quantity']
                    ));
                } catch (Exception $e) {
                    continue 2;
                }

                if (!$paid && (Hash::get($params, 'order.destination.address.country') === 'rus')) {
                    $item_tax = Hash::get($product, 'tax_percent', 'VATX');
                    $item->setPaymentVATRate($item_tax);
                    if (array_key_exists($item_tax, $tax_percents)) {
                        $percent = $tax_percents[$item_tax];
                        $item->setPaymentVATSum(round($percent > 0 ? $product['cost'] - $product['cost'] / (100 + $percent) * 100 : 0, 2));
                    }
                }

                $package->addItem($item);
            }

            if ($package->getItems()->count() > 0) {
                $order->getPackage()->add($package);
            }
        }

        // Услуги
        if ($services = (array)Hash::get($params, 'order.services')) {
            $order->setAddService($services);
        }

        // Расписание доставки, если курьер и если указано
        if ((Hash::get($params, 'order.destination.type') === 'to-door') && ($sch = (array)Hash::get($params, 'order.schedule'))) {
            foreach ($sch as $s) {
                try {
                    $attempt = (new ScheduleAttempt)->fromArray(array(
                        'ID'      => 1,
                        'Date'    => new DateTimeImmutable($s['date']),
                        'TimeBeg' => $s['from'],
                        'TimeEnd' => $s['to']
                    ));
                    if (!empty($s['name'])) {
                        $attempt->setRecipientName($s['name']);
                    }
                    if (!empty($s['phone'])) {
                        $attempt->setPhone($s['phone']);
                    }
                    if (!empty($s['address'])) {
                        $address = new StreetAddress();
                        if ($street = (string)Hash::get($s, 'address.street')) {
                            $address->setStreet($street);
                        }
                        if ($house = (string)Hash::get($s, 'address.house')) {
                            $address->setHouse($house);
                        }
                        if ($flat = (string)Hash::get($s, 'address.flat')) {
                            $address->setFlat($flat);
                        }
                        $attempt->setAddress($address);
                    }
                    $order->addSchedule($attempt);
                } catch (Exception $e) {
                    continue;
                }
            }
        }

        // Отправитель
        $sender = new OrderSender();

        try {
            $delivery_request = new DeliveryRequest();
        } catch (Exception $e) {
            $this->errors[] = $e->getCode() . ': ' . $e->getMessage();
            return null;
        }

        // Заявка на забор
        if (Hash::get($params, 'order.origin.type') === 'from-door') {
            try {
                // отправитель еще раз
                $sender = OrderSender::fromArray(array_filter(array(
                    'company' => $sender->getCompany(),
                    'name'    => Hash::get($params, 'order.origin.contact.name'),
                    'phone'   => Hash::get($params, 'order.origin.contact.phone'),
                    'address' => StreetAddress::fromArray(array_filter(array(
                        'Street' => Hash::get($params, 'order.origin.address.street'),
                        'House'  => Hash::get($params, 'order.origin.address.house'),
                        'Flat'   => Hash::get($params, 'order.origin.address.flat')
                    )))
                )));
                if ($sender->isEmpty()) {
                    throw new waException('Не заполнен отправитель', 701);
                }

                $courier_call = CourierCall::fromArray(array(
                    'Date'         => new DateTimeImmutable(Hash::get($params, 'order.origin.courier.date')),
                    'TimeBeg'      => Hash::get($params, 'order.origin.courier.time_beg'),
                    'TimeEnd'      => Hash::get($params, 'order.origin.courier.time_end'),
                    'SendCityCode' => Hash::get($params, 'order.origin.address.city.id'),
                    'SendPhone'    => Hash::get($params, 'order.origin.contact.phone'),
                    'SenderName'   => Hash::get($params, 'order.origin.contact.name'),
                    'SendAddress'  => StreetAddress::fromArray(array_filter(array(
                        'Street' => Hash::get($params, 'order.origin.address.street'),
                        'House'  => Hash::get($params, 'order.origin.address.house'),
                        'Flat'   => Hash::get($params, 'order.origin.address.flat')
                    )))
                ));
                $delivery_request = $delivery_request->addCallCourier($courier_call);

            } catch (Exception $e) {
                $this->errors[] = $e->getCode() . ': ' . $e->getMessage();
                return null;
            }
        }

        // Заявка на доставку
        try {
            if (!$sender->isEmpty()) {
                $order->setSender($sender);
            }
            $delivery_request = $delivery_request->addOrder($order);
        } catch (Exception $e) {
            $this->errors[] = $e->getCode() . ': ' . $e->getMessage();
            return null;
        }

        try {
            $result = $this->plugin->getOrdersApiClient()->deliveryRequest($delivery_request);
            $sdek_order = $result->getOrders()->findFirstByNumber($order->getNumber());
            if (!($sdek_order instanceof OrderResult)) {
                shopSdekintPluginHelper::log(var_export($result, true));
                throw new waException('Неизвестная ошибка');
            }
            if ($sdek_order->isError()) {
                throw new waException($sdek_order->getErrorMessage() ? 'Сервер СДЭК вернул сообщение об ошибке: ' . $sdek_order->getErrorMessage() . ' (' . $sdek_order->getErrorCode() . ')' : 'Неизвестная ошибка');
            }
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return null;
        }

        $packages = [];
        /** @var OrderPackage $p */
        foreach ($order->getPackage() as $p) {
            try {
                $packages[] = array(
                    'number'  => $p->getNumber(),
                    'barcode' => $p->getBarCode(),
                    'weight'  => $p->getWeight()->toUnit('kg'),
                    'size'    => array(
                        'length' => $p->getSizeA()->toUnit('cm'),
                        'width'  => $p->getSizeB()->toUnit('cm'),
                        'height' => $p->getSizeC()->toUnit('cm')
                    )
                );
            } catch (Exception $e) {
            }
        }

        $sdekint_params = array_filter(
                array(
                    'sdekint_plugin.dispatch_number' => $sdek_order->getDispatchNumber(),
                    'sdekint_plugin.tariff_id'       => $order->getTariffTypeCode(),
                    'sdekint_plugin.order_id'        => $order->getNumber(),
                    'tracking_number'                => $this->plugin->track_suggest ? $sdek_order->getDispatchNumber() : '',
                    'sdekint_plugin.comment'         => (string)$order->getComment()
                ))
            + Hash::flatten($params['order']['destination'], '.', 'sdekint_plugin.to.')
            + Hash::flatten($packages, '.', 'sdekint_plugin.packages.');

        if (Hash::get($params, 'order.services')) {
            $sdekint_params += Hash::flatten($params['order']['services'], '.', 'sdekint_plugin.services.');
        }

        if (Hash::get($params, 'order.schedule')) {
            $sdekint_params += Hash::flatten($params['order']['schedule'], '.', 'sdekint_plugin.schedule.');
        }

        if ($courier_call && ($courier_call instanceof CourierCall)) {
            $sdekint_params += Hash::flatten(
                array(
                    'date'    => $courier_call->getDate()->format('Y-m-d'),
                    'from'    => $courier_call->getTimeBeg(),
                    'to'      => $courier_call->getTimeEnd(),
                    'address' => array(
                        'street' => $courier_call->getSendAddress()->getStreet(),
                        'house'  => $courier_call->getSendAddress()->getHouse(),
                        'flat'   => $courier_call->getSendAddress()->getFlat(),
                    )
                ),
                '.', 'sdekint_plugin.pickup.'
            );
        }

        $result = array(
            'text'   => sprintf(
                '<p class="s-order-timeline-message" style="background-color:honeydew">Номер накладной %s</p>',
                $sdek_order->getDispatchNumber()
            ),
            'update' => array(
                'params' => $sdekint_params
            )
        );

        if (Hash::get($params, 'order.origin.type') == 'from-door') {
            $plugin_config = new shopSdekintPluginConfig();
            $plugin_config->set('from_door', array(
                'contact' => ['name' => (string)Hash::get($params, 'order.origin.contact.name'), 'phone' => (string)Hash::get($params, 'order.origin.contact.phone')],
                'address' => [
                    'street' => Hash::get($params, 'order.origin.address.street'),
                    'house'  => Hash::get($params, 'order.origin.address.house'),
                    'flat'   => Hash::get($params, 'order.origin.address.flat'),
                ]
            ))->save();
        }

        return $result;
    }

    /**
     * Для вызова родительского метода нужно отдельно данные подготовить
     * Должен быть id_order и массив для параметров заказа и логов
     *
     * @param array $params те параметры, что и в execute() переданы 'delivery_request' и 'data'
     * @param array $result результат работы execute()
     * @return array|mixed
     */
    public function postExecute($params = null, $result = null)
    {
        if (is_null($params)) {
            return null;
        }
        $order_id = $params['order_id'];
        return parent::postExecute($order_id, $result);
    }

    /**
     * @param array $item
     * @return mixed
     */
    protected function getItemPrice(array $item)
    {
        switch ($this->plugin->appraised_price) {
            case 'fix' :
                return max($this->plugin->fix_appraised_price, 0.0);
                break;
            case 'discounted' :
                return max($item['price'] - $item['total_discount'] / $item['quantity'], 0.0);
                break;
            case 'purchase' :
                return max(0.0, (float)Hash::get($item, 'purchase_price'));
                break;
        }

        return $item['price'];
    }

    /**
     * @param string $template suffix to add to template basename
     * @return string template file basename for this action. Can be overriden in subclasses.
     */
    protected function getTemplateBasename($template = '')
    {
        return 'SdekSend.html';
    }

    /**
     * @return string dir to look template files in (path with trailing slash)
     */
    protected function getTemplateDir()
    {
        return wa()->getAppPath('plugins/sdekint/templates/workflow/', 'shop');
    }

    /**
     * @throws waException
     */
    protected function init()
    {
        $this->plugin = wa('shop')->getPlugin('sdekint');
        parent::init();
    }

    /**
     * @param array $address
     * @return array
     */
    protected function mapCustomAddressFields(array $address)
    {
        foreach (['address_field_street' => 'street', 'address_field_house' => 'house', 'address_field_flat' => 'flat'] as $k => $v) {
            if ($field = $this->plugin->{$k}) {
                $address[$v] = (string)ifset($address, $field, '');
            }
        }

        return $address;
    }

    /**
     * @param array $products
     * @return array
     */
    protected function mapTaxRate(array $products)
    {
        array_walk($products, function (&$p) {
            $tax_percent = ifset($p, 'tax_percent', null);
            if (in_array(mb_strtoupper($tax_percent, 'UTF-8'), ['VATX', 'VAT0', 'VAT10', 'VAT18', 'VAT20'])) {
                return;
            }
            if (!in_array($tax_percent, [null, '-1', '0', '10', '18', '20'])) {
                $tax_percent = null;
            }
            if (is_null($tax_percent)) {
                $tax_percent = '-1';
            } elseif (!$tax_percent) {
                $p['tax_percent'] = $this->plugin->ru_vat_product;
                return;
            }

            $map = ['-1' => 'VATX', '0' => 'VAT0', '10' => 'VAT10', '18' => 'VAT18', '20' => 'VAT20'];
            $p['tax_percent'] = $map[$tax_percent];
        });

        return $products;
    }

    /**
     * @param array $sellers
     * @return array
     */
    protected function _getSeller(array $sellers)
    {
        $result = ['name' => '', 'ownership_form' => null, 'inn' => '', 'address' => '', 'phone' => '', 'id' => null];
        if ($sellers) {
            foreach ($sellers as $seller) {
                if ($seller['_is_default']) {
                    $result = $seller;
                    break;
                }
            }
        }

        return $result;
    }
}

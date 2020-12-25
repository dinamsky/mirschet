<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class shopFlexdiscountPlugin extends shopPlugin
{

    // Купоны, участвующие в скидках.
    private static $coupons = array();

    private static $profile;

    public function __construct($info)
    {
        parent::__construct($info);
        if (self::$profile === null && shopFlexdiscountProfile::isEnabled()) {
            self::$profile = new shopFlexdiscountProfile();
            ini_set('memory_limit', -1);
        }
    }

    public function backendMenu()
    {
        if (wa()->getUser()->isAdmin() || wa()->getUser()->getRights("shop", "flexdiscount_rules")) {
            $output = (new waSmarty3View(wa()))->fetch((new shopFlexdiscountHelper())->getTemplatePath('backend/include.backend.menu.html'));
            return array("core_li" => $output);
        }
    }

    public function backendProductSkuSettings($params)
    {
        if (shopDiscounts::isEnabled('flexdiscount')) {
            $view = new waSmarty3View(wa());
            $view->assign('sku', $params['sku']);
            $view->assign('sku_id', $params['sku_id']);
            $view->assign('currencies', wa()->getConfig()->getCurrencies());
            return $view->fetch((new shopFlexdiscountHelper())->getTemplatePath('backend/include.backend.product.sku.settings.html'));
        }
        return null;
    }

    public function backendProductEdit($product)
    {
        if (shopDiscounts::isEnabled('flexdiscount')) {
            $view = new waSmarty3View(wa());
            $view->assign('currencies', wa()->getConfig()->getCurrencies());
            $view->assign('product', $product->getData());
            return ['basics' => $view->fetch((new shopFlexdiscountHelper())->getTemplatePath('backend/include.backend.product.edit.html'))];
        }
    }

    public function backendSettingsDiscounts()
    {
        $enabled = shopDiscounts::isEnabled('flexdiscount');
        $type = array(
            "id" => "flexdiscount",
            "name" => _wp("Flexdiscount"),
            "url" => "?plugin=flexdiscount&module=settings",
            "status" => ($enabled ? true : false)
        );
        return array('flexdiscount' => $type);
    }

    public function backendOrder($params)
    {
        if (shopDiscounts::isEnabled('flexdiscount')) {
            $output = array('title_suffix' => '', 'action_button' => '', 'action_link' => '', 'info_section' => '');
            $view = new waSmarty3View(wa());

            // Отображение скидок для каждого товара
            if (shopFlexdiscountHelper::getSettings('backend_product_discount') && !empty($params['items'])) {
                foreach ($params['items'] as &$item) {
                    $item['total_discount_html'] = shop_currency_html($item['total_discount'], $params['currency'], $params['currency']);
                    $item['total_html'] = shop_currency_html($item['price'] * $item['quantity'] - $item['total_discount'], $params['currency'], $params['currency']);
                }
                $view->assign('items', $params['items']);
                $view->assign('section', 'info_section');
                $output['info_section'] = $view->fetch((new shopFlexdiscountHelper())->getTemplatePath('backend/include.backend.order.html'));
            }

            if ($coupons = (new shopFlexdiscountCouponPluginModel())->getCouponsByOrderId($params['id'])) {
                $view->assign('coupons', $coupons);
                $view->assign('section', 'action_link');
                $output['action_link'] = $view->fetch((new shopFlexdiscountHelper())->getTemplatePath('backend/include.backend.order.html'));
            }

            return $output;
        }
    }

    public function backendOrders()
    {
        // Выводим часть стилей, чтобы оформить Заказы
        if (shopDiscounts::isEnabled('flexdiscount')) {
            return ['sidebar_section' => (new waSmarty3View(wa()))->fetch((new shopFlexdiscountHelper())->getTemplatePath('backend/include.backend.orders.html'))];
        }
    }

    public function backendOrderEdit($params)
    {
        if (shopDiscounts::isEnabled('flexdiscount')) {
            $params['id'] = !isset($params['id']) ? 0 : $params['id'];
            $view = new waSmarty3View(wa());
            $view->assign('order_id', $params['id']);
            $view->assign('params', $params);
            $view->assign('forceRecalculate', shopFlexdiscountHelper::getSettings('boe_force_calcultate') ? 1 : 0);
            $view->assign('coupons', (new shopFlexdiscountCouponPluginModel())->getCouponsByOrderId($params['id']));
            return $view->fetch((new shopFlexdiscountHelper())->getTemplatePath('backend/include.backend.order.edit.html'));
        }
        return '';
    }

    public function checkoutBeforeShipping($params)
    {
        $this->updateContactFieldsFromParams($params['result']);
    }

    public function checkoutRenderShipping($params)
    {
        // Сохраняем информацию о заказе
        $cache = new waRuntimeCache('flexdiscount_checkout_params');
        $checkout_params = array(
            'shipping' => array('id' => 0, 'rate_id' => 0),
            'contact' => ''
        );
        if ($cache->isCached()) {
            $checkout_params += $cache->get();
        }

        if (!empty($params['vars']['shipping']['selected_variant_id'])) {
            $parts = explode('.', $params['vars']['shipping']['selected_variant_id']);
            $checkout_params['shipping']['id'] = (int) $parts[0];
            $checkout_params['shipping']['rate_id'] = $parts[1];
        }

        // Обновляем контактные поля
        $this->updateContactFieldsFromParams($params['vars']);

        $cache->set($checkout_params);
    }

    public function checkoutRenderPayment($params)
    {
        $checkout_params = array(
            'payment' => 0
        );
        // Сохраняем информацию о заказе
        $cache = new waRuntimeCache('flexdiscount_checkout_params');
        if ($cache->isCached()) {
            $checkout_params += $cache->get();
        }

        if (!empty($params['vars']['payment']['selected_method_id'])) {
            if (!$cache->isCached()) {
                waRequest::setParam('flexdiscount_ss8_force_update', 1);
            }
            $checkout_params['payment'] = (int) $params['vars']['payment']['selected_method_id'];
        }
        $cache->set($checkout_params);
    }

    public function checkoutBeforeConfirm($params)
    {
        // TODO Ждем, когда в бекенде можно будет менять стоимость доставки в заказе
        if (!empty($params['data']['order']['shipping']) && $params['data']['order']['shipping'] > 0) {
            $cache = new waRuntimeCache('flexdiscount_shipping_price');
            $cache->set(['rate' => $params['data']['order']['shipping'], 'currency' => $params['data']['order']['currency']]);
        }
//        // Запускаем вычисление скидок
//        $discount = $params['data']['order']['discount'];
//        $cache = new waRuntimeCache('flexdiscount_shipping_discount');
//        if ($cache->isCached()) {
//            $shipping_discount = $cache->get();
//        }
//
//        // Изменяем цену доставки
//        $params['data']['order']['shipping'] -= $shipping_discount;
//        if ($params['data']['order']['shipping'] < 0) {
//            $params['data']['order']['shipping'] = 0;
//        }
    }

    public function frontendMyNav()
    {
        if (shopDiscounts::isEnabled('flexdiscount')) {
            // Получаем настройки
            $settings = shopFlexdiscountHelper::getSettings();
            if (!empty($settings['flexdiscount_my_discounts']['value'])) {
                $view = new waSmarty3View(wa());
                $view->assign('page_name', !empty($settings['flexdiscount_my_discounts']['page_name']) ? $settings['flexdiscount_my_discounts']['page_name'] : _wp('Your discounts'));
                return $view->fetch((new shopFlexdiscountHelper())->getTemplatePath('frontend/include.frontend.my.nav.html'));
            }
        }
    }

    public function frontendHead()
    {
        if (shopDiscounts::isEnabled('flexdiscount')) {
            $this->addJs('js/flexdiscountFrontend.' . (!waSystemConfig::isDebug() ? 'min.' : '') . 'js');

            $view = new waSmarty3View(wa());
            $view->assign('plugin_id', $this->getId());
            $view->assign('helper', new shopFlexdiscountHelper());
            $view->assign('settings', shopFlexdiscountHelper::getSettings());

            return $view->fetch((new shopFlexdiscountHelper())->getTemplatePath('frontend/include.frontend.head.html'));
        }
    }

    public function frontendFooter()
    {
        if (shopDiscounts::isEnabled('flexdiscount')) {
            $settings = shopFlexdiscountHelper::getSettings();

            // Вывод стилей в подвале сайта
            if (!empty($settings['styles_output']) && $settings['styles_output'] == 'footer') {
                return (new shopFlexdiscountHelper())->getCssStyles();
            }
        }
    }

    public function frontendOrderCartVars(&$params)
    {
        if (shopDiscounts::isEnabled('flexdiscount')) {
            $settings = shopFlexdiscountHelper::getSettings();

            $output = array();
            // Бонусы
            if (shopAffiliate::isEnabled()) {
                $workflow = shopFlexdiscountData::getOrderCalculateDiscount();
                $bonus = $workflow['affiliate'] + (float) $params['affiliate']['add_affiliate_bonus'];
                $params['affiliate']['add_affiliate_bonus'] = $bonus;
            }

            // Подменяем данные на витрине магазина о скидке по купону
            $coupon_discounts = shopFlexdiscountPluginHelper::getActiveCoupons();
            $fl_coupon_discount = 0;
            foreach ($coupon_discounts as $cd) {
                $fl_coupon_discount += $cd['coupon_discount'];
            }
            if (!isset($params['coupon_discount'])) {
                $params['coupon_discount'] = 0;
            }
            $params['coupon_discount'] += $fl_coupon_discount;

            // Вывод примененных скидок
            if (!empty($settings['flexdiscount_user_discounts']['value'])) {
                $output['bottom'] = shopFlexdiscountPluginHelper::getUserDiscounts(!empty($settings['flexdiscount_user_discounts']['type']) ? $settings['flexdiscount_user_discounts']['type'] : 0);
            }

            return $output;
        }
    }

    public function frontendProduct($product)
    {
        if (shopDiscounts::isEnabled('flexdiscount')) {
            $output = array('menu' => '', 'block_aux' => '', 'block' => '', 'cart' => '');
            // Получаем настройки
            $settings = shopFlexdiscountHelper::getSettings();
            // Вывод цены со скидкой
            if (!empty($settings['enable_price_output']['value'])) {
                $price_output_place = isset($settings['price_output_place']) && isset($output[$settings['price_output_place']]) ? $settings['price_output_place'] : 'cart';
                $output[$price_output_place] .= shopFlexdiscountPluginHelper::price($product, 0, !empty($settings['enable_price_output']['type']) ? $settings['enable_price_output']['type'] : 0);
            }
            // Вывод действующих скидок 
            if (!empty($settings['flexdiscount_product_discounts']['value'])) {
                $pd_output_place = isset($settings['pd_output_place']) && isset($output[$settings['pd_output_place']]) ? $settings['pd_output_place'] : 'cart';
                $output[$pd_output_place] .= shopFlexdiscountPluginHelper::getProductDiscounts($product, !empty($settings['flexdiscount_product_discounts']['type']) ? $settings['flexdiscount_product_discounts']['type'] : '');
            }
            // Вывод доступных скидок
            if (!empty($settings['flexdiscount_avail_discounts']['value'])) {
                $ad_output_place = isset($settings['ad_output_place']) && isset($output[$settings['ad_output_place']]) ? $settings['ad_output_place'] : 'block';
                $output[$ad_output_place] .= shopFlexdiscountPluginHelper::getAvailableDiscounts($product, !empty($settings['flexdiscount_avail_discounts']['type']) ? $settings['flexdiscount_avail_discounts']['type'] : '');
            }
            // Вывод правил запрета
            if (!empty($settings['flexdiscount_deny_discounts']['value'])) {
                $deny_output_place = isset($settings['deny_output_place']) && isset($output[$settings['deny_output_place']]) ? $settings['deny_output_place'] : 'block';
                $output[$deny_output_place] .= shopFlexdiscountPluginHelper::getDenyRules($product, !empty($settings['flexdiscount_deny_discounts']['type']) ? $settings['flexdiscount_deny_discounts']['type'] : '');
            }

            return $output;
        }
    }

    public function frontendCart()
    {
        $html = '';
        if (shopDiscounts::isEnabled('flexdiscount')) {
            // Получаем настройки
            $settings = shopFlexdiscountHelper::getSettings();
            // Вывод формы для ввода купонов 
            if (!empty($settings['enable_frontend_cart_hook'])) {
                $html .= shopFlexdiscountPluginHelper::getCouponForm();
            }
            // Вывод примененных скидок
            if (!empty($settings['flexdiscount_user_discounts']['value'])) {
                $html .= shopFlexdiscountPluginHelper::getUserDiscounts(!empty($settings['flexdiscount_user_discounts']['type']) ? $settings['flexdiscount_user_discounts']['type'] : 0);
            }
            // Вывод бонусов
            if (!empty($settings['flexdiscount_affiliate_bonus']['value'])) {
                $html .= shopFlexdiscountPluginHelper::getUserAffiliate(!empty($settings['flexdiscount_affiliate_bonus']['type']) ? $settings['flexdiscount_affiliate_bonus']['type'] : 0);
            }

            $view = wa()->getView();
            // Подменяем данные на витрине магазина о скидке по купону
            $coupon_discounts = shopFlexdiscountPluginHelper::getActiveCoupons();
            $fl_coupon_discount = 0;
            foreach ($coupon_discounts as $cd) {
                $fl_coupon_discount += $cd['coupon_discount'];
            }
            $coupon_discount = $view->getVars('coupon_discount');
            $coupon_discount = $coupon_discount ? ((float) $coupon_discount + $fl_coupon_discount) : ($fl_coupon_discount ? $fl_coupon_discount : null);
            if ($coupon_discount !== null) {
                $view->assign('coupon_discount', $coupon_discount);
            }

            // Подменяем данные на витрине магазина о начисленных бонусах
            if (shopAffiliate::isEnabled()) {
                $workflow = shopFlexdiscountData::getOrderCalculateDiscount();
                $affiliate_bonus = $view->getVars('add_affiliate_bonus');
                $affiliate_bonus = $affiliate_bonus ? ((float) $affiliate_bonus + $workflow['affiliate']) : $workflow['affiliate'];
                $view->assign('add_affiliate_bonus', (!$affiliate_bonus ? '<i class="icon16-flexdiscount loading"></i>' : $affiliate_bonus) . '<span class="fl-affiliate-holder' . (!$affiliate_bonus ? ' fl-hide-block' : '') . '" style="display:none"></span>');
            }
        }
        return $html;
    }

    public function frontendCheckout()
    {
        /* Бесплатная доставка */
        $workflow = shopFlexdiscountData::getOrderCalculateDiscount();
        if ($workflow['active_rules']) {
            $view = wa()->getView();
            $vars = $view->getVars();
            if (isset($vars['shipping']) && isset($vars['total'])) {
                $shipping_price = $vars['shipping'];
                foreach ($workflow['active_rules'] as $active_rule) {
                    if (!empty($active_rule['free_shipping'])) {
                        $shipping_price -= $active_rule['free_shipping'];
                    }
                }
                if ($shipping_price <= 0) {
                    $view->assign('total', $vars['total'] - $vars['shipping']);
                    $view->assign('shipping', 0);
                }
            }
        }
    }

    public function frontendProducts(&$params)
    {
        if (shopDiscounts::isEnabled('flexdiscount')) {
            // Получаем настройки
            $settings = shopFlexdiscountHelper::getSettings();

            // Профилируем
            if (self::$profile) {
                $hook_before = self::$profile->log('frontend_products', 'Before validating plugins');
                self::$profile->stop($hook_before);
            }

            // Запоминаем товары, с которыми предстоит работать.
            // Это делается на случай, если у нас будет использоваться фильтрация по характеристикам.
            // Вместо того, чтобы делать десятки запросов к БД по каждому товару, мы за один раз получим хар-ки для всех товаров
            $data_class = new shopFlexdiscountData();
            if (!empty($params['products'])) {
                $data_class->setShopProducts($params['products']);
            }
            if (!empty($params['skus'])) {
                $product_skus = array();
                foreach ($params['skus'] as $s) {
                    if (isset($params['products'][$s['product_id']])) {
                        $product_skus[$s['id']] = $s['product_id'];
                    }
                }
                if ($product_skus) {
                    $data_class->setShopProductSkus($product_skus);
                }
            }

            // Параметры страницы
            $param = waRequest::param();
            // Игнорировать action
            $action_ignore = (!empty($param['flexdiscount-ignore']) && !empty($param['action']) && $param['action'] == $param['flexdiscount-ignore']) || (!empty($param['flexdiscount_skip_frontend_products']));

            if (empty($settings['frontend_prices']) || $action_ignore || $this->isExternalPluginAllowed()) {
                return;
            }

            // Профилируем
            if (self::$profile) {
                $hook = self::$profile->log('frontend_products', 'After validating plugins');
                self::$profile->stop($hook);
            }

            static $order = null;
            static $in_process = 0;

            // Если необходимо пропустить получение данных из корзины, получаем сразу заказ, чтобы приступить к обработке товара
            $skip_shop_cart = $this->isExternalPluginAllowed('skip_shop_cart_plugins');
            if ($skip_shop_cart) {
                $order = $this->getOrder($skip_shop_cart);
            }

            // Чтобы вычислить цену со скидкой для товара, необходимо знать содержимое заказа.
            // Но, когда мы пытаемся получить содержимое заказа,  при выполнении:
            // $shopCart = new shopCart(); $items = $shopCart->items(false);
            // Происходит вызов frontend_products. Это загонит нас в рекурсию, если не делать проверки.
            if (!$order && !$in_process && !$skip_shop_cart) {
                $in_process = 1;
                $order = $this->getOrder();
            } elseif ($order) {
                // Если есть массив skus, берем его первый элемент.
                // Он понадобится, чтобы определить, какой набор товаров перед нами.
                // Если у элемента будет обнаружена переменная item_id, значит обрабатываются товары из корзины
                // и для них не нужно просчитывать потенциальную скидку
                $first_sku = !empty($params['skus']) ? reset($params['skus']) : array();
                // Правила скидок
                $discount_groups = shopFlexdiscountHelper::getDiscounts();
                // Если нет активных правил, прерываем обработку
                if (!$discount_groups) {
                    return;
                }
                // Текущая валюта
                $current_cur = wa('shop')->getConfig()->getCurrency(false);
                // Основная валюта
                $primary_cur = wa('shop')->getConfig()->getCurrency(true);
                // Рассчитываем потенциальную скидку только, если в обработке не товары из корзины и
                // не происходит добавление или удаление из корзины
                if (
                    !isset($first_sku['item_id']) &&
                    (
                        (isset($param['module'])
                            && $param['module'] !== 'frontendCart'
                            && $param['module'] !== 'frontendOrderCart'
                            && $param['module'] !== 'frontendOrder'
                        )
                        || !isset($param['module'])
                    )) {
                    // Не обрабатываем товары из корзины
                    if (isset($params['products']) &&
                        isset($params['skus']) &&
                        !empty($order['order']['products']) &&
                        !array_diff(array_keys($params['products']), $order['order']['products']) &&
                        !array_diff(array_keys($params['skus']), $order['order']['skus']) &&
                        empty($param['force_flexdiscount_frontend_products']) &&
                        (isset($param['action']) && in_array($param['action'], array('cart', 'checkout', 'order')))
                    ) {
                        return;
                    }

                    // Получаем обработанные товары
                    $cache_work = new waRuntimeCache('flexdiscount_product_workflows');
                    $workflows = $cache_work->get();
                    if (!$workflows) {
                        $workflows = array();
                    }

                    // Профилируем
                    if (self::$profile) {
                        $hook_calc = self::$profile->log('frontend_products', 'Calculating');
                    }
                    if (isset($params['products'])) {
                        foreach ($params['products'] as &$p) {
                            $product = ($p instanceof shopProduct) ? $p->getData() : $p;
                            if (empty($product['sku_id'])) {
                                continue;
                            }
                            $product['type'] = 'product';

                            // Если явно указан артикул, данные которого необходимо получить
                            if ($get_sku_id = waRequest::get('sku')) {
                                $sku = isset($product['skus'][$get_sku_id]) ? $product['skus'][$get_sku_id] : (isset($params['skus'][$get_sku_id]) ? $params['skus'][$get_sku_id] : $product);
                                $product = array_merge($product, array(
                                        // Очень сомнительный момент присвоения цене основной валюты
                                        'price' => isset($sku['primary_price']) ? $sku['primary_price'] : shop_currency($sku['price'], $product['currency'], $primary_cur, false),
                                        'currency' => $primary_cur,
                                        'compare_price' => isset($sku['compare_price']) ? $sku['compare_price'] : 0,
                                        'primary_price' => isset($sku['primary_price']) ? $sku['primary_price'] : shop_currency($sku['price'], $product['currency'], $primary_cur, false),
                                        'purchase_price' => isset($sku['purchase_price']) ? $sku['purchase_price'] : 0
                                    )
                                );
                            }

                            // Добавляем товар к заказу
                            $order_params = (new shopFlexdiscountWorkflow())->addToOrder($product, $primary_cur);
                            if (!isset($workflows[$product['sku_id']])) {
                                // Вычисляем размер скидки и бонусов
                                $workflow = shopFlexdiscountCore::calculate_discount($order_params, $discount_groups);
                                $product['currency'] = $primary_cur;
                                // Сохраняем результат обработки товара
                                $workflows[$product['sku_id']] = shopFlexdiscountHelper::prepareProductWorkflow($workflow, $product['sku_id'], $current_cur, $product, $order_params);
                                $cache_work->set($workflows);
                            }

                            // Если имеется скидка, то заменяем цены у товаров
                            if (!empty($workflows[$product['sku_id']]['clear_discount'])) {
                                $p['old_compare_price'] = shop_currency($product['compare_price'], $primary_cur, $current_cur, false);
                                $p['old_price'] = shop_currency($product['price'], $primary_cur, $current_cur, false);
                                // Меняем зачеркнутую цену, если не указано иное
                                if (empty($settings['use_original_compare_pr'])) {
                                    $p['compare_price'] = $product['price'];
                                }
                                $p['price'] = shop_currency($workflows[$product['sku_id']]['clear_price'], $current_cur, $primary_cur, false);
                            }

                            // Добавляем переменную в данные о товаре
                            $p['flexdiscount_price'] = isset($workflows[$product['sku_id']]['clear_price']) ? shop_currency($workflows[$product['sku_id']]['clear_price'], $current_cur, $primary_cur, false) : $product['price'];
                            $p['flexdiscount_discount'] = isset($workflows[$product['sku_id']]['clear_discount']) ? $workflows[$product['sku_id']]['clear_discount'] : 0;
                            $p['flexdiscount_affiliate'] = $workflows[$product['sku_id']]['affiliate'] ? $workflows[$product['sku_id']]['affiliate'] : 0;
                            unset($p);
                        }
                    }
                    if (isset($params['skus'])) {

                        // Если отсутствует массив товаров $params['products'], получаем информацию о товарах самостоятельно
                        static $products = array();
                        $find_products = array();
                        foreach ($params['skus'] as $s) {
                            if (!isset($params['products'][$s['product_id']]) && !isset($products[$s['product_id']])) {
                                $find_products[$s['product_id']] = $s['product_id'];
                            }
                        }
                        if ($find_products) {
                            $pm = new shopProductModel();
                            $products += $pm->getByField('id', $find_products, 'id');
                        }

                        foreach ($params['skus'] as &$s) {
                            // Выполняем обработку только, если имеется информация о товаре
                            $sku = $s;
                            $product = isset($params['products'][$sku['product_id']]) ? (is_object($params['products'][$sku['product_id']]) ? $params['products'][$sku['product_id']]->getData() : $params['products'][$sku['product_id']]) : (isset($products[$sku['product_id']]) ? $products[$sku['product_id']] : null);
                            if ($product) {
                                $sku['sku_id'] = $sku['id'];
                                // Для корректного вычисления скидки добавляем недостающие параметры товара
                                $sku['product'] = $product;
                                $sku['currency'] = $product['currency'];
                                $sku['type'] = 'product';

                                if (!isset($workflows[$sku['sku_id']])) {
                                    $order_params = (new shopFlexdiscountWorkflow())->addToOrder($sku, $product['currency']);
                                    // Вычисляем размер скидки и бонусов
                                    $workflow = shopFlexdiscountCore::calculate_discount($order_params, $discount_groups);

                                    // Сохраняем результат обработки товара
                                    $workflows[$sku['sku_id']] = shopFlexdiscountHelper::prepareProductWorkflow($workflow, $sku['sku_id'], $current_cur, $sku, $order_params);
                                    $cache_work->set($workflows);
                                }

                                // Если имеется скидка, то заменяем цены у товаров
                                if (!empty($workflows[$sku['sku_id']]['clear_discount'])) {
                                    $s['old_compare_price'] = $s['compare_price'];
                                    $s['old_price'] = $s['price'];
                                    // Меняем зачеркнутую цену, если не указано иное
                                    if (empty($settings['use_original_compare_pr'])) {
                                        $s['compare_price'] = $s['price'];
                                    }
                                    $s['price'] = shop_currency($workflows[$sku['sku_id']]['clear_price'], $workflows[$sku['sku_id']]['currency'], $sku['currency'], false);
                                }
                                // Добавляем переменную в данные о товаре
                                $s['flexdiscount_price'] = isset($workflows[$sku['sku_id']]['clear_price']) ? shop_currency($workflows[$sku['sku_id']]['clear_price'], $workflows[$sku['sku_id']]['currency'], $sku['currency'], false) : $s['price'];
                                $s['flexdiscount_discount'] = isset($workflows[$sku['sku_id']]['clear_discount']) ? $workflows[$sku['sku_id']]['clear_discount'] : 0;
                                $s['flexdiscount_affiliate'] = $workflows[$sku['sku_id']]['affiliate'] ? $workflows[$sku['sku_id']]['affiliate'] : 0;
                                unset($s);
                            }
                        }
                    }
                    if (self::$profile) {
                        if (!empty($hook_calc)) {
                            self::$profile->stop($hook_calc);
                        }
                    }
                }
            }
        }
    }

    public function orderCalculateDiscount($params)
    {
        static $workflow = null;
        static $applied = null;

        // Профилируем
        if (self::$profile) {
            $profile_hook = self::$profile->log('order_calculate_discount');
        }
        // Сделать настройку для кеширования
        if ($workflow === null || waRequest::param('flexdiscount_force_calculate') || waRequest::param('igaponov_force_calculate')) {
            $workflow = array(
                "discount" => 0,
                "affiliate" => 0,
                "rule_products" => array(),
                "products" => array(),
                "active_rules" => array()
            );
            // Если скидка включена
            if (shopDiscounts::isEnabled('flexdiscount') && !empty($params['order']['items'])) {

                // Профилируем
                if (self::$profile) {
                    $profile_hook_point_calc = self::$profile->log('order_calculate_discount', 'Calculating');
                }

                // Если скидки проверяются из бэкэнда, добавляем товары в набор для дальнейшей работы с ними.
                // или работает плагин "Купить в 1 клик" (quickorder)
                if (wa()->getEnv() == 'backend' || waRequest::param('plugin', '') == 'quickorder') {
                    $data_class = new shopFlexdiscountData();
                    $data_class->setShopProducts($params['order']['items']);
                }

                // Получаем все скидки по группам
                $discount_groups = shopFlexdiscountHelper::getDiscounts();
                // Если нет активных правил, прерываем обработку
                if ($discount_groups) {
                    // Вычисляем размер скидки и бонусов
                    $workflow = shopFlexdiscountCore::calculate_discount($params, $discount_groups);
                }
            }

            // Сохраняем результат обработки
            $cache_order_calc = new waRuntimeCache('flexdiscount_order_calculate');
            $cache_order_calc->set($workflow);
        }

        // Если происходит оформление заказа
        if ($workflow['active_rules'] && $params['apply'] && $applied === null) {
            $coupon_info = shopFlexdiscountCouponPluginModel::couponCheck(isset($params['order']['id']) ? $params['order']['id'] : 0);
            // Если был введен купон
            if ($coupon_info) {
                // Перебираем правила скидок, которые участвовали в работе
                $cm = new shopFlexdiscountCouponPluginModel();
                foreach ($workflow['active_rules'] as $rule) {
                    // Если был использован купон, увеличиваем значение его использования
                    if ($rule['coupon_id']) {
                        // Если один купон использовался в нескольких скидках, то увеличиваем кол-во его использований лишь на один раз
                        if (!isset(self::$coupons[$rule['coupon_id']])) {
                            if (empty($coupon_info[$rule['coupon_id']]['reduced'])) {
                                $cm->useOne($rule['coupon_id'], $rule['clean_coupon']);
                            }
                            self::$coupons[$rule['coupon_id']] = array(
                                'discount' => $rule['discount'],
                                'affiliate' => $rule['affiliate'],
                                'code' => $coupon_info[$rule['coupon_id']]['code'],
                                'reduced' => isset($params['order']['id']) && empty($coupon_info[$rule['coupon_id']]['reduced']) ? 0 : 1
                            );
                        } else {
                            // Суммируем значения скидок и бонусов по купону
                            self::$coupons[$rule['coupon_id']]['discount'] += $rule['discount'];
                            self::$coupons[$rule['coupon_id']]['affiliate'] += $rule['affiliate'];
                        }
                    }
                }
                $applied = 1;
            }
        }

        $return = array(
            'discount' => $workflow['discount'],
            'description' => ''
        );

        // Если были правила, по которым начислились скидки или бонусы
        if ($workflow['active_rules']) {
            // Распределение скидок по товарам
            if ($workflow['products']) {
                $products_with_discount = array();
                foreach ($workflow['products'] as $sku) {
                    $products_with_discount[$sku['item_id']] = array(
                        "description" => "<table class='zebra' style='margin: 10px 0'><tr><th>" . _wp("Discount name") . "</th><th>" . _wp("Discount") . "</th><th>" . _wp("Affiliate") . "</th></tr>",
                        "discount" => $sku['total_discount']
                    );
                    if (!empty($sku['rules'])) {
                        foreach ($sku['rules'] as $r_id => $r) {
                            $products_with_discount[$sku['item_id']]['description'] .= "<tr>";
                            $products_with_discount[$sku['item_id']]['description'] .= "<td>" . str_replace('%', '&percnt;', shopFlexdiscountHelper::escape($workflow['active_rules'][$r_id]['name'])) . "</td>";
                            $products_with_discount[$sku['item_id']]['description'] .= "<td>" . shop_currency_html($r['discount'], $params['order']['currency'], $params['order']['currency']) . "</td>";
                            $products_with_discount[$sku['item_id']]['description'] .= "<td>" . $r['affiliate'] . "</td>";
                            $products_with_discount[$sku['item_id']]['description'] .= "</tr>";
                        }
                        $products_with_discount[$sku['item_id']]['description'] .= "</table>";
                        $products_with_discount[$sku['item_id']]['description'] .= _wp("Total product discount");
                    }
                    $return['discount'] -= $sku['total_discount'];
                }
                $return['items'] = $products_with_discount;
            }

            $return['description'] .= "<div style='margin-bottom: 5px; margin-top: 15px'>" . _wp("Flexdiscount") . '.' . _wp("Total order discount") . '</div>';
            $return['description'] .= "<table class='zebra'>"
                . "<tr><th>" . _wp("Discount name") . "</th><th>" . _wp("Discount") . "</th><th>" . _wp("Affiliate") . "</th></tr>";
            // Список всех скидок
            foreach ($workflow['active_rules'] as $active_rule) {
                // Бесплатная доставка
                if (!empty($active_rule['free_shipping'])) {
                    $params['order']['shipping'] = 0;
                    $params['order']['params']['coupon_free_shipping'] = 1;
                }

                if (!empty($active_rule['name'])) {
                    $return['description'] .= "<tr>";
                    $return['description'] .= "<td>" . str_replace('%', '&percnt;', shopFlexdiscountHelper::escape($active_rule['name'])) . "</td>";
                    $return['description'] .= "<td>" . shop_currency_html($active_rule['discount'], $params['order']['currency'], $params['order']['currency']) . "</td>";
                    $return['description'] .= "<td>" . $active_rule['affiliate'] . "</td>";
                    $return['description'] .= "</tr>";
                }
            }
            $return['description'] .= "</table><br>";
            $return['description'] .= _wp("Total discount");
        }

        // Профилируем
        if (self::$profile) {
            if (!empty($profile_hook)) {
                self::$profile->stop($profile_hook);
            }
            if (!empty($profile_hook_point_calc)) {
                self::$profile->stop($profile_hook_point_calc);
            }
        }

        return $return;
    }

    public function orderActionCreate($data)
    {
        if (shopDiscounts::isEnabled('flexdiscount')) {

            $workflow = shopFlexdiscountData::getOrderCalculateDiscount($data['order_id']);

            if (!empty($workflow['active_rules'])) {
                $needles = [
                    '"type":"cookie"' => 'flexdiscount_cookie',
                    '"type":"session"' => 'flexdiscount_session',
                    '"type":"get"' => 'flexdiscount_get',
                    '"type":"post"' => 'flexdiscount_post',
                    '"type":"server"' => 'flexdiscount_server',
                ];
                $params = $env_vars = [];
                // Отбираем, какие переменные окружения необходимо сохранять.
                foreach ($workflow['active_rules'] as $rule) {
                    if ($needles) {
                        foreach ($needles as $needle => $save_param) {
                            if (strpos($rule["full_info"]["conditions"], $needle) !== false) {
                                $params[$save_param] = 1;
                                unset($needles[$needle]);
                            }
                        }
                    }
                }
                if (isset($params['flexdiscount_cookie'])) {
                    $env_vars['flexdiscount_cookie'] = json_encode(waRequest::cookie());
                }
                if (isset($params['flexdiscount_session'])) {
                    $env_vars['flexdiscount_session'] = json_encode(wa()->getStorage()->getAll());
                }
                if (isset($params['flexdiscount_get'])) {
                    $env_vars['flexdiscount_get'] = json_encode(wa()->getStorage()->get('flexdiscount_get'));
                }
                if (isset($params['flexdiscount_post'])) {
                    $env_vars['flexdiscount_post'] = json_encode(wa()->getStorage()->get('flexdiscount_post'));
                }
                if (isset($params['flexdiscount_server'])) {
                    $env_vars['flexdiscount_server'] = json_encode(wa()->getStorage()->get('flexdiscount_server'));
                }
                $env_vars = array_filter($env_vars, function ($value) {
                    return $value !== 'null';
                });
                if ($env_vars) {
                    // Сохраняем куки и сессию на момент оформления заказа
                    (new shopFlexdiscountOrderParamsPluginModel())->set($data['order_id'], $env_vars);
                }
            }

            // Если был использован купон и заказ оформлен, то запоминаем заказ и присваиваем его купону
            if (!empty(self::$coupons) && !empty($data['order_id'])) {
                $save_coupons = array();
                foreach (self::$coupons as $c_id => $c) {
                    $save_coupons[] = array(
                        "coupon_id" => (int) $c_id,
                        "order_id" => (int) $data['order_id'],
                        "discount" => shopFlexdiscountHelper::floatVal($c['discount']),
                        "affiliate" => (shopAffiliate::isEnabled() ? shopFlexdiscountHelper::floatVal($c['affiliate']) : 0),
                        "code" => $c['code'],
                        "datetime" => date("Y-m-d H:i:s"),
                    );
                }
                $sfcom = new shopFlexdiscountCouponOrderPluginModel();
                $sfcom->multipleInsert($save_coupons);
            }

            // Если были начислены бонусы за заказ, запоминаем их
            $sfam = new shopFlexdiscountAffiliatePluginModel();
            $sfam->saveBonuses($data, $workflow['affiliate']);
        }
    }

    public function orderActionEdit($data)
    {
        // Если был использован купон и заказ оформлен, то запоминаем заказ и присваиваем его купону
        if (!empty(self::$coupons) && !empty($data['order_id'])) {
            $sfcom = new shopFlexdiscountCouponOrderPluginModel();

            // Получаем существующие коды купонов
            $scm = new shopFlexdiscountCouponPluginModel();
            $sql = "SELECT c.id, co.code FROM {$sfcom->getTableName()} co LEFT JOIN {$scm->getTableName()} c ON co.code = c.code WHERE co.order_id = '" . (int) $data['order_id'] . "'";
            $order_coupons = $sfcom->query($sql)->fetchAll('code');

            foreach (self::$coupons as $c_id => $c) {
                // Если текущий купон отработал, то удаляем его из списка на изменение
                if (isset($order_coupons[$c['code']])) {
                    unset($order_coupons[$c['code']]);
                }
                // Если купон еще не использовался, сохраняем его скидки и бонусы
                $update = array(
                    "discount" => shopFlexdiscountHelper::floatVal($c['discount']),
                    "affiliate" => (shopAffiliate::isEnabled() ? shopFlexdiscountHelper::floatVal($c['affiliate']) : 0)
                );
                if (empty($c['reduced'])) {
                    $update['reduced'] = 1;
                }
                $sfcom->updateByField(array("coupon_id" => $c_id, "order_id" => $data['order_id']), $update);
            }
            // Если у нас остались купоны, которые не участвовали в заказе, то обнуляем по ним данные
            if ($order_coupons) {
                foreach ($order_coupons as $coup) {
                    $update = array(
                        "reduced" => 0,
                        "discount" => 0,
                        "affiliate" => 0,
                    );
                    if (empty($coup['id'])) {
                        $update['coupon_id'] = 0;
                    }
                    $sfcom->updateByField(array("code" => $coup['code'], "order_id" => $data['order_id']), $update);
                }
            }
        }
        $workflow = shopFlexdiscountData::getOrderCalculateDiscount($data['order_id']);
        // Списываем старые бонусы, чтобы обновить данные
        $this->orderActionCancelAffiliate($data);

        // Если были начислены бонусы за заказ, запоминаем их
        $sfam = new shopFlexdiscountAffiliatePluginModel();
        $sfam->saveBonuses($data, $workflow['affiliate']);

        // Если следует один из статусов, который предполагает начисление бонусов
        if (in_array($data['after_state_id'], array('paid', 'completed', 'restore'))) {
            $this->orderActionApplyAffiliate($data);
        }
    }

    public function orderActionApplyAffiliate($data)
    {
        if (shopDiscounts::isEnabled('flexdiscount') && shopAffiliate::isEnabled()) {
            $settings = shopFlexdiscountHelper::getSettings();
            $log_text = !empty($settings['flexdiscount_affiliate_bonus']['text']) ? $settings['flexdiscount_affiliate_bonus']['text'] : _wp("Flexdiscount bonus");
            (new shopFlexdiscountAffiliatePluginModel())->updateBonuses($data['order_id'], 'done', $log_text, $data['action_id']);
        }
    }

    public function orderActionCancelAffiliate($data)
    {
        if (shopDiscounts::isEnabled('flexdiscount') && shopAffiliate::isEnabled()) {
            $settings = shopFlexdiscountHelper::getSettings();
            $log_text = !empty($settings['flexdiscount_affiliate_bonus']['text']) ? _wp('Cancel') . ' "' . $settings['flexdiscount_affiliate_bonus']['text'] . '"' : _wp("Cancel flexdiscount bonus");
            (new shopFlexdiscountAffiliatePluginModel())->updateBonuses($data['order_id'], 'cancel', $log_text);
        }
    }

    public function productPreSave($params)
    {
        if (shopDiscounts::isEnabled('flexdiscount')) {
            // Сохраняем минмиальные цены для артикулов
            $minimal_price_for_all = waRequest::post('minimal_price_for_all');
            if (!empty($minimal_price_for_all) && !empty($params['data']['skus'])) {
                foreach ($params['data']['skus'] as &$sku) {
                    // Если артикул редактируется, тогда не меняем его цены
                    if (!isset($sku['flexdiscount_minimal_discount_price'])) {
                        $sku['flexdiscount_minimal_discount_price'] = $params['data']['flexdiscount_minimal_discount_price'];
                        $sku['flexdiscount_minimal_discount_currency'] = $params['data']['flexdiscount_minimal_discount_currency'];
                    }
                }
                $params['instance']['skus'] = $params['data']['skus'];
            }
        }
    }

    public function promoRuleTypes()
    {
        return (new shopFlexdiscountMarketing($this))->getPromoRuleTypes();
    }

    public function backendMarketingSidebar()
    {
        return (new shopFlexdiscountMarketing($this))->getBackendMarketingSidebar();
    }

    public function backendMarketingPromo()
    {
        return (new shopFlexdiscountMarketing($this))->getBackendMarketingPromo();
    }

    public function promoRuleEditor(&$params)
    {
        (new shopFlexdiscountMarketing($this))->getPromoRuleEditor($params);
    }

    public function promoRuleValidate(&$params)
    {
        (new shopFlexdiscountMarketing($this))->getPromoRuleValidate($params);
    }

    public function promoWorkflowRun(&$params)
    {
        return (new shopFlexdiscountMarketing($this))->getPromoWorkflowRun($params);
    }

    public function routing($params = array())
    {
        static $stop = 0;

        // Получаем содержимое заказа, чтобы в методе frontendProducts() не было проблем
        if (!$stop && wa()->getEnv() == 'frontend') {
            if (!$this->isExternalPluginAllowed()) {
                $stop = 1;
                $event_params = array("products" => array(), "skus" => array());
                $this->frontendProducts($event_params);
            }
        }
        return parent::routing($params);
    }

    public function rightsConfig(waRightConfig $config)
    {
        $config->addItem('flexdiscout_header', _wp('Flexdiscount'), 'header');
        $config->addItem('flexdiscount_rules', _wp('Access to discount rules'));
        $config->addItem('flexdiscount_settings', _wp('Access to discount settings'));
    }

    /**
     * Check if plugin is active
     *
     * @return bool
     */
    public static function isEnabled()
    {
        $plugins = wa()->getConfig()->getPlugins();
        return shopDiscounts::isEnabled('flexdiscount') && isset($plugins['flexdiscount']);
    }

    /**
     * Check, should we ignore other plugin requests
     *
     * @param string $ignore_list
     * @return bool
     * @throws waException
     */
    private function isExternalPluginAllowed($ignore_list = 'ignore_plugins')
    {
        static $plugins = [];
        $settings = shopFlexdiscountHelper::getSettings();
        if (!isset($plugins[$ignore_list])) {
            $plugins[$ignore_list] = [];
        }
        if (!empty($settings[$ignore_list])) {
            $active_plugin = waRequest::param('plugin', '');
            if (in_array($active_plugin, $settings[$ignore_list])) {
                $plugins[$ignore_list][$active_plugin] = 1;
                return true;
            } elseif (!$active_plugin) {
                // Для плагинов, которые генерируют выгрузки, необходимо анализировать адрес, откуда пришел запрос, потому что в коде
                // нигде не указывается, что запрос исходит от плагина
                $request_url = wa()->getConfig()->getRequestUrl(false, true);
                // Для заданий по крону проверяем аргументы
                $cli_arg = waRequest::server('argv');
                foreach ($settings[$ignore_list] as $pl) {
                    if (isset($plugins[$ignore_list][$pl])) {
                        return $plugins[$ignore_list][$pl];
                    } else {
                        if (strpos($request_url, $pl) !== false || (!empty($cli_arg[2]) && strpos($cli_arg[2], $pl) !== false) || $this->issetPluginCaller($pl)) {
                            $plugins[$ignore_list][$pl] = 1;
                            return true;
                        } else {
                            $plugins[$ignore_list][$pl] = 0;
                        }
                    }
                }
            }
        }
        return false;
    }

    /**
     * Check, if plugin isset in backtrace
     *
     * @param string $plugin_id
     * @return bool
     */
    private function issetPluginCaller($plugin_id)
    {
        static $backtrace;

        if ($backtrace === null) {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        }
        if ($backtrace) {
            foreach ($backtrace as $b) {
                if (isset($b['file']) && strpos($b['file'], $plugin_id) !== false) {
                    return true;
                }
            }
        }
        return false;
    }

    private function updateContactFieldsFromParams($params)
    {
        $update = array();
        // Обновляем контактные поля
        if (!empty($params['auth']['fields'])) {
            $update = $params['auth']['fields'];
        }
        // Обновляем поля адреса
        if (!empty($params['region']['selected_values'])) {
            $address = array('address.shipping' => array('country' => $params['region']['selected_values']['country_id'], 'region' => $params['region']['selected_values']['region_id'], 'city' => $params['region']['selected_values']['city']));
            $update += $address;
        }
        if ($update) {
            (new shopFlexdiscountHelper())->updateContact($update);
        }
    }

    /**
     * Get current order
     *
     * @param bool $skip_shop_cart - skip calculating total and items from shop cart to prevent redirecting to frontend_products
     * @return array
     * @throws waException
     */
    private function getOrder($skip_shop_cart = false)
    {
        $order = (new shopFlexdiscountWorkflow())->getOrder(0, $skip_shop_cart);
        // Формируем массив из товаров и артикулов. Он требуется, чтобы понимать с каким набором товаром мы работаем.
        // Если нам попались товары, лежащие в корзине, их не трогаем
        if (!empty($order['order']['items'])) {
            $order['order']['products'] = $order['order']['skus'] = array();
            foreach ($order['order']['items'] as $it) {
                $order['order']['products'][$it['product_id']] = $it['product_id'];
                $order['order']['skus'][$it['sku_id']] = $it['sku_id'];
            }
        }
        return $order;
    }
}

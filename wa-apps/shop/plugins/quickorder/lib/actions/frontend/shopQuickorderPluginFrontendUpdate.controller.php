<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class shopQuickorderPluginFrontendUpdateController extends waJsonController
{
    public function execute()
    {
        // Тип формы: товар, корзина
        $type = waRequest::post('qformtype', 'product');

        $cart = new shopQuickorderPluginCart($type);

        if ($cart->issetData('coupon')) {
            $data = wa()->getStorage()->get('shop/checkout');
            $old_coupon = ifset($data, 'coupon_code', '');
            $data['coupon_code'] = $cart->getData('coupon');
            wa()->getStorage()->set('shop/checkout', $data);
        }

        // Общая сумма заказа
        $total = $cart->getTotal();
        // Скидка
        $discount = $cart->getDiscount();

        $view = new waSmarty3View(wa());

        // Заменяем поля доставки
        $shipping = $cart->getMethods('shipping');
        $view->assign('shipping', $shipping);
        $view->assign('no_js', 1);
        $shipping_html = $view->fetch(wa()->getAppPath('plugins/quickorder/templates/actions/frontend/include.shipping.methods.html', 'shop'));

        // Заменяем поля оплаты
        $payment = $cart->getMethods('payment');
        $view->assign('payment', $payment);
        $payment_html = $view->fetch(wa()->getAppPath('plugins/quickorder/templates/actions/frontend/include.payment.methods.html', 'shop'));

        $settings = shopQuickorderPluginHelper::getSettings();
        $form_settings = !empty($settings['shared_display_settings']) ? $settings['product'] : $settings[$type];

        $items = $cart->getItems();

        // Интеграция плагина "Гибкие скидки и бонусы" (flexdiscount)
        // Проверяем доступность плагина
        $use_flexdiscount = 0;
        if ((!empty($settings['use_flexdiscount_ad']) || !empty($settings['flexdiscount_prices'])) && class_exists('shopFlexdiscountPlugin') && shopFlexdiscountPlugin::isEnabled()) {
            $use_flexdiscount = 1;
        }

        if ($use_flexdiscount) {
            // Активные скидки
            if (!empty($settings['use_flexdiscount_ad'])) {
                $workflow = shopFlexdiscountData::getOrderCalculateDiscount();
                $view_type = !empty($settings['flexdiscount_avt']) ? (int) $settings['flexdiscount_avt'] : 0;
                $user_discounts = array(
                    'html' => !empty($workflow['active_rules']) ? shopFlexdiscountPluginHelper::getUserDiscounts($view_type) : '',
                    'collapse' => !empty($settings['collapse_flexdiscount']),
                );
            }
            // Цены со скидкой
            if (!empty($settings['flexdiscount_prices'])) {

                foreach ($items as $item) {
                    if ($item['type'] == 'product') {
                        $prices[$item['sku_id']] = shopFlexdiscountPluginHelper::cartPrice($item, false, 0, false, false);
                    }
                }
            }
        }

        // Бонусная программа
        $affiliate_html = '';
        if (!empty($form_settings['use_affiliate']) && shopAffiliate::isEnabled()) {
            $affiliate = $cart->getAffiliateVars();
            // Интеграция с Гибкими скидками
            if (class_exists('shopFlexdiscountPlugin') && shopFlexdiscountPlugin::isEnabled()) {
                if (!isset($workflow)) {
                    $workflow = shopFlexdiscountData::getOrderCalculateDiscount();
                }
                $affiliate['add_affiliate_bonus'] = $workflow['affiliate'] + (float) $affiliate['add_affiliate_bonus'];
            }
            if (!empty($form_settings['affiliate_text'])) {
                $form_settings['affiliate_text'] = str_replace(array('$bonus', '$discount'), array($affiliate['affiliate_bonus'], $affiliate['affiliate_discount']), $form_settings['affiliate_text']);
            }
            if (!empty($form_settings['affiliate_info']) && !empty($affiliate['add_affiliate_bonus'])) {
                $form_settings['affiliate_info'] = str_replace('$points', $affiliate['add_affiliate_bonus'], $form_settings['affiliate_info']);
            }
            $view->assign('settings', $form_settings);
            $view->assign('affiliate', $affiliate);
            $affiliate_html = $view->fetch(wa()->getAppPath('plugins/quickorder/templates/actions/frontend/include.affiliate.html', 'shop'));
        }

        $quantity = 0;
        foreach ($items as $item) {
            if ($item['type'] == 'product') {
                $quantity += $item['quantity'];
            }
        }

        if ($cart->issetData('coupon')) {
            $data = wa()->getStorage()->get('shop/checkout');
            $data['coupon_code'] = $old_coupon;
            wa()->getStorage()->set('shop/checkout', $data);
        }

        $this->response = array(
            'total' => $total,
            'quantity' => $quantity,
            'discount' => $discount,
            'affiliate' => $affiliate_html,
            'shipping' => $shipping_html,
            'payment' => $payment_html,
            'user_discounts' => !empty($user_discounts) ? $user_discounts : null,
            'prices' => !empty($prices) ? $prices : null,
            'replace_shipping' => waRequest::post('replace_shipping')
        );
    }

}
<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class shopFlexdiscountPluginFrontendUpdateCartController extends shopFlexdiscountPluginJsonController
{

    public function execute()
    {
        $data = waRequest::post('data', array());

        // Обновление цен у товаров
        if (!empty($data['fields'])) {
            $fields = $data['fields'];
            $order = (new shopFlexdiscountWorkflow())->getOrder();
            if (!empty($order['order']['items'])) {
                $c = 0;
                foreach ($order['order']['items'] as $item) {
                    if (isset($fields[$item['id']])) {
                        foreach ($fields[$item['id']] as $k => $i) {
                            $this->response['fields'][$c]['removeClass'] = $k;
                            $this->response['fields'][$c]['elem'] = ".flexdiscount-cart-price.cart-id-" . $item['id'] . "." . $k;
                            $this->response['fields'][$c]['price'] = shopFlexdiscountPluginHelper::cartPrice($item, (int) $i['mult'], (int) $i['html'], (int) $i['format']);
                            $c++;
                        }
                    }
                }
            }
        }

        // Примененные скидки
        if (!empty($data['user_discounts'])) {
            foreach ($data['user_discounts'] as $view_type) {
                $this->response['blocks']['discounts'][$view_type] = shopFlexdiscountPluginHelper::getUserDiscounts($view_type);
            }
        }
        // Бонусы
        if (shopAffiliate::isEnabled()) {
            if (!empty($data['affiliate_block'])) {
                foreach ($data['affiliate_block'] as $view_type) {
                    $this->response['blocks']['affiliate'][$view_type] = shopFlexdiscountPluginHelper::getUserAffiliate($view_type);
                }
            }

            $workflow = shopFlexdiscountData::getOrderCalculateDiscount();
            $cart = new shopCart();
            $add_affiliate_bonus = shopAffiliate::calculateBonus(array(
                'total' => $cart->total(),
                'items' => $cart->items(false)
            ));
            $bonus = $workflow['affiliate'] + (float) $add_affiliate_bonus;
            $this->response['clear_affiliate'] = $bonus;
        }
    }

}

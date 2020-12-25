<?php

class shopGiftcardPluginFrontendBuycardController extends waJsonController
{
    public function execute()
    {
		$giftcard_plugin = waSystem::getInstance('shop')->getPlugin('giftcard');
		if(!$giftcard_plugin->is_on()) 
		{
			$this->getResponse()->setStatus(404);
			return;
		}
		
		// Получение данных корзины
		$code = waRequest::cookie('shop_cart');
        if (!$code) 
		{
            $code = md5(uniqid(time(), true));
            wa()->getResponse()->addHeader('P3P', 'CP="NOI ADM DEV COM NAV OUR STP"');
            wa()->getResponse()->setCookie('shop_cart', $code, time() + 30 * 86400, null, '', false, true);
        }
        $cart = new shopCart($code);
        $cart_model = new shopCartItemsModel();
		
		// Получение данных о номинале карты
		$giftcard_sku = $giftcard_plugin->check_card_sku(waRequest::post('sku_id'));
		if(!$giftcard_sku) {$this->response = array('result' => 'error', 'message' => 'Произошла ошибка при покупке сертификата. Приносим извинения за неудобства.'); return;}
		
		// Добавление карты в корзину
		$item = $cart_model->getItemByProductAndServices($code, $giftcard_plugin->get_card_id(), $giftcard_sku['id'], array());
		if($item) {$cart->setQuantity($item['id'], $item['quantity'] + 1);}
		else
		{
			$data = array(
							'create_datetime' => date('Y-m-d H:i:s'),
							'product_id' => $giftcard_plugin->get_card_id(),
							'sku_id' => $giftcard_sku['id'],
							'quantity' => 1,
							'type' => 'product'
						 );
			//$item_id = $cart_model->insert($data);
			$cart->addItem($data, array());
		}
		$this->response = array('result' => 'ok', 'message' => $giftcard_sku['name'].' добавлен в Вашу корзину.', 'cart_total' => shop_currency_html($cart->total(), true));
	}
}
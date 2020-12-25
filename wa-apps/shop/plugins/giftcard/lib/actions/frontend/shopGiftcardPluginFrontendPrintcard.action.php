<?php

class shopGiftcardPluginFrontendPrintcardAction extends waViewAction
{
	public function execute()
	{
		$giftcard_plugin = waSystem::getInstance('shop')->getPlugin('giftcard');
		if(!$giftcard_plugin->is_on()) 
		{
			$this->setLayout(new shopFrontendLayout());
			$this->getResponse()->setStatus(404);
			$this->view->assign('error_code', '404');
			$this->view->assign('error_message', 'Товар не найден');
			$this->setThemeTemplate('error.html');
			return;
		}
		
		$coupon_id = intval(waRequest::get('coupon_id'));
		$brute_protection = waRequest::get('hash');
		
		$coupon = $giftcard_plugin->get_frontend_coupon($coupon_id, $brute_protection);
		if(!$coupon) {$this->view->assign('gc_error', true);}
		else 
		{
			$this->view->assign('gc_error', false);
			$this->view->assign('gc_coupon', $coupon);
		}
		
		$this->view->assign('gc_shopname', htmlspecialchars(wa()->getSetting('name', '-Укажите имя в настройках приложения Магазин-', 'shop')));
		$this->view->assign('gc_shopmail', htmlspecialchars(wa()->getSetting('email', '-Укажите email в настройках приложения Магазин-', 'shop')));
		$this->view->assign('gc_shopphone', htmlspecialchars(wa()->getSetting('phone', '-Укажите контактный телефон в настройках приложения Магазин-', 'shop')));
		$this->view->assign('gc_shop_url', substr(wa()->getRootUrl(true), 0, -1).wa()->getRouteUrl('shop/frontend'));
		
		$html = $this->view->fetch($giftcard_plugin->get_templates_path().'print_card.html');
		$this->view->assign('gc_html', $html);
	}
}
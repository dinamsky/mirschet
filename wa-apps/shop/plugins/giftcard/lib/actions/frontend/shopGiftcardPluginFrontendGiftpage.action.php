<?php

class shopGiftcardPluginFrontendGiftpageAction extends waViewAction
{
	public function execute()
	{
		// Экземпляр плагина
		$giftcard_plugin = waSystem::getInstance('shop')->getPlugin('giftcard');
		$this->setLayout(new shopFrontendLayout());
		if(!$giftcard_plugin->is_on()) 
		{
			$this->getResponse()->setStatus(404);
			$this->view->assign('error_code', '404');
			$this->view->assign('error_message', 'Товар не найден');
			$this->setThemeTemplate('error.html');
			return;
		}

		$this->getResponse()->setTitle($giftcard_plugin->getSettings('frontend_title'));
		$this->getResponse()->setMeta('keywords', $giftcard_plugin->getSettings('frontend_keywords'));
		$this->getResponse()->setMeta('description', $giftcard_plugin->getSettings('frontend_description'));
		
		// Получение списка артикулов товара-карты
		$card_product_skus = $giftcard_plugin->get_card_skus();
		if(!count($card_product_skus)) 
		{
			$this->view->assign('gc_error', true);
			$html = $this->view->fetch($giftcard_plugin->get_templates_path().'frontend_giftcard.html');
			$this->view->assign('gc_html', $html);
			return;
		}
		
		// Передача артикулов в шаблон
		$this->view->assign('gc_skus', $card_product_skus);
		
		// Ошибок нет
		$this->view->assign('gc_error', false);
		$html = $this->view->fetch($giftcard_plugin->get_templates_path().'frontend_giftcard.html');
		$this->view->assign('gc_html', $html);
	}
}
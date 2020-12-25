<?php

class shopGiftcardPluginBackendActions extends waJsonActions
{	
	public function saveTemplateAction()
	{
		$tpl_name = waRequest::post('tpl_name');
		if(!($tpl_name == 'frontend_giftcard' || $tpl_name == 'frontend_order' || $tpl_name == 'print_card'))
		{
			$this->response = array('success' => 0, 'message' => 'Некорректное имя шаблона');
			return;
		}
		$giftcard_plugin = waSystem::getInstance('shop')->getPlugin('giftcard');
		$result = $giftcard_plugin->save_template($tpl_name, waRequest::post('tpl_content'));
		if($result !== true) {$this->response = array('success' => 0, 'message' => $result);}
		else {$this->response = array('success' => 1, 'message' => 'Шаблон успешно сохранен');}
	}
	
	public function resetTemplateAction()
	{
		$tpl_name = waRequest::post('tpl_name');
		if(!($tpl_name == 'frontend_giftcard' || $tpl_name == 'frontend_order' || $tpl_name == 'print_card'))
		{
			$this->response = array('success' => 0, 'message' => 'Некорректное имя шаблона');
			return;
		}
		$giftcard_plugin = waSystem::getInstance('shop')->getPlugin('giftcard');
		$result = $giftcard_plugin->reset_template($tpl_name);
		if($result !== true) {$this->response = array('success' => 0, 'message' => $result);}
		else {$this->response = array('success' => 1, 'message' => 'Шаблон успешно сброшен в изначальное состояние');}
	}
	
	public function savePluginSettingsAction()
	{
		$giftcard_plugin = waSystem::getInstance('shop')->getPlugin('giftcard');
		$settings = waRequest::post('gc_common');
		if(isset($settings['engage_plugin']))
		{
			if(!isset($settings['giftcardid'])) {$this->response = array('success' => 0, 'message' => 'ID подарочной карты - обязательный параметр'); return;}
			$card_id = intval($settings['giftcardid']);
			$product_fits = $giftcard_plugin->check_the_card($card_id);
			if($product_fits !== true) {$this->response = array('success' => 0, 'message' => $product_fits); return;}
		}
		else {$settings['engage_plugin'] = 0;}
		
		$giftcard_plugin->saveSettings($settings);
		
		$this->response = array('success' => 1, 'message' => 'Настройки успешно сохранены'); 
	}
}
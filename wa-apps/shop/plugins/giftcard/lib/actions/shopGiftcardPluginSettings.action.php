<?php

class shopGiftcardPluginSettingsAction extends waViewAction
{
    public function execute()
    {
        $view = wa()->getView();
		$db = new waModel();
		
		// Настройки
		$giftcard_plugin = waSystem::getInstance('shop')->getPlugin('giftcard');
		$giftcard_settings = $giftcard_plugin->getSettings();
		$view->assign('gc_sys_settings', $giftcard_settings);
		
		// Шаблоны
		$tpl_path = $giftcard_plugin->get_templates_path();
		$templates = array();
		$templates['frontend_giftcard'] = file_get_contents($tpl_path.'frontend_giftcard.html');
		$templates['frontend_order'] = file_get_contents($tpl_path.'frontend_order.html');
		$templates['print_card'] = file_get_contents($tpl_path.'print_card.html');
		
		$repo_path = $giftcard_plugin->get_repo_path();
		$templates['frontend_giftcard_original'] = file_get_contents($repo_path.'frontend_giftcard.html');
		$templates['frontend_order_original'] = file_get_contents($repo_path.'frontend_order.html');
		$templates['print_card_original'] = file_get_contents($repo_path.'print_card.html');
		
		$view->assign('gc_templates', $templates);
	}
}

<?php

class shopIncartsPluginSettingsAction extends waViewAction {

    public function execute() {
	
        $plugin = wa('shop')->getPlugin('incarts');
        // получаем все настройки плагина, чтобы передать их в шаблон
        $settings = $plugin->getSettings(); 

        foreach($settings as &$set) {
        	$set = htmlspecialchars($set, ENT_QUOTES);
        }
        
        $this->view->assign('settings', $settings);

    }

}

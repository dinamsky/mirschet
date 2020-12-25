<?php

class siteAdvancedparamsPluginBackendSettingsAction extends waViewAction {
    
    public function execute() {
        $plugin = wa(siteAdvancedparamsPlugin::APP)->getPlugin(siteAdvancedparamsPlugin::PLUGIN_ID);
        // получаем все настройки плагина, чтобы передать их в шаблон
        $settings = $plugin->getSettings();
        $this->view->assign('settings', $settings);
        
    }
} 
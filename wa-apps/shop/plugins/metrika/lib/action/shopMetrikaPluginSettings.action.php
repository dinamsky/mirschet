<?php

class shopMetrikaPluginSettingsAction extends waViewAction
{
    public function execute()
    {
        $plugin = wa('shop')->getPlugin('metrika');
        $settings = $plugin->getSettings();
        $this->view->assign('settings', $settings);
    }
}
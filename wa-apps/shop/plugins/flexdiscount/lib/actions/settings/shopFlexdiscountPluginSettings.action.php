<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class shopFlexdiscountPluginSettingsAction extends waViewAction
{

    public function preExecute()
    {
        if (!wa()->getUser()->isAdmin() && !wa()->getUser()->getRights("shop", "flexdiscount_settings")) {
            throw new waRightsException();
        }
    }

    public function execute()
    {
        // Получаем настройки
        $settings = (new shopFlexdiscountSettingsPluginModel())->getSettings();
        // Список всех плагинов
        $plugins = wa()->getConfig()->getPlugins();
        unset($plugins['flexdiscount']);

        $this->view->assign('settings', $settings);
        $this->view->assign('js_locale_strings', (new shopFlexdiscountHelper())->getJsLocaleStrings());
        $this->view->assign('enabled', shopDiscounts::isEnabled('flexdiscount'));
        $this->view->assign('plugins', $plugins);
        $this->view->assign('plugin_url', wa()->getPlugin('flexdiscount')->getPluginStaticUrl());
    }

}

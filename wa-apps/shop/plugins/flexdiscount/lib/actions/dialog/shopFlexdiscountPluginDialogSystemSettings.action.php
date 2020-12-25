<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class shopFlexdiscountPluginDialogSystemSettingsAction extends waViewAction
{

    public function preExecute()
    {
        if (!wa()->getUser()->isAdmin() && !wa()->getUser()->getRights("shop", "flexdiscount_settings")) {
            throw new waRightsException();
        }
    }

    public function execute()
    {
        $system_settings = shopFlexdiscountProfile::SETTINGS;
        $settings = [];
        foreach ($system_settings as $key) {
            $settings[$key] = (new waAppSettingsModel())->get(array('shop', 'flexdiscount'), $key);
        }
        $this->view->assign('settings', $settings);
        $this->view->assign('plugin_url', wa()->getPlugin('flexdiscount')->getPluginStaticUrl());
    }

}

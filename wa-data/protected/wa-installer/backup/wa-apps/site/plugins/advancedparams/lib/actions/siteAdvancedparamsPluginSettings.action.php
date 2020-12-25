<?php

class siteAdvancedparamsPluginSettingsAction extends waViewAction
{
    public function execute() {
        $this->view->assign('banned_fields', siteAdvancedparamsPlugin::getConfigParam('banned_fields'));
        $this->view->assign('field_types', siteAdvancedparamsPlugin::getConfigParam('field_types'));
        $this->view->assign('field_types_selectable', siteAdvancedparamsPlugin::getConfigParam('field_types_selectable'));
    }
}

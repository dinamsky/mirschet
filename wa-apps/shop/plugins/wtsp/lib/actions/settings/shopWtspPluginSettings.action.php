<?php
/**
*
*/
class shopWtspPluginSettingsAction extends waViewAction
{

	public function execute(){
        $plugin = wa('shop')->getPlugin('wtsp');

        $model = new shopWtspPluginModel();

        $this->view->assign('list', $model->getAll() );

		$settings =  wa('shop')->getPlugin("wtsp")->getSettings();
        $this->view->assign('settings', isset($settings['settings']) ? $settings['settings'] : array("whatsapp" => 1, "viber" => 1) );
		
	}
}

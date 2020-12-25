<?php


class shopLastmodifiedPluginSettingsAction extends waViewAction
{
	public function execute()
	{
		$plugin = wa('shop')->getPlugin('lastmodified');
		$settings_storage = shopLastmodifiedPlugin::getConfiguration()->getSettingsStorage();
		$settings = $settings_storage->getAll();
		$arr_settings = array();
		
		foreach ($settings as $setting)
		{
			$arr_settings[$setting->getGroupId()] = $setting->getSettings();
		}
		
		$this->view->assign(array(
			'version' => $plugin->getVersion(),
			'settings' => $arr_settings,
		));
	}
}
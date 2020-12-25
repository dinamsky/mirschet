<?php


class shopLastmodifiedPluginSettingsSaveController extends waJsonController
{
	public function execute()
	{
		$arr_settings = waRequest::post('settings', array());
		
		$settings_storage = shopLastmodifiedPlugin::getConfiguration()->getSettingsStorage();
		$settings = $settings_storage->getAll();
		
		foreach ($settings as $setting)
		{
			if (!isset($arr_settings[$setting->getGroupId()]))
			{
				continue;
			}
			
			$setting->setSettings($arr_settings[$setting->getGroupId()]);
			$settings_storage->store($setting);
		}
	}
}
<?php

class shopLastmodifiedSettingsStorage
{
	private $settings_config;
	private $settings_model;
	
	public function __construct($settings_config)
	{
		$this->settings_config = $settings_config;
		$this->settings_model = new shopLastmodifiedSettingsModel();
	}
	
	/**
	 * @return shopLastmodifiedSettings[]
	 */
	public function getAll()
	{
		$result = array();

		foreach ($this->settings_config as $group => $fields)
		{
			$settings = $this->getSettings($group);
			
			if (!isset($settings))
			{
				continue;
			}
			
			$result[] = $settings;
		}

		return $result;
	}
	
	/**
	 * @return shopLastmodifiedSettingsPlugin
	 */
	public function getSettingsPlugin()
	{
		$settings = $this->getSettings('plugin');
		
		return $settings instanceof shopLastmodifiedSettingsPlugin ? $settings : null;
	}
	
	/**
	 * @return shopLastmodifiedSettingsGroup
	 */
	public function getSettingsGroup($group_id)
	{
		$settings = $this->getSettings($group_id);
		
		return $settings instanceof shopLastmodifiedSettingsGroup ? $settings : null;
	}
	
	/**
	 * @param $group_id
	 * @return shopLastmodifiedSettings
	 */
	private function getSettings($group_id)
	{
		if (!isset($this->settings_config[$group_id]))
		{
			return null;
		}
		
		$group_settings_config = $this->settings_config[$group_id];
		
		try
		{
			$rows = $this->settings_model->getByField(array(
				'group' => $group_id,
				'name' => array_keys($group_settings_config)
			), true);
		}
		catch (Exception $e)
		{
			return null;
		}
		
		$arr_settings = array();
		
		foreach ($group_settings_config as $name => $config)
		{
			$arr_settings[$name] = ifset($config['default']);
		}
		
		foreach ($rows as $row)
		{
			$name = $row['name'];
			$value = $row['value'];
			
			if (isset($group_settings_config[$name]['values'])
				&& !in_array($value, $group_settings_config[$name]['values']))
			{
				$value = ifset($group_settings_config[$name]['default']);
			}
			
			$arr_settings[$name] = $value;
		}
		if ($group_id == 'plugin')
		{
			$settings = new shopLastmodifiedSettingsPlugin();
		}
		else
		{
			$settings = new shopLastmodifiedSettingsGroup();
		}
		
		$settings->setGroupId($group_id);
		$settings->setSettings($arr_settings);
		
		return $settings;
	}

	public function store(shopLastmodifiedSettings $settings)
	{
		foreach ($settings->getSettings() as $name => $value)
		{
			$this->settings_model->replace(array(
				'group' => $settings->getGroupId(),
				'name' => $name,
				'value' => $value,
			));
		}
	}
}
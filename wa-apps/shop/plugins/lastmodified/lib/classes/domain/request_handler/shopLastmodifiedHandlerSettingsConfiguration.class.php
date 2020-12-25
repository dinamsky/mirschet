<?php


class shopLastmodifiedHandlerSettingsConfiguration implements shopLastmodifiedRequestHandlerConfiguration
{
	private $settings_plugin;
	private $settings;
	
	public function __construct(shopLastmodifiedSettingsPlugin $settings_plugin, shopLastmodifiedSettingsGroup $settings)
	{
		$this->settings_plugin = $settings_plugin;
		$this->settings = $settings;
	}
	
	public function getType()
	{
		return $this->settings_plugin->getType();
	}
	
	public function getFor()
	{
		return $this->settings->getFor();
	}
	
	public function getDate()
	{
		return $this->settings->getDate();
	}
	
	public function getAllowAgents()
	{
		return $this->settings->getAllowAgents();
	}
}
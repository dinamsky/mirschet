<?php


class shopLastmodifiedRequestHandlerBuilder
{
	private $settings_plugin;
	private $hash_storage;
	private $settings_storage;
	
	public function __construct(
		shopLastmodifiedHashStorage $hash_storage,
		shopLastmodifiedSettingsStorage $settings_storage
	)
	{
		$this->settings_plugin = $settings_storage->getSettingsPlugin();
		$this->hash_storage = $hash_storage;
		$this->settings_storage = $settings_storage;
	}
	
	public function buildBySettingsGroup($group_id, shopLastmodifiedDataSelector $data_selector)
	{
		$settings = $this->settings_storage->getSettingsGroup($group_id);
		
		if (!isset($settings))
		{
			return null;
		}
		
		$configuration = new shopLastmodifiedHandlerSettingsConfiguration($this->settings_plugin, $settings);
		
		return new shopLastmodifiedRequestHandler($configuration, $this->hash_storage, $data_selector);
	}
}
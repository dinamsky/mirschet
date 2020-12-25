<?php

class shopCatalogreviewsPluginSettingsService
{
	private $plugin_settings_storage;

	private $settings;

	public function __construct(shopCatalogreviewsPluginSettingsStorage $plugin_settings_storage)
	{
		$this->plugin_settings_storage = $plugin_settings_storage;
	}

	public function getSettings()
	{
		if (!isset($this->settings))
		{
			$this->settings = $this->plugin_settings_storage->getSettings();
		}

		return $this->settings;
	}
}

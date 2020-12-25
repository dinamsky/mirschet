<?php

class shopCatalogreviewsPluginSettingsStorage
{
	private $settings_source;

	public function __construct(shopCatalogreviewsPluginSettingsSource $settings_source)
	{
		$this->settings_source = $settings_source;
	}

	/**
	 * @return shopCatalogreviewsPluginSettings
	 */
	public function getSettings()
	{
		$raw_settings = $this->settings_source->fetchSettings();

		$settings = new shopCatalogreviewsPluginSettings();
		$settings->setRawSettings($raw_settings);

		return $settings;
	}

	public function saveSettings(shopCatalogreviewsPluginSettings $settings)
	{
		return $this->settings_source->storeSettings($settings->getRawSettings());
	}
}

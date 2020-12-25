<?php

class shopCatalogreviewsCategorySettingsStorage
{
	private $settings_source;

	public function __construct(shopCatalogreviewsCategorySettingsSource $settings_source)
	{
		$this->settings_source = $settings_source;
	}

	/**
	 * @param $category_id
	 * @return shopCatalogreviewsCategorySettings
	 */
	public function getSettings($category_id)
	{
		$raw_settings = $this->settings_source->fetchSettings($category_id);

		$settings = new shopCatalogreviewsCategorySettings();
		$settings->setRawSettings($raw_settings);

		return $settings;
	}

	public function saveSettings($category_id, shopCatalogreviewsCategorySettings $settings)
	{
		return $this->settings_source->storeSettings(
			$category_id,
			$settings->getRawSettings()
		);
	}
}

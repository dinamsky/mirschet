<?php

class shopCatalogreviewsPluginStorefrontSettingsStorage
{
	private $settings_source;

	public function __construct(shopCatalogreviewsPluginStorefrontSettingsSource $settings_source)
	{
		$this->settings_source = $settings_source;
	}

	public function getGeneralStorefrontSettings()
	{
		return $this->getStorefrontSettings(shopCatalogreviewsGeneralStorefront::NAME);
	}

	public function getDefaultSettings()
	{
		return new shopCatalogreviewsPluginStorefrontSettings();
	}

	/**
	 * @param string $storefront
	 * @return shopCatalogreviewsPluginStorefrontSettings|null
	 */
	public function getStorefrontSettings($storefront)
	{
		$raw_settings = $this->settings_source->fetchStorefrontSettings($storefront);

		if (count($raw_settings) === 0)
		{
			return null;
		}

		$settings = new shopCatalogreviewsPluginStorefrontSettings();
		$settings->setRawSettings($raw_settings);

		return $settings;
	}

	public function saveGeneralStorefrontSettings(shopCatalogreviewsPluginStorefrontSettings $settings)
	{
		return $this->settings_source->storeStorefrontSettings(shopCatalogreviewsGeneralStorefront::NAME, $settings->getRawSettings());
	}

	public function saveStorefrontSettings($storefront, shopCatalogreviewsPluginStorefrontSettings $settings)
	{
		return $this->settings_source->storeStorefrontSettings($storefront, $settings->getRawSettings());
	}

	public function deleteStorefrontSettings($storefront)
	{
		return $this->settings_source->deleteStorefrontSettings($storefront);
	}

	public function getStorefrontsWithPersonalSettings()
	{
		return $this->settings_source->getStorefrontsWithPersonalSettings();
	}
}

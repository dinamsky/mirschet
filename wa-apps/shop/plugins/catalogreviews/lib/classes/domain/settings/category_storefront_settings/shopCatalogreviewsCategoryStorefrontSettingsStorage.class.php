<?php

class shopCatalogreviewsCategoryStorefrontSettingsStorage
{
	private $settings_source;

	public function __construct(shopCatalogreviewsCategoryStorefrontSettingsSource $settings_source)
	{
		$this->settings_source = $settings_source;
	}

	/**
	 * @param $storefront
	 * @param $category_id
	 * @return shopCatalogreviewsCategoryStorefrontSettings|null
	 */
	public function getStorefrontSettings($storefront, $category_id)
	{
		$raw_settings = $this->settings_source->fetchSettings($storefront, $category_id);

		if (count($raw_settings) === 0)
		{
			return null;
		}

		$settings = new shopCatalogreviewsCategoryStorefrontSettings();
		$settings->setRawSettings($raw_settings);

		return $settings;
	}

	/**
	 * @param $category_id
	 * @return shopCatalogreviewsCategoryStorefrontSettings|null
	 */
	public function getGeneralStorefrontSettings($category_id)
	{
		return $this->getStorefrontSettings(shopCatalogreviewsGeneralStorefront::NAME, $category_id);
	}

	public function getDefaultSettings()
	{
		return new shopCatalogreviewsCategoryStorefrontSettings();
	}

	public function saveSettings($storefront, $category_id, shopCatalogreviewsCategoryStorefrontSettings $settings)
	{
		return $this->settings_source->storeSettings(
			$storefront,
			$category_id,
			$settings->getRawSettings()
		);
	}

	public function saveGeneralStorefrontSettings($category_id, shopCatalogreviewsCategoryStorefrontSettings $general_settings)
	{
		return $this->saveSettings(
			shopCatalogreviewsGeneralStorefront::NAME,
			$category_id,
			$general_settings
		);
	}

	public function getStorefrontsWithPersonalSettings($category_id)
	{
		return $this->settings_source->getStorefrontsWithPersonalSettings($category_id);
	}

	public function deleteSettings($storefront_to_clear, $category_id)
	{
		return $this->settings_source->deleteSettings($storefront_to_clear, $category_id);
	}
}

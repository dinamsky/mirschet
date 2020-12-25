<?php

interface shopCatalogreviewsPluginStorefrontSettingsSource
{
	public function fetchStorefrontSettings($storefront);

	public function storeStorefrontSettings($storefront, array $raw_settings);

	public function deleteStorefrontSettings($storefront);

	public function getStorefrontsWithPersonalSettings();
}

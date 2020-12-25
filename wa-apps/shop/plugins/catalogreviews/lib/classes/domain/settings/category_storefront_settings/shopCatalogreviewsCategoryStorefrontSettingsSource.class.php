<?php

interface shopCatalogreviewsCategoryStorefrontSettingsSource
{
	public function fetchSettings($storefront, $category_id);

	public function storeSettings($storefront, $category_id, array $raw_settings);

	public function getStorefrontsWithPersonalSettings($category_id);

	public function deleteSettings($storefront_to_clear, $category_id);
}

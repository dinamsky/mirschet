<?php

interface shopCatalogreviewsCategorySettingsSource
{
	public function fetchSettings($category_id);

	public function storeSettings($category_id, array $raw_settings);
}

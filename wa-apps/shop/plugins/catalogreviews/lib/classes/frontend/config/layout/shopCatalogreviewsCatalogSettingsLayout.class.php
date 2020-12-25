<?php

/**
 * Class shopCatalogreviewsCatalogSettingsLayout
 *
 * @property-read string $meta_title
 * @property-read string $meta_description
 * @property-read string $meta_keywords
 * @property-read string $h1
 * @property-read string $description
 * @property-read string $default_sort
 * @property-read boolean $is_client_sorting_enabled
 */
class shopCatalogreviewsCatalogSettingsLayout extends shopCatalogreviewsFixedLayout
{
	/** @return string[] */
	protected function getFixedLayoutFields()
	{
		return [
			'meta_title',
			'meta_description',
			'meta_keywords',
			'h1',
			'description',
			'default_sort',
			'is_client_sorting_enabled',
		];
	}
}

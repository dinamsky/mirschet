<?php

/**
 * Class shopCatalogreviewsCategoryStorefrontSettings
 *
 * @property string $default_sort
 * @property string $client_sorting_mode
 * @property string $filters_mode
 * @property array $filters
 * @property string $catalog_meta_title
 * @property string $catalog_meta_description
 * @property string $catalog_meta_keywords
 * @property string $catalog_h1
 * @property string $catalog_description
 */
class shopCatalogreviewsCategoryStorefrontSettings extends shopCatalogreviewsSettings
{
	public function getSettingsSpecification()
	{
		return [
			'default_sort' => [
				'type' => shopCatalogreviewsRawSettingsAccess::TYPE_TEXT,
				'default_value' => '',
			],
			'client_sorting_mode' => [
				'type' => shopCatalogreviewsRawSettingsAccess::TYPE_TEXT,
				'default_value' => shopCatalogreviewsCategoryClientSortingMode::INHERITED,
			],
			'filters_mode' => [
				'type' => shopCatalogreviewsRawSettingsAccess::TYPE_TEXT,
				'default_value' => shopCatalogreviewsCategoryFilterMode::INHERITED,
			],
			'filters' => [
				'type' => shopCatalogreviewsRawSettingsAccess::TYPE_JSON_ARRAY,
				'default_value' => [],
			],
			'catalog_meta_title' => [
				'type' => shopCatalogreviewsRawSettingsAccess::TYPE_TEXT,
				'default_value' => '',
			],
			'catalog_meta_description' => [
				'type' => shopCatalogreviewsRawSettingsAccess::TYPE_TEXT,
				'default_value' => '',
			],
			'catalog_meta_keywords' => [
				'type' => shopCatalogreviewsRawSettingsAccess::TYPE_TEXT,
				'default_value' => '',
			],
			'catalog_h1' => [
				'type' => shopCatalogreviewsRawSettingsAccess::TYPE_TEXT,
				'default_value' => '',
			],
			'catalog_description' => [
				'type' => shopCatalogreviewsRawSettingsAccess::TYPE_TEXT,
				'default_value' => '',
			],
		];
	}
}

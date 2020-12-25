<?php

/**
 * Class shopCatalogreviewsPluginStorefrontSettings
 *
 * @property string $url_type
 * @property boolean $remove_category_keyword
 * @property string $default_sort
 * @property boolean $is_client_sorting_enabled
 * @property string $reviews_page_link_display_mode
 * @property string $reviews_page_link_template
 * @property string $reviews_grouping_mode
 * @property string $catalog_meta_title
 * @property string $catalog_meta_description
 * @property string $catalog_meta_keywords
 * @property string $catalog_h1
 * @property string $catalog_description
 */
class shopCatalogreviewsPluginStorefrontSettings extends shopCatalogreviewsSettings
{
	public function getSettingsSpecification()
	{
		return [
			'url_type' => [
				'type' => shopCatalogreviewsRawSettingsAccess::TYPE_TEXT,
				'default_value' => shopCatalogreviewsPluginUrlType::KEYWORD_POSTFIX,
			],
			'remove_category_keyword' => [
				'type' => shopCatalogreviewsRawSettingsAccess::TYPE_BOOLEAN,
				'default_value' => true,
			],
			'default_sort' => [
				'type' => shopCatalogreviewsRawSettingsAccess::TYPE_TEXT,
				'default_value' => shopCatalogreviewsReviewsSort::CREATE_DATETIME_DESC,
			],
			'is_client_sorting_enabled' => [
				'type' => shopCatalogreviewsRawSettingsAccess::TYPE_BOOLEAN,
				'default_value' => false,
			],
			'reviews_page_link_display_mode' => [
				'type' => shopCatalogreviewsRawSettingsAccess::TYPE_TEXT,
				'default_value' => shopCatalogreviewsReviewsPageLinkDisplayMode::SIDEBAR,
			],
			'reviews_page_link_template' => [
				'type' => shopCatalogreviewsRawSettingsAccess::TYPE_TEXT,
				'default_value' => 'Отзывы на {$category.name}',
			],
			'reviews_grouping_mode' => [
				'type' => shopCatalogreviewsRawSettingsAccess::TYPE_TEXT,
				'default_value' => shopCatalogreviewsReviewsGroupingMode::SIMPLE,
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

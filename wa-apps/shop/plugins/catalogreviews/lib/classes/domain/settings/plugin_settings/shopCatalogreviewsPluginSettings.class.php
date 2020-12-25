<?php

/**
 * Class shopCatalogreviewsPluginSettings
 *
 * @property bool $is_enabled
 * @property int $cache_ttl_seconds
 * @property int $reviews_per_page
 */
class shopCatalogreviewsPluginSettings extends shopCatalogreviewsSettings
{
	public function getSettingsSpecification()
	{
		return [
			'is_enabled' => [
				'type' => shopCatalogreviewsRawSettingsAccess::TYPE_BOOLEAN,
				'default_value' => false,
			],
			'cache_ttl_seconds' => [
				'type' => shopCatalogreviewsRawSettingsAccess::TYPE_INT,
				'default_value' => 600,
			],
			'reviews_per_page' => [
				'type' => shopCatalogreviewsRawSettingsAccess::TYPE_INT,
				'default_value' => 20,
			],
		];
	}
}

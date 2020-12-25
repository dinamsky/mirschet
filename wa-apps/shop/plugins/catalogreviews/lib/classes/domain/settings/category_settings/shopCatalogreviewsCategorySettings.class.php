<?php

/**
 * Class shopCatalogreviewsCategorySettings
 *
 * @property bool $is_enabled
 */
class shopCatalogreviewsCategorySettings extends shopCatalogreviewsSettings
{
	public function getSettingsSpecification()
	{
		return [
			'is_enabled' => [
				'type' => shopCatalogreviewsRawSettingsAccess::TYPE_BOOLEAN,
				'default_value' => false,
			],
		];
	}
}

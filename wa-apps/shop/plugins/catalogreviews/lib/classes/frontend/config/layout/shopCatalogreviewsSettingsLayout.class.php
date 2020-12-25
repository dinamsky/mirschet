<?php

class shopCatalogreviewsSettingsLayout implements shopCatalogreviewsILayout
{
	private $settings;
	private $mapper;

	public function __construct(shopCatalogreviewsSettings $settings, shopCatalogreviewsSettingsAssocMapper $mapper)
	{
		$this->settings = $settings;
		$this->mapper = $mapper;
	}

	/** @return array */
	public function getLayoutAssoc()
	{
		return $this->mapper->settingsToAssoc($this->settings);
	}

	/**
	 * @param string $field_name
	 * @return bool
	 */
	public function hasField($field_name)
	{
		return array_key_exists($field_name, $this->settings->getSettingsSpecification());
	}

	/**
	 * @return shopCatalogreviewsSettings
	 */
	public function getSettings()
	{
		return $this->settings;
	}
}

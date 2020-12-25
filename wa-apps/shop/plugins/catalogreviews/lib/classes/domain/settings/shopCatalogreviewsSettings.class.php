<?php

abstract class shopCatalogreviewsSettings
{
	protected $raw_settings_access;

	public function __construct()
	{
		$this->raw_settings_access = new shopCatalogreviewsRawSettingsAccess($this->getSettingsSpecification());
	}

	public function __get($name)
	{
		return $this->raw_settings_access->__get($name);
	}

	public function __set($name, $value)
	{
		$this->raw_settings_access->__set($name, $value);
	}

	public function getRawSettings()
	{
		return $this->raw_settings_access->getRawSettings();
	}

	public function setRawSettings($raw_settings)
	{
		$this->raw_settings_access->setRawSettings($raw_settings);
	}

	abstract public function getSettingsSpecification();
}

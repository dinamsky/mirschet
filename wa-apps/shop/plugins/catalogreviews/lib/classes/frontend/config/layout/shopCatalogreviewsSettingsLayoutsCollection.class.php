<?php

class shopCatalogreviewsSettingsLayoutsCollection extends shopCatalogreviewsLayoutsCollection
{
	private $settings_assoc_mapper;

	public function __construct(shopCatalogreviewsSettingsLayout $base_layout, shopCatalogreviewsSettingsAssocMapper $settings_assoc_mapper)
	{
		$this->settings_assoc_mapper = $settings_assoc_mapper;

		parent::__construct($base_layout);
	}

	public function getResult()
	{
		$settings_assoc = [];

		foreach ($this->heap as $name => $data)
		{
			$settings_assoc[$name] = $data['value'];
		}

		/** @var shopCatalogreviewsSettingsLayout $base_layout */
		$base_layout = $this->base_layout;
		$settings = $base_layout->getSettings();
		$result_settings = new $settings();

		$this->settings_assoc_mapper->mapAssocToSettings($result_settings, $settings_assoc);

		return new $this->base_layout($result_settings, $this->settings_assoc_mapper);
	}
}

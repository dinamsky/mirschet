<?php

class shopCatalogreviewsSettingsAssocMapper
{
	public function settingsToAssoc(shopCatalogreviewsSettings $settings)
	{
		$result = [];
		foreach ($settings->getSettingsSpecification() as $name => $_)
		{
			$result[$name] = $settings->{$name};
		}

		return $result;
	}

	public function mapAssocToSettings(shopCatalogreviewsSettings $settings, $assoc)
	{
		foreach ($settings->getSettingsSpecification() as $name => $_)
		{
			if (array_key_exists($name, $assoc))
			{
				$settings->{$name} = $assoc[$name];
			}
		}
	}
}

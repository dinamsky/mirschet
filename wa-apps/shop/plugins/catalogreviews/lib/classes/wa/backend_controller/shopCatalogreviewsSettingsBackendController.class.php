<?php

class shopCatalogreviewsSettingsBackendController
{
	private $settings_storage;
	private $storefront_settings_storage;
	private $catalog_template_variables_meta;
	private $settings_assoc_mapper;

	public function __construct(
		shopCatalogreviewsPluginSettingsStorage $settings_storage,
		shopCatalogreviewsPluginStorefrontSettingsStorage $storefront_settings_storage,
		shopCatalogreviewsReviewsCatalogTemplateVariablesMeta $catalog_template_variables_meta,
		shopCatalogreviewsSettingsAssocMapper $settings_assoc_mapper
	)
	{
		$this->settings_storage = $settings_storage;
		$this->storefront_settings_storage = $storefront_settings_storage;
		$this->catalog_template_variables_meta = $catalog_template_variables_meta;
		$this->settings_assoc_mapper = $settings_assoc_mapper;
	}

	public function getState(array $loaded_storefronts)
	{
		return array_merge(
			$this->getSettingsState($loaded_storefronts),
			$this->getEnvState()
		);
	}

	public function getSettingsState(array $loaded_storefronts)
	{
		return [
			'plugin_settings' => $this->getPluginSettings(),
			'plugin_storefront_settings' => $this->getPluginStorefrontSettings($loaded_storefronts),
			'storefronts_with_personal_settings' => $this->storefrontsWithPersonalSettings(),
			'loaded_storefronts' => $loaded_storefronts,
		];
	}

	public function getEnvState()
	{
		return [
			'storefronts' => $this->getAllStorefronts(),
			'reviews_catalog_variables_meta' => $this->getVariablesMeta(),
		];
	}

	public function getStorefrontSettingsState($storefront)
	{
		$settings = $this->storefront_settings_storage->getStorefrontSettings($storefront);

		if ($settings)
		{
			return [
				'has_storefront_settings' => true,
				'plugin_storefront_settings' => $this->settings_assoc_mapper->settingsToAssoc($settings),
			];
		}
		else
		{
			return ['has_storefront_settings' => false];
		}
	}

	public function save($state, array &$loaded_storefronts)
	{
		$plugin_settings_assoc = $state['plugin_settings'];
		$plugin_storefront_settings_assoc = $state['plugin_storefront_settings'];
		$storefronts_with_personal_settings = $state['storefronts_with_personal_settings'];
		$storefronts_to_clear = $state['storefronts_to_clear'];

		$success = true;

		$success = $this->savePluginSettings($plugin_settings_assoc) && $success;
		$success = $this->savePluginStorefrontSettings($plugin_storefront_settings_assoc, $storefronts_with_personal_settings, $storefronts_to_clear, $loaded_storefronts) && $success;

		return $success;
	}

	private function getPluginSettings()
	{
		$settings = $this->settings_storage->getSettings();

		return $this->settings_assoc_mapper->settingsToAssoc($settings);
	}

	private function getPluginStorefrontSettings(array $storefronts)
	{
		$result = [];

		$general_settings = $this->storefront_settings_storage->getGeneralStorefrontSettings()
			?: $this->storefront_settings_storage->getDefaultSettings();

		$result[] = [
			'storefront' => shopCatalogreviewsGeneralStorefront::NAME,
			'settings' => $this->settings_assoc_mapper->settingsToAssoc($general_settings),
		];

		foreach ($storefronts as $storefront)
		{
			$settings = $this->storefront_settings_storage->getStorefrontSettings($storefront);

			$result[] = [
				'storefront' => $storefront,
				'settings' => $this->settings_assoc_mapper->settingsToAssoc($settings ?: $general_settings),
			];
		}

		return $result;
	}

	private function savePluginSettings($plugin_settings_assoc)
	{
		$settings = new shopCatalogreviewsPluginSettings();
		$this->settings_assoc_mapper->mapAssocToSettings($settings, $plugin_settings_assoc);

		return $this->settings_storage->saveSettings($settings);
	}

	private function savePluginStorefrontSettings(
		$plugin_storefront_settings_assoc,
		$storefronts_with_personal_settings,
		$storefronts_to_clear,
		&$loaded_storefronts
	)
	{
		$loaded_storefronts_hash = [];

		$mapper = $this->settings_assoc_mapper;

		$general_settings = new shopCatalogreviewsPluginStorefrontSettings();
		$mapper->mapAssocToSettings($general_settings, $plugin_storefront_settings_assoc[shopCatalogreviewsGeneralStorefront::NAME]);

		$success = $this->storefront_settings_storage->saveGeneralStorefrontSettings($general_settings);

		foreach ($storefronts_with_personal_settings as $storefront)
		{
			if (!array_key_exists($storefront, $plugin_storefront_settings_assoc))
			{
				continue;
			}

			$settings = new shopCatalogreviewsPluginStorefrontSettings();
			$mapper->mapAssocToSettings($settings, $plugin_storefront_settings_assoc[$storefront]);

			$success = $this->storefront_settings_storage->saveStorefrontSettings($storefront, $settings) && $success;

			$loaded_storefronts_hash[$storefront] = $storefront;
		}

		foreach ($storefronts_to_clear as $storefront_to_clear)
		{
			$success = $this->storefront_settings_storage->deleteStorefrontSettings($storefront_to_clear) && $success;

			unset($loaded_storefronts_hash[$storefront_to_clear]);
		}

		$loaded_storefronts = array_values($loaded_storefronts_hash);

		return $success;
	}

	private function storefrontsWithPersonalSettings()
	{
		return $this->storefront_settings_storage->getStorefrontsWithPersonalSettings();
	}

	private function getAllStorefronts()
	{
		$routing = wa()->getRouting();
		$domains = $routing->getByApp('shop');
		$storefronts = [];

		foreach ($domains as $domain => $routes)
		{
			foreach ($routes as $route)
			{
				if ((!method_exists($routing, 'isAlias') || !$routing->isAlias($domain)) && isset($route['url']))
				{
					$storefronts[] = $domain . '/' . $route['url'];
				}
			}
		}

		return $storefronts;
	}


	private function getVariablesMeta()
	{
		return $this->catalog_template_variables_meta->getMeta();
	}
}

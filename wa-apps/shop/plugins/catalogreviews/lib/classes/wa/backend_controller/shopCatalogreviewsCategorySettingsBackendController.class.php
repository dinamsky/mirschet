<?php

class shopCatalogreviewsCategorySettingsBackendController
{
	private $category_settings_storage;
	private $category_storefront_settings_storage;
	private $category_source;
	private $catalog_template_variables_meta;
	private $plugin_storefront_settings_storage;
	private $settings_assoc_mapper;

	public function __construct(
		shopCatalogreviewsCategorySettingsStorage $category_settings_storage,
		shopCatalogreviewsCategoryStorefrontSettingsStorage $category_storefront_settings_storage,
		shopCatalogreviewsCategorySource $category_source,
		shopCatalogreviewsReviewsCatalogTemplateVariablesMeta $catalog_template_variables_meta,
		shopCatalogreviewsPluginStorefrontSettingsStorage $plugin_storefront_settings_storage,
		shopCatalogreviewsSettingsAssocMapper $settings_assoc_mapper
	)
	{
		$this->category_settings_storage = $category_settings_storage;
		$this->category_storefront_settings_storage = $category_storefront_settings_storage;
		$this->category_source = $category_source;
		$this->catalog_template_variables_meta = $catalog_template_variables_meta;
		$this->plugin_storefront_settings_storage = $plugin_storefront_settings_storage;
		$this->settings_assoc_mapper = $settings_assoc_mapper;
	}

	public function getSettingsPageState($category_id, array $loaded_storefronts)
	{
		return [
			'category' => $this->getCategory($category_id),
			'storefronts' => $this->getAllStorefronts(),
			'category_settings' => $this->getCategorySettings($category_id),
			'category_storefront_settings' => $this->getCategoryStorefrontSettings($category_id, $loaded_storefronts),
			'storefronts_with_personal_settings' => $this->getStorefrontsWithPersonalSettings($category_id),
			'loaded_storefronts' => $loaded_storefronts,
			'reviews_catalog_variables_meta' => $this->catalog_template_variables_meta->getMeta(),
		];
	}

	public function saveCategoriesSettings($categories_settings_assoc)
	{
		foreach ($categories_settings_assoc as $category_id => $category_settings_assoc)
		{
			$this->saveCategorySettings($category_id, $category_settings_assoc);
		}
	}

	public function getCategoryStorefrontSettingsState($storefront, $category_id)
	{
		$settings = $this->category_storefront_settings_storage->getStorefrontSettings($storefront, $category_id);

		if ($settings)
		{
			return [
				'has_storefront_settings' => true,
				'category_storefront_settings' => $this->settings_assoc_mapper->settingsToAssoc($settings),
			];
		}
		else
		{
			return [
				'has_storefront_settings' => false,
			];
		}
	}

	public function saveSettings($category_id, $state, $loaded_storefronts)
	{
		$category_settings_assoc = $state['category_settings'];
		$category_storefront_settings_assoc = $state['category_storefront_settings'];
		$storefronts_with_personal_settings = $state['storefronts_with_personal_settings'];
		$storefronts_to_clear = $state['storefronts_to_clear'];

		$success = true;

		$success = $this->saveCategorySettings($category_id, $category_settings_assoc) && $success;
		$success = $this->saveCategoryStorefrontSettings(
			$category_id,
			$category_storefront_settings_assoc,
			$storefronts_with_personal_settings,
			$storefronts_to_clear,
			$loaded_storefronts
			) && $success;

		return $success;
	}

	private function getCategory($category_id)
	{
		return $this->category_source->getCategory($category_id);
	}

	private function getCategorySettings($category_id)
	{
		$settings = $this->category_settings_storage->getSettings($category_id);

		return $this->settings_assoc_mapper->settingsToAssoc($settings);
	}

	private function getCategoryStorefrontSettings($category_id, array $storefronts)
	{
		$result = [];

		$category_general_settings = $this->category_storefront_settings_storage->getGeneralStorefrontSettings($category_id)
			?: $this->category_storefront_settings_storage->getDefaultSettings();

		$plugin_general_settings = $this->plugin_storefront_settings_storage->getGeneralStorefrontSettings()
			?: $this->plugin_storefront_settings_storage->getDefaultSettings();

		$result[] = [
			'storefront' => shopCatalogreviewsGeneralStorefront::NAME,
			'settings' => array_merge(
				$this->settings_assoc_mapper->settingsToAssoc($category_general_settings),
				['inherited_is_client_sorting_enabled' => $plugin_general_settings->is_client_sorting_enabled]
			),
		];

		foreach ($storefronts as $storefront)
		{
			$category_storefront_settings = $this->category_storefront_settings_storage->getStorefrontSettings($storefront, $category_id);
			$plugin_storefront_settings = $this->plugin_storefront_settings_storage->getStorefrontSettings($storefront) ?: $plugin_general_settings;

			$result[] = [
				'storefront' => $storefront,
				'settings' => array_merge(
					$this->settings_assoc_mapper->settingsToAssoc($category_storefront_settings ?: $category_general_settings),
					['inherited_is_client_sorting_enabled' => $plugin_storefront_settings->is_client_sorting_enabled]
				),
			];
		}

		return $result;
	}

	private function getStorefrontsWithPersonalSettings($category_id)
	{
		return $this->category_storefront_settings_storage->getStorefrontsWithPersonalSettings($category_id);
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

	private function saveCategorySettings($category_id, $category_settings_assoc)
	{
		$settings = new shopCatalogreviewsCategorySettings();

		$this->settings_assoc_mapper->mapAssocToSettings($settings, $category_settings_assoc);
		return $this->category_settings_storage->saveSettings($category_id, $settings);
	}

	private function saveCategoryStorefrontSettings(
		$category_id,
		$category_storefront_settings_assoc,
		$storefronts_with_personal_settings,
		$storefronts_to_clear,
		&$loaded_storefronts
	)
	{
		$loaded_storefronts_hash = [];

		$mapper = $this->settings_assoc_mapper;

		$general_settings = new shopCatalogreviewsCategoryStorefrontSettings();
		$mapper->mapAssocToSettings($general_settings, $category_storefront_settings_assoc[shopCatalogreviewsGeneralStorefront::NAME]);

		$success = $this->category_storefront_settings_storage->saveGeneralStorefrontSettings($category_id, $general_settings);

		foreach ($storefronts_with_personal_settings as $storefront)
		{
			if (!array_key_exists($storefront, $category_storefront_settings_assoc))
			{
				continue;
			}

			$settings = new shopCatalogreviewsCategoryStorefrontSettings();
			$mapper->mapAssocToSettings($settings, $category_storefront_settings_assoc[$storefront]);

			$success = $this->category_storefront_settings_storage->saveSettings($storefront, $category_id, $settings) && $success;

			$loaded_storefronts_hash[$storefront] = $storefront;
		}

		foreach ($storefronts_to_clear as $storefront_to_clear)
		{
			$success = $this->category_storefront_settings_storage->deleteSettings($storefront_to_clear, $category_id) && $success;

			unset($loaded_storefronts_hash[$storefront_to_clear]);
		}

		$loaded_storefronts = array_values($loaded_storefronts_hash);

		return $success;
	}
}

<?php

/**
 * todo rename!
 * todo layoutFactory
 * Class shopCatalogreviewsConfigStorage
 */
class shopCatalogreviewsConfigStorage
{
	private $plugin_settings_storage;
	private $plugin_storefront_settings_storage;
	private $category_settings_storage;
	private $category_storefront_settings_storage;
	private $settings_assoc_mapper;

	public function __construct(
		shopCatalogreviewsPluginSettingsStorage $plugin_settings_storage,
		shopCatalogreviewsPluginStorefrontSettingsStorage $plugin_storefront_settings_storage,
		shopCatalogreviewsCategorySettingsStorage $category_settings_storage,
		shopCatalogreviewsCategoryStorefrontSettingsStorage $category_storefront_settings_storage,
		shopCatalogreviewsSettingsAssocMapper $settings_assoc_mapper
	)
	{
		$this->plugin_settings_storage = $plugin_settings_storage;
		$this->plugin_storefront_settings_storage = $plugin_storefront_settings_storage;
		$this->category_settings_storage = $category_settings_storage;
		$this->category_storefront_settings_storage = $category_storefront_settings_storage;
		$this->settings_assoc_mapper = $settings_assoc_mapper;
	}

	/**
	 * @param $storefront
	 * @return shopCatalogreviewsPluginConfig
	 */
	public function getPluginConfig($storefront)
	{
		$plugin_settings = $this->plugin_settings_storage->getSettings();
		$current_plugin_storefront_settings = $this->collectPluginStorefrontSettings($storefront);

		return $this->buildPluginConfig(
			$plugin_settings,
			$current_plugin_storefront_settings
		);
	}

	/**
	 * @param $storefront
	 * @param $category_id
	 * @return shopCatalogreviewsFullConfig
	 */
	public function getFullConfig($storefront, $category_id)
	{
		$plugin_settings = $this->plugin_settings_storage->getSettings();
		$category_settings = $this->category_settings_storage->getSettings($category_id);

		$current_plugin_storefront_settings = $this->collectPluginStorefrontSettings($storefront);
		$current_category_storefront_settings = $this->collectCategoryStorefrontSettings($storefront, $category_id);

		$current_catalog_settings_layout = $this->collectTemplateLayout(
			$current_plugin_storefront_settings,
			$current_category_storefront_settings
		);

		return $this->buildFullConfig(
			$plugin_settings,
			$category_settings,
			$current_plugin_storefront_settings,
			$current_category_storefront_settings,
			$current_catalog_settings_layout
		);
	}

	/**
	 * @param shopCatalogreviewsPluginConfig $plugin_config
	 * @return shopCatalogreviewsFullConfig
	 */
	public function getEmptyFullConfig(shopCatalogreviewsPluginConfig $plugin_config)
	{
		return $this->buildEmptyFullConfig($plugin_config);
	}

	/**
	 * @param $storefront
	 * @return shopCatalogreviewsPluginStorefrontSettings
	 */
	private function collectPluginStorefrontSettings($storefront)
	{
		$general_plugin_settings = $this->plugin_storefront_settings_storage->getGeneralStorefrontSettings();
		$storefront_plugin_settings = $this->plugin_storefront_settings_storage->getStorefrontSettings($storefront);
		$default_plugin_settings = $this->plugin_storefront_settings_storage->getDefaultSettings();

		$base_plugin_settings_layout = new shopCatalogreviewsSettingsLayout($default_plugin_settings, $this->settings_assoc_mapper);


		/** @var shopCatalogreviewsPluginStorefrontSettings[] $settings_array */
		$settings_array = [
			$storefront_plugin_settings,
			$general_plugin_settings,
			$default_plugin_settings,
		];

		$plugin_settings_collection = new shopCatalogreviewsSettingsLayoutsCollection($base_plugin_settings_layout, $this->settings_assoc_mapper);
		foreach ($settings_array as $settings)
		{
			if (!$settings)
			{
				continue;
			}

			$plugin_settings_collection->push(new shopCatalogreviewsSettingsLayout($settings, $this->settings_assoc_mapper));
		}

		/** @var shopCatalogreviewsSettingsLayout $result_plugin_settings_layout */
		$result_plugin_settings_layout = $plugin_settings_collection->getResult();

		return $result_plugin_settings_layout->getSettings();
	}

	/**
	 * @param $storefront
	 * @param $category_id
	 * @return shopCatalogreviewsCategoryStorefrontSettings
	 */
	private function collectCategoryStorefrontSettings($storefront, $category_id)
	{
		$general_category_settings = $this->category_storefront_settings_storage->getGeneralStorefrontSettings($category_id);
		$storefront_category_settings = $this->category_storefront_settings_storage->getStorefrontSettings($storefront, $category_id);
		$default_category_settings = $this->category_storefront_settings_storage->getDefaultSettings();

		$base_category_settings_layout = new shopCatalogreviewsSettingsLayout($default_category_settings, $this->settings_assoc_mapper);

		/** @var shopCatalogreviewsCategoryStorefrontSettings[] $settings_array */
		$settings_array = [
			$storefront_category_settings,
			$general_category_settings,
			$default_category_settings,
		];

		$category_settings_collection = new shopCatalogreviewsSettingsLayoutsCollection($base_category_settings_layout, $this->settings_assoc_mapper);

		foreach ($settings_array as $settings)
		{
			if (!$settings)
			{
				continue;
			}

			$category_settings_collection->push(new shopCatalogreviewsSettingsLayout($settings, $this->settings_assoc_mapper));
		}

		/** @var shopCatalogreviewsSettingsLayout $result_category_settings_layout */
		$result_category_settings_layout = $category_settings_collection->getResult();

		return $result_category_settings_layout->getSettings();
	}

	/**
	 * @param shopCatalogreviewsPluginStorefrontSettings $current_plugin_storefront_settings
	 * @param shopCatalogreviewsCategoryStorefrontSettings $current_category_storefront_settings
	 * @return shopCatalogreviewsCatalogSettingsLayout
	 */
	private function collectTemplateLayout(
		shopCatalogreviewsPluginStorefrontSettings $current_plugin_storefront_settings,
		shopCatalogreviewsCategoryStorefrontSettings $current_category_storefront_settings
	)
	{
		$plugin_settings_template_layout_assoc = [
			'meta_title' => $current_plugin_storefront_settings->catalog_meta_title,
			'meta_description' => $current_plugin_storefront_settings->catalog_meta_description,
			'meta_keywords' => $current_plugin_storefront_settings->catalog_meta_keywords,
			'h1' => $current_plugin_storefront_settings->catalog_h1,
			'description' => $current_plugin_storefront_settings->catalog_description,
			'default_sort' => $current_plugin_storefront_settings->default_sort,
			'is_client_sorting_enabled' => $current_plugin_storefront_settings->is_client_sorting_enabled,
		];

		$is_client_sorting_enabled_for_category = $current_category_storefront_settings->client_sorting_mode === shopCatalogreviewsCategoryClientSortingMode::INHERITED
			? null
			: $current_category_storefront_settings->client_sorting_mode === shopCatalogreviewsCategoryClientSortingMode::ENABLED;

		$category_settings_template_layout_assoc = [
			'meta_title' => $current_category_storefront_settings->catalog_meta_title,
			'meta_description' => $current_category_storefront_settings->catalog_meta_description,
			'meta_keywords' => $current_category_storefront_settings->catalog_meta_keywords,
			'h1' => $current_category_storefront_settings->catalog_h1,
			'description' => $current_category_storefront_settings->catalog_description,
			'default_sort' => $current_category_storefront_settings->default_sort,
			'is_client_sorting_enabled' => $is_client_sorting_enabled_for_category,
		];

		$collection = new shopCatalogreviewsLayoutsCollection(new shopCatalogreviewsCatalogSettingsLayout);
		$category_settings_template_layout = new shopCatalogreviewsCatalogSettingsLayout($category_settings_template_layout_assoc);
		$plugin_settings_template_layout = new shopCatalogreviewsCatalogSettingsLayout($plugin_settings_template_layout_assoc);


		$collection->push($category_settings_template_layout);
		$collection->push($plugin_settings_template_layout);

		return $collection->getResult();
	}

	private function buildPluginConfig(
		shopCatalogreviewsPluginSettings $plugin_settings,
		shopCatalogreviewsPluginStorefrontSettings $current_plugin_storefront_settings
	)
	{
		$config_params = [
			'plugin_is_enabled' => $plugin_settings->is_enabled,
			'cache_ttl_seconds' => $plugin_settings->cache_ttl_seconds,
			'reviews_per_page' => intval($plugin_settings->reviews_per_page) ?: 20,

			'url_type' => $current_plugin_storefront_settings->url_type,
			//'remove_category_keyword' => $current_plugin_storefront_settings->remove_category_keyword,
			'remove_category_keyword' => true,
			'reviews_page_link_template' => $current_plugin_storefront_settings->reviews_page_link_template,
			'reviews_grouping_mode' => $current_plugin_storefront_settings->reviews_grouping_mode,
			'reviews_page_link_display_mode' => $current_plugin_storefront_settings->reviews_page_link_display_mode,
		];

		return new shopCatalogreviewsPluginConfig($config_params);
	}

	private function buildFullConfig(
		shopCatalogreviewsPluginSettings $plugin_settings,
		shopCatalogreviewsCategorySettings $category_settings,
		shopCatalogreviewsPluginStorefrontSettings $current_plugin_storefront_settings,
		shopCatalogreviewsCategoryStorefrontSettings$current_category_storefront_settings,
		shopCatalogreviewsCatalogSettingsLayout $current_catalog_settings_layout
	)
	{
		$config_params = [
			'plugin_is_enabled' => $plugin_settings->is_enabled,
			'cache_ttl_seconds' => $plugin_settings->cache_ttl_seconds,
			'reviews_per_page' => $plugin_settings->reviews_per_page,

			'url_type' => $current_plugin_storefront_settings->url_type,
			//'remove_category_keyword' => $current_plugin_storefront_settings->remove_category_keyword,
			'remove_category_keyword' => true,
			'reviews_page_link_template' => $current_plugin_storefront_settings->reviews_page_link_template,
			'reviews_grouping_mode' => $current_plugin_storefront_settings->reviews_grouping_mode,
			'reviews_page_link_display_mode' => $current_plugin_storefront_settings->reviews_page_link_display_mode,

			'category_is_enabled' => $category_settings->is_enabled,

			'category_filters_mode' => $current_category_storefront_settings->filters_mode,
			'category_filters' => $current_category_storefront_settings->filters,

			'catalog_default_sort' => $current_catalog_settings_layout->default_sort,
			'catalog_is_client_sorting_enabled' => $current_catalog_settings_layout->is_client_sorting_enabled,
			'catalog_meta_title' => $current_catalog_settings_layout->meta_title,
			'catalog_meta_description' => $current_catalog_settings_layout->meta_description,
			'catalog_meta_keywords' => $current_catalog_settings_layout->meta_keywords,
			'catalog_h1' => $current_catalog_settings_layout->h1,
			'catalog_description' => $current_catalog_settings_layout->description,
		];

		return new shopCatalogreviewsFullConfig($config_params);
	}

	private function buildEmptyFullConfig(shopCatalogreviewsPluginConfig $plugin_config)
	{
		$config_params = [
			'plugin_is_enabled' => $plugin_config->plugin_is_enabled,
			'cache_ttl_seconds' => $plugin_config->cache_ttl_seconds,
			'reviews_per_page' => $plugin_config->reviews_per_page,

			'url_type' => $plugin_config->url_type,
			//'remove_category_keyword' => $plugin_config->remove_category_keyword,
			'remove_category_keyword' => true,
			'reviews_page_link_template' => $plugin_config->reviews_page_link_template,
			'reviews_grouping_mode' => $plugin_config->reviews_grouping_mode,
			'reviews_page_link_display_mode' => $plugin_config->reviews_page_link_display_mode,

			'category_is_enabled' => false,

			'category_filters_mode' => shopCatalogreviewsCategoryFilterMode::DISABLED,
			'category_filters' => [],

			'catalog_default_sort' => shopCatalogreviewsReviewsSort::CREATE_DATETIME_DESC,
			'catalog_is_client_sorting_enabled' => false,
			'catalog_meta_title' => '',
			'catalog_meta_description' => '',
			'catalog_meta_keywords' => '',
			'catalog_h1' => '',
			'catalog_description' => '',
		];

		return new shopCatalogreviewsFullConfig($config_params);
	}
}

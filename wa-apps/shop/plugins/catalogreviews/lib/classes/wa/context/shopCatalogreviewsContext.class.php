<?php

class shopCatalogreviewsContext
{
	private static $instance = null;
	private static $backend_facade = null;
	private static $frontend_facade = null;

	private $wa_env;
	private $settings_storage;
	private $storefront_settings_storage;
	private $category_settings_storage;
	private $category_storefront_settings_storage;
	private $settings_assoc_mapper;
	private $plugin_settings_source;
	private $plugin_storefront_settings_source;
	private $category_settings_source;
	private $category_storefront_settings_source;
	private $wa_source;
	private $product_reviews_storage;
	private $product_reviews_source;
	private $product_reviews_collection_factory;

	private $setting_controller;
	private $reviews_controller;
	private $category_settings_controller;

	private $config_storage;
	private $storefront_service;
	private $wa_routing;
	private $wa_plugin_routing;
	private $view_buffer_factory;
	private $reviews_catalog_data_renderer;
	private $reviews_catalog_view_buffer_modifier;
	private $wa_custom_view_buffer_modifiers;
	private $category_extender;
	private $seo_category_view_buffer_modifier;
	private $seo_storefront_view_buffer_modifier;
	private $pagination_view_buffer_modifier;
	private $reviews_catalog_template_variables_meta;
	private $wa_custom_variables_source;
	private $plugin_view_helper;
	private $wa_action_template_storages = [];
	private $wa_theme_factory;

	private function __construct()
	{
	}

	/**
	 * @return shopCatalogreviewsContext
	 */
	private static function getInstance()
	{
		if (self::$instance === null)
		{
			self::$instance = new shopCatalogreviewsContext();
		}

		return self::$instance;
	}

	/**
	 * @return shopCatalogreviewsBackendContextFacade
	 */
	public static function getBackendInstance()
	{
		if (self::$backend_facade === null)
		{
			self::$backend_facade = new shopCatalogreviewsBackendContextFacade(self::getInstance());
		}

		return self::$backend_facade;
	}

	/**
	 * @return shopCatalogreviewsFrontendContextFacade
	 */
	public static function getFrontendInstance()
	{
		if (self::$frontend_facade === null)
		{
			self::$frontend_facade = new shopCatalogreviewsFrontendContextFacade(self::getInstance());
		}

		return self::$frontend_facade;
	}

	/** @return shopCatalogreviewsReviewsCatalogTemplateVariablesMeta */
	public function getReviewsCatalogTemplateVariablesMeta()
	{
		if (!isset($this->reviews_catalog_template_variables_meta))
		{
			$this->reviews_catalog_template_variables_meta = new shopCatalogreviewsReviewsCatalogTemplateVariablesMeta(
				$this->getFrameworkEnv(),
				$this->getFrameworkCustomVariablesSource()
			);
		}

		return $this->reviews_catalog_template_variables_meta;
	}

	public function getFrameworkCustomVariablesSource()
	{
		if (!isset($this->wa_custom_variables_source))
		{
			$this->wa_custom_variables_source = new shopCatalogreviewsWaCustomVariablesSource();
		}

		return $this->wa_custom_variables_source;
	}

	/** @return shopCatalogreviewsFrameworkEnv */
	public function getFrameworkEnv()
	{
		if (!isset($this->wa_env))
		{
			$this->wa_env = new shopCatalogreviewsWaEnv();
		}

		return $this->wa_env;
	}

	/**
	 * @return shopCatalogreviewsStorefrontSource
	 */
	public function getStorefrontSource()
	{
		return $this->getWaRouting();
	}

	/**
	 * @return shopCatalogreviewsPluginSettingsStorage
	 */
	public function getPluginSettingsStorage()
	{
		if (!isset($this->settings_storage))
		{
			$settings_source = $this->getPluginSettingsSource();

			$this->settings_storage = new shopCatalogreviewsPluginSettingsStorage($settings_source);
		}

		return $this->settings_storage;
	}

	/**
	 * @return shopCatalogreviewsPluginStorefrontSettingsStorage
	 */
	public function getPluginStorefrontSettingsStorage()
	{
		if (!isset($this->storefront_settings_storage))
		{
			$settings_source = $this->getPluginStorefrontSettingsSource();

			$this->storefront_settings_storage = new shopCatalogreviewsPluginStorefrontSettingsStorage($settings_source);
		}

		return $this->storefront_settings_storage;
	}

	/**
	 * @return shopCatalogreviewsCategorySettingsStorage
	 */
	public function getCategorySettingsStorage()
	{
		if (!isset($this->category_settings_storage))
		{
			$settings_source = $this->getCategorySettingsSource();

			$this->category_settings_storage = new shopCatalogreviewsCategorySettingsStorage($settings_source);
		}

		return $this->category_settings_storage;
	}

	/**
	 * @return shopCatalogreviewsCategoryStorefrontSettingsStorage
	 */
	public function getCategoryStorefrontSettingsStorage()
	{
		if (!isset($this->category_storefront_settings_storage))
		{
			$settings_source = $this->getCategoryStorefrontSettingsSource();

			$this->category_storefront_settings_storage = new shopCatalogreviewsCategoryStorefrontSettingsStorage($settings_source);
		}

		return $this->category_storefront_settings_storage;
	}

	/**
	 * @return shopCatalogreviewsSettingsAssocMapper
	 */
	public function getSettingsMapper()
	{
		if (!isset($this->settings_assoc_mapper))
		{
			$this->settings_assoc_mapper = new shopCatalogreviewsSettingsAssocMapper();
		}

		return $this->settings_assoc_mapper;
	}

	/** @return shopCatalogreviewsPluginSettingsService */
	public function getPluginSettingsService()
	{
		if (!isset($this->plugin_settings_service))
		{
			$this->plugin_settings_service = new shopCatalogreviewsPluginSettingsService(
				$this->getPluginSettingsStorage()
			);
		}

		return $this->plugin_settings_service;
	}

	/** @return shopCatalogreviewsWaSource */
	public function getWaSource()
	{
		if (!isset($this->wa_source))
		{
			$this->wa_source = new shopCatalogreviewsWaSource(
				$this->getPluginSettingsService(),
				new shopCategoryModel(),
				new shopProductModel()
			);
		}

		return $this->wa_source;
	}

	/**
	 * @return shopCatalogreviewsProductReviewsStorage
	 */
	public function getProductReviewsStorage()
	{
		if (!isset($this->product_reviews_storage))
		{
			$source = $this->getProductReviewsSource();

			$this->product_reviews_storage = new shopCatalogreviewsProductReviewsStorage($source);
		}

		return $this->product_reviews_storage;
	}

	/**
	 * @return shopCatalogreviewsProductReviewsSource
	 */
	public function getProductReviewsSource()
	{
		if (!isset($this->product_reviews_source))
		{
			$this->product_reviews_source = new shopCatalogreviewsWaProductReviewsSource();
		}

		return $this->product_reviews_source;
	}

	public function getProductReviewsCollectionFactory()
	{
		if (!isset($this->product_reviews_collection_factory))
		{
			$source = $this->getProductReviewsSource();

			$this->product_reviews_collection_factory = new shopCatalogreviewsProductReviewsCollectionFactory($source);
		}

		return $this->product_reviews_collection_factory;
	}

	/**
	 * @return shopCatalogreviewsPluginSettingsSource
	 */
	private function getPluginSettingsSource()
	{
		if (!isset($this->plugin_settings_source))
		{
			$this->plugin_settings_source = new shopCatalogreviewsPluginSettingsModel();
		}

		return $this->plugin_settings_source;
	}

	/**
	 * @return shopCatalogreviewsPluginStorefrontSettingsSource
	 */
	private function getPluginStorefrontSettingsSource()
	{
		if (!isset($this->plugin_storefront_settings_source))
		{
			$this->plugin_storefront_settings_source = new shopCatalogreviewsPluginStorefrontSettingsModel();
		}

		return $this->plugin_storefront_settings_source;
	}

	/**
	 * @return shopCatalogreviewsCategorySettingsSource
	 */
	private function getCategorySettingsSource()
	{
		if (!isset($this->category_settings_source))
		{
			$this->category_settings_source = new shopCatalogreviewsCategorySettingsModel();
		}

		return $this->category_settings_source;
	}

	/**
	 * @return shopCatalogreviewsCategoryStorefrontSettingsSource
	 */
	private function getCategoryStorefrontSettingsSource()
	{
		if (!isset($this->category_storefront_settings_source))
		{
			$this->category_storefront_settings_source = new shopCatalogreviewsCategoryStorefrontSettingsModel();
		}

		return $this->category_storefront_settings_source;
	}

	/**
	 * @return shopCatalogreviewsSettingsBackendController
	 */
	public function getPluginSettingsController()
	{
		if (!isset($this->setting_controller))
		{
			$this->setting_controller = new shopCatalogreviewsSettingsBackendController(
				$this->getPluginSettingsStorage(),
				$this->getPluginStorefrontSettingsStorage(),
				$this->getReviewsCatalogTemplateVariablesMeta(),
				$this->getSettingsMapper()
			);
		}

		return $this->setting_controller;
	}

	/**
	 * @return shopCatalogreviewsReviewsBackendController
	 */
	public function getReviewsController()
	{
		if (!isset($this->reviews_controller))
		{
			$this->reviews_controller = new shopCatalogreviewsReviewsBackendController(
				$this->getCategorySettingsStorage(),
				$this->getWaSource(),
				$this->getWaSource(),
				$this->getProductReviewsStorage(),
				$this->getProductReviewsCollectionFactory(),

				$this->getSettingsMapper()
			);
		}

		return $this->reviews_controller;
	}

	/**
	 * @return shopCatalogreviewsCategorySettingsBackendController
	 */
	public function getCategorySettingsController()
	{
		if (!isset($this->category_settings_controller))
		{
			$this->category_settings_controller = new shopCatalogreviewsCategorySettingsBackendController(
				$this->getCategorySettingsStorage(),
				$this->getCategoryStorefrontSettingsStorage(),
				$this->getWaSource(),
				$this->getReviewsCatalogTemplateVariablesMeta(),
				$this->getPluginStorefrontSettingsStorage(),
				$this->getSettingsMapper()
			);
		}

		return $this->category_settings_controller;
	}

	// todo rights.config
	public function userHasRights(waContact $contact = null)
	{
		if (!$contact)
		{
			$contact = wa()->getUser();
		}

		return true;
	}

	/**
	 * @return shopCatalogreviewsConfigStorage
	 */
	public function getConfigStorage()
	{
		if (!isset($this->config_storage))
		{
			$this->config_storage = new shopCatalogreviewsConfigStorage(
				$this->getPluginSettingsStorage(),
				$this->getPluginStorefrontSettingsStorage(),
				$this->getCategorySettingsStorage(),
				$this->getCategoryStorefrontSettingsStorage(),
				$this->getSettingsMapper()
			);
		}

		return $this->config_storage;
	}

	/**
	 * @return shopCatalogreviewsStorefrontService
	 */
	public function getStorefrontService()
	{
		if (!isset($this->storefront_service))
		{
			$source = $this->getStorefrontSource();

			$this->storefront_service = new shopCatalogreviewsStorefrontService($source);
		}

		return $this->storefront_service;
	}

	/** @return shopCatalogreviewsWaRouting */
	public function getWaRouting()
	{
		if (!isset($this->wa_routing))
		{
			$this->wa_routing = new shopCatalogreviewsWaRouting();
		}

		return $this->wa_routing;
	}

	/** @return shopCatalogreviewsWaPluginRoutingDispatcher */
	public function getWaPluginRoutingDispatcher()
	{
		if (!isset($this->wa_plugin_routing))
		{
			$this->wa_plugin_routing = new shopCatalogreviewsWaPluginRoutingDispatcher(
				$this->getWaSource(),
				$this->getWaSource()
			);
		}

		return $this->wa_plugin_routing;
	}

	/** @return shopCatalogreviewsViewBufferFactory */
	public function getViewBufferFactory()
	{
		if (!isset($this->view_buffer_factory))
		{
			$this->view_buffer_factory = new shopCatalogreviewsWaViewBufferFactory();
		}

		return $this->view_buffer_factory;
	}

	/** @return shopCatalogreviewsCategoryExtender */
	public function getCategoryExtender()
	{
		if (!isset($this->category_extender))
		{
			$this->category_extender = new shopCatalogreviewsCategoryExtender($this->getWaSource());
		}

		return $this->category_extender;
	}

	/** @return shopCatalogreviewsReviewsCatalogViewBufferModifier */
	public function getReviewsCatalogViewBufferModifier()
	{
		if (!isset($this->reviews_catalog_view_buffer_modifier))
		{
			$this->reviews_catalog_view_buffer_modifier = new shopCatalogreviewsReviewsCatalogViewBufferModifier(
				$this->getWaSource(),
				$this->getCategoryExtender()
			);
		}

		return $this->reviews_catalog_view_buffer_modifier;
	}

	/** @return shopCatalogreviewsViewBufferModifiers */
	public function getWaCustomViewBufferModifiers()
	{
		if (!isset($this->wa_custom_view_buffer_modifiers))
		{
			$this->wa_custom_view_buffer_modifiers = new shopCatalogreviewsViewBufferModifiers();
			$this->wa_custom_view_buffer_modifiers->addModifier(new shopCatalogreviewsWaPluginsViewBufferModifier());
		}

		return $this->wa_custom_view_buffer_modifiers;
	}

	/** @return shopCatalogreviewsPaginationViewBufferModifier */
	public function getPaginationViewBufferModifier()
	{
		if (!isset($this->pagination_view_buffer_modifier))
		{
			$this->pagination_view_buffer_modifier = new shopCatalogreviewsPaginationViewBufferModifier();
		}

		return $this->pagination_view_buffer_modifier;
	}

	/** @return shopCatalogreviewsCategoryViewBufferModifier */
	public function getSeoCategoryViewBufferModifier()
	{
		if (!isset($this->seo_category_view_buffer_modifier))
		{
			$this->seo_category_view_buffer_modifier = $this->getFrameworkEnv()->isSeoPluginEnabled()
				? new shopCatalogreviewsSeoPluginCategoryViewBufferModifier()
				: new shopCatalogreviewsCategoryEmptyViewBufferModifier();
		}

		return $this->seo_category_view_buffer_modifier;
	}

	/** @return shopCatalogreviewsStorefrontViewBufferModifier */
	public function getSeoStorefrontViewBufferModifier()
	{
		if (!isset($this->seo_storefront_view_buffer_modifier))
		{
			$this->seo_storefront_view_buffer_modifier = $this->getFrameworkEnv()->isSeoPluginEnabled()
				? new shopCatalogreviewsSeoPluginStorefrontViewBufferModifier(shopSeoContext::getInstance()->getStorefrontDataCollector()) // todo заменить тернарник на получение своей обертки над shopSeoContext
				: new shopCatalogreviewsStorefrontEmptyViewBufferModifier();
		}

		return $this->seo_storefront_view_buffer_modifier;
	}

	/** @return shopCatalogreviewsReviewsCatalogDataRenderer */
	public function getReviewsCatalogDataRenderer()
	{
		if (!isset($this->reviews_catalog_data_renderer))
		{
			$this->reviews_catalog_data_renderer = new shopCatalogreviewsReviewsCatalogDataRenderer(
				$this->getViewBufferFactory(),
				$this->getReviewsCatalogViewBufferModifier(),
				$this->getSeoCategoryViewBufferModifier(),
				$this->getSeoStorefrontViewBufferModifier(),
				$this->getPaginationViewBufferModifier(),
				$this->getWaCustomViewBufferModifiers()
			);
		}

		return $this->reviews_catalog_data_renderer;
	}

	/** @return shopCatalogreviewsWaViewHelper */
	public function getViewHelper()
	{
		if (!isset($this->plugin_view_helper))
		{
			$this->plugin_view_helper = new shopCatalogreviewsWaViewHelper();
		}

		return $this->plugin_view_helper;
	}

	public function getWaThemeFactory()
	{
		if (!isset($this->wa_theme_factory))
		{
			$this->wa_theme_factory = new shopCatalogreviewsWaThemeFactory();
		}

		return $this->wa_theme_factory;
	}

	/**
	 * @param shopCatalogreviewsWaTheme $theme
	 * @return shopCatalogreviewsWaActionTemplateStorage
	 */
	public function getWaActionTemplateStorage(shopCatalogreviewsWaTheme $theme)
	{
		$key = $theme->getFullId();

		if (!array_key_exists($key, $this->wa_action_template_storages))
		{
			$this->wa_action_template_storages[$key] = new shopCatalogreviewsWaActionTemplateStorage($theme);
		}

		return $this->wa_action_template_storages[$key];
	}
}

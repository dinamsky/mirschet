<?php

class shopCatalogreviewsWaSource implements shopCatalogreviewsCategorySource, shopCatalogreviewsProductSource
{
	private $plugin_settings_service;
	private $category_model;
	private $product_model;

	public function __construct(
		shopCatalogreviewsPluginSettingsService $plugin_settings_service,
		shopCategoryModel $category_model,
		shopProductModel $product_model
	)
	{
		$this->plugin_settings_service = $plugin_settings_service;
		$this->category_model = $category_model;
		$this->product_model = $product_model;
	}

	public function getCategory($category_id)
	{
		return $this->category_model->getById($category_id);
	}

	public function getRootCategories()
	{
		return $this->category_model
			->select('*')
			->where('parent_id = 0')
			->where('type = 0')
			->order('left_key ASC')
			->fetchAll();
	}

	public function getCategoriesByLeftKey()
	{
		return $this->category_model
			->select('*')
			->where('type = 0')
			->order('left_key ASC')
			->fetchAll();
	}

	public function getCategoryByFullUrl($full_url)
	{
		return $this->category_model
			->select('*')
			->where('full_url = :full_url', ['full_url' => $full_url])
			->fetchAssoc();
	}

	public function getCategoryByUrl($url)
	{
		return $this->category_model
			->select('*')
			->where('url = :url', ['url' => $url])
			->fetchAssoc();
	}

	public function getShortProductsByIds(array $product_ids)
	{
		if (count($product_ids) === 0)
		{
			return [];
		}

		$products = $this->product_model
			->select('id,name,url,image_id,ext,image_filename')
			->where('id IN (:ids)', ['ids' => $product_ids])
			->fetchAll();

		return $products;
	}

	public function getCategoryPath($category_id)
	{
		return $this->category_model->getPath($category_id);
	}

	public function getCategoryProductsData($storefront, $category_id)
	{
		$settings = $this->plugin_settings_service->getSettings();

		if ($settings->cache_ttl_seconds > 0)
		{
			/** @var shopConfig $config */
			$config = wa('shop')->getConfig();
			$default_currency = $config->getCurrency(true);
			$frontend_currency = $config->getCurrency(false);

			$key = json_encode([
				'storefront' => $storefront,
				'default_currency' => $default_currency,
				'frontend_currency' => $frontend_currency,
			]);

			$cache = $this->getCacheCategoryProductsData($category_id);

			if ($cache->isCached())
			{
				$data = $cache->get();
			}
			else
			{
				$data = [];
			}

			if (!array_key_exists($key, $data))
			{
				$data[$key] = $this->_getCategoryProductsData($category_id);
				$cache->set($data);
			}

			return $data[$key];
		}

		return $this->_getCategoryProductsData($category_id);
	}

	private function getCacheCategoryProductsData($category_id)
	{
		$settings = $this->plugin_settings_service->getSettings();
		$ttl = $settings->cache_ttl_seconds * 60;
		$ttl *= 1 + rand(-25, 25) * 0.01;

		return new waSerializeCache(
			'catalogreviews/category_products_data_' . $category_id,
			$ttl,
			'shop'
		);
	}

	private function _getCategoryProductsData($category_id)
	{
		$products = new shopCatalogreviewsWaProductsCollection('category/' . $category_id);

		$result = array();
		$result['products_count'] = $products->count();

		$range = $products->getPriceRange();

		$range['min'] = $this->roundPrice($range['min']);
		$result['min_price'] = shop_currency($range['min']);
		$result['min_price_without_currency'] = $range['min'];

		$range['max'] = $this->roundPrice($range['max']);
		$result['max_price'] = shop_currency($range['max']);
		$result['max_price_without_currency'] = $range['max'];

		return $result;
	}

	private function roundPrice($price)
	{
		/** @var shopConfig $config */
		$config = wa('shop')->getConfig();
		$curs = $config->getCurrencies();
		$default_currency = $config->getCurrency(true);
		$frontend_currency = $config->getCurrency(false);

		if ($price > 0)
		{
			$frontend_price = shop_currency($price, $default_currency, $frontend_currency, false);

			if (!empty($curs[$frontend_currency]['rounding']) && $default_currency != $frontend_currency)
			{
				$frontend_price = shopRounding::roundCurrency($frontend_price, $frontend_currency);
				$price = shop_currency($frontend_price, $frontend_currency, $default_currency, false);
			}
		}

		return $price;
	}
}

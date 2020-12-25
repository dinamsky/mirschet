<?php

class shopSeoredirectShopUrl extends shopSeoredirectUrl
{
	/**
	 * Часть url без учета витрины
	 *
	 * @var string|null
	 */
	protected $current_url;

	/**
	 * @var shopSeoredirectShopRouting
	 */
	protected $shop_routing;

	public function __construct($url = null)
	{
		parent::__construct();
		$url = isset($url) ? $url : wa()->getRouting()->getCurrentUrl();
		$url = preg_replace('/\/$/', '', $url);
		$this->current_url = $url;
		$this->shop_routing = new shopSeoredirectShopRouting();
	}

	public function __toString()
	{
		return $this->current_url ? $this->current_url : '';
	}

	public function isCategory()
	{
		if (waRequest::param('category_url') != null)
		{
			return true;
		}
		$path_array = $this->getExplodeCurrentUrl();
		$category_route = $this->shop_routing->getFrontendCategory(shopSeoredirectUrlType::FLAT);
		if (array_shift($path_array) == $category_route)
		{
			return true;
		}

		return false;
	}

	public function isProduct()
	{
		if (waRequest::param('url_type') == shopSeoredirectUrlType::FLAT
			&& waRequest::param('product_url') != null
		)
		{
			return true;
		}
		$path_array = $this->getExplodeCurrentUrl();
		$product_route = $this->shop_routing->getFrontendProduct(shopSeoredirectUrlType::FLAT);
		if (array_shift($path_array) == $product_route)
		{
			return true;
		}

		// TODO: сделать для других типов

		return false;
	}

	public function isProductPage()
	{
		if (waRequest::param('url_type') == shopSeoredirectUrlType::FLAT
			&& waRequest::param('page_url') != null
		)
		{
			return true;
		}
		$path_array = $this->getExplodeCurrentUrl();
		if (count($path_array) == 3)
		{
			$product_route = $this->shop_routing->getFrontendProductPage(shopSeoredirectUrlType::FLAT);
			if (array_shift($path_array) == $product_route)
			{
				return true;
			}
		}

		// TODO: сделать для других типов

		return false;
	}

	public function isProductReviews()
	{
		if (waRequest::param('action') == 'productReviews')
		{
			return true;
		}
		$path_array = $this->getExplodeCurrentUrl();
		$reviews_route = $this->shop_routing->getFrontendProductReviews(shopSeoredirectUrlType::FLAT);
		if (array_pop($path_array) == $reviews_route)
		{
			return true;
		}

		return false;
	}

	/**
	 * Возвращает массив url-ов
	 *
	 * @return array ['category', 'product_url', 'reviews']
	 */
	public function getExplodeCurrentUrl()
	{
		if (empty($this->current_url))
		{
			return array();
		}
		$path = substr($this->current_url, -1) == '/'
			? substr($this->current_url, 0, -1)
			: $this->current_url;
		$path_array = explode('/', $path);

		return $path_array;
	}

	/**
	 * Вернет часть url без учета витрины
	 *
	 * @return string
	 */
	public function getCurrentUrl()
	{
		return $this->current_url;
	}
}
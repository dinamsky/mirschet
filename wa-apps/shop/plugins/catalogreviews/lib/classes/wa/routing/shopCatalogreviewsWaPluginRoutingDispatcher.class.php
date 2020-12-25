<?php

class shopCatalogreviewsWaPluginRoutingDispatcher
{
	private $category_source;
	private $product_source;

	public function __construct(
		shopCatalogreviewsCategorySource $category_source,
		shopCatalogreviewsProductSource $product_source
	)
	{
		$this->category_source = $category_source;
		$this->product_source = $product_source;
	}

	/**
	 * @param shopCatalogreviewsPluginConfig $plugin_config
	 * @param $domain
	 * @param $wa_route
	 * @param $url
	 * @return shopCatalogreviewsWaPluginRoutingDispatchResult
	 */
	public function dispatch(
		shopCatalogreviewsPluginConfig $plugin_config,
		$domain,
		$wa_route,
		$url
	)
	{
		$plugin_routing_rules = $this->getPluginRoutingRules($plugin_config, $wa_route);

		list($category, $category_reviews_route) = $this->tryParseReviewsCatalogUrl($plugin_config, $wa_route, $url);

		if (!$category)
		{
			$category = $this->tryParseCategoryUrl($wa_route, $url);

			return new shopCatalogreviewsWaPluginRoutingDispatchResult($domain, $wa_route, $url, $plugin_routing_rules, [], $category);
		}

		$plugin_catalog_route = [
			'plugin' => 'catalogreviews',
			'module' => 'frontend',
			'action' => 'categoryReviews',
		];

		$active_routing = [
			$category_reviews_route => $plugin_catalog_route,
		];

		return new shopCatalogreviewsWaPluginRoutingDispatchResult($domain, $wa_route, $url, $plugin_routing_rules, $active_routing, $category);
	}

	private function tryParseReviewsCatalogUrl(shopCatalogreviewsPluginConfig $plugin_config, $route, $url)
	{
		$empty_result = [null, null];


		$url = trim($url, '/');
		if ($url === '')
		{
			return $empty_result;
		}

		$url_parts = explode('/', $url);
		$last_part = $url_parts[count($url_parts) - 1];

		$shop_url_type = ifset($route, 'url_type', '0');

		$url_is_mixed = $shop_url_type == shopCatalogreviewsWaShopUrlType::MIXED;
		$url_is_plain = $shop_url_type == shopCatalogreviewsWaShopUrlType::PLAIN;
		$url_is_natural = $shop_url_type == shopCatalogreviewsWaShopUrlType::NATURAL;


		$is_prefix = $plugin_config->url_type === shopCatalogreviewsPluginUrlType::KEYWORD_PREFIX;
		$is_postfix = $plugin_config->url_type === shopCatalogreviewsPluginUrlType::KEYWORD_POSTFIX;


		$category = null;
		$category_reviews_route = null;

		$category_url = null;


		$category_root = shopCatalogreviewsWaPluginRouting::CATEGORY_ROOT;

		// mixed - /category/cat-1/subcat-1/
		if ($url_is_mixed)
		{
			if ($is_prefix)
			{
				if ($plugin_config->remove_category_keyword)
				{
					if (count($url_parts) < 2 || $url_parts[0] !== $plugin_config->root_url_keyword)
					{
						return $empty_result;
					}

					$category_url = implode('/', array_slice($url_parts, 1));
					$category_reviews_route = "{$plugin_config->root_url_keyword}/{$category_url}/";
				}
				else
				{
					if (count($url_parts) < 3 || $url_parts[0] !== $category_root || $url_parts[1] !== $plugin_config->root_url_keyword)
					{
						return $empty_result;
					}

					$category_url = implode('/', array_slice($url_parts, 2));
					$category_reviews_route = "{$category_root}/{$plugin_config->root_url_keyword}/{$category_url}/";
				}
			}
			elseif ($is_postfix)
			{
				if (count($url_parts) < 3 || $url_parts[0] !== $category_root || $last_part !== $plugin_config->root_url_keyword)
				{
					return $empty_result;
				}

				$category_url = implode('/', array_slice($url_parts, 1, -1));
				$category_reviews_route = "{$category_root}/{$category_url}/{$plugin_config->root_url_keyword}/";
			}
		}
		// plain - /category/subcat-1/
		elseif ($url_is_plain)
		{
			if ($is_prefix)
			{
				if ($plugin_config->remove_category_keyword)
				{
					if (count($url_parts) !== 2 || $url_parts[0] !== $plugin_config->root_url_keyword)
					{
						return $empty_result;
					}

					$category_url = $url_parts[1];
					$category_reviews_route = "{$plugin_config->root_url_keyword}/{$category_url}/";
				}
				else
				{
					if (count($url_parts) !== 3 || $url_parts[0] !== $category_root || $url_parts[1] !== $plugin_config->root_url_keyword)
					{
						return $empty_result;
					}

					$category_url = $url_parts[2];
					$category_reviews_route = "{$category_root}/{$plugin_config->root_url_keyword}/{$category_url}/";
				}
			}
			elseif ($is_postfix)
			{
				if (count($url_parts) !== 3 || $url_parts[0] !== $category_root || $last_part !== $plugin_config->root_url_keyword)
				{
					return $empty_result;
				}

				$category_url = $url_parts[1];
				$category_reviews_route = "{$category_root}/{$category_url}/{$plugin_config->root_url_keyword}/";
			}
		}
		// natural - /cat-1/subcat-2/
		elseif ($url_is_natural)
		{
			if ($is_prefix)
			{
				if (count($url_parts) < 2 || $url_parts[0] !== $plugin_config->root_url_keyword)
				{
					return $empty_result;
				}

				$category_url = implode('/', array_slice($url_parts, 1));
				$category_reviews_route = "{$plugin_config->root_url_keyword}/{$category_url}/";
			}
			elseif ($is_postfix)
			{
				if (count($url_parts) < 2 || $last_part !== $plugin_config->root_url_keyword)
				{
					return $empty_result;
				}

				$category_url = implode('/', array_slice($url_parts, 0, -1));
				$category_reviews_route = "{$category_url}/{$plugin_config->root_url_keyword}/";
			}
		}

		$category = $url_is_plain
			? $this->category_source->getCategoryByUrl($category_url)
			: $this->category_source->getCategoryByFullUrl($category_url);

		return [$category, $category_reviews_route];
	}

	private function tryParseCategoryUrl($route, $current_url)
	{
		$current_url = trim($current_url, '/');
		if ($current_url === '')
		{
			return null;
		}

		$url_parts = explode('/', $current_url);


		$shop_url_type = ifset($route, 'url_type', '0');
		$is_mixed_url_type = $shop_url_type == shopCatalogreviewsWaShopUrlType::MIXED;
		$is_plain_url_type = $shop_url_type == shopCatalogreviewsWaShopUrlType::PLAIN;
		$is_natural_url_type = $shop_url_type == shopCatalogreviewsWaShopUrlType::NATURAL;

		$category = null;
		$category_reviews_route = null;

		$category_url = null;

		$category_root = shopCatalogreviewsWaPluginRouting::CATEGORY_ROOT;

		// mixed - /category/cat-1/subcat-1/
		if ($is_mixed_url_type && $url_parts[0] === $category_root)
		{
			$category_url = implode('/', array_slice($url_parts, 1));

			$category = $this->category_source->getCategoryByFullUrl($category_url);
		}
		// plain - /category/subcat-1/
		elseif ($is_plain_url_type && $url_parts[0] === $category_root)
		{
			$category_url = $url_parts[1];

			$category = $this->category_source->getCategoryByUrl($category_url);
		}
		// natural - /cat-1/subcat-2/
		elseif ($is_natural_url_type)
		{
			$category_url = implode('/', $url_parts);

			$category = $this->category_source->getCategoryByFullUrl($category_url);
		}

		return $category;
	}

	private function getPluginRoutingRules(shopCatalogreviewsPluginConfig $plugin_config, $route)
	{
		if (ifset($route, 'app', null) !== 'shop')
		{
			return [];
		}

		return [
			shopCatalogreviewsRouteName::CATEGORY_REVIEWS => $this->getCategoryReviewsPageCommonRule($plugin_config, $route),
		];
	}

	private function getCategoryReviewsPageCommonRule(shopCatalogreviewsPluginConfig $plugin_config, $route)
	{
		$common_route_template = '';

		$shop_url_type = ifset($route, 'url_type', shopCatalogreviewsWaShopUrlType::MIXED);

		$is_plugin_url_prefix = $plugin_config->url_type === shopCatalogreviewsPluginUrlType::KEYWORD_PREFIX;
		$is_plugin_url_postfix = $plugin_config->url_type === shopCatalogreviewsPluginUrlType::KEYWORD_POSTFIX;

		/*
		 * mixed - /category/cat-1/subcat-1/
		 * or plain - /category/subcat-1/
		 */
		if (
			$shop_url_type == shopCatalogreviewsWaShopUrlType::MIXED
			|| $shop_url_type == shopCatalogreviewsWaShopUrlType::PLAIN
		)
		{
			$category_root = shopCatalogreviewsWaPluginRouting::CATEGORY_ROOT;

			if ($is_plugin_url_postfix)
			{
				$common_route_template = "{$category_root}/<category_url>/{$plugin_config->root_url_keyword}/";
			}
			elseif ($is_plugin_url_prefix)
			{
				$common_route_template = $plugin_config->remove_category_keyword
					? "{$plugin_config->root_url_keyword}/<category_url>/"
					: "{$category_root}/{$plugin_config->root_url_keyword}/<category_url>/";
			}
		}
		/*
		 * natural - /cat-1/subcat-2/
		 */
		elseif ($shop_url_type == '2')
		{
			if ($is_plugin_url_postfix)
			{
				$common_route_template = "<category_url>/{$plugin_config->root_url_keyword}/";
			}
			elseif ($is_plugin_url_prefix)
			{
				$common_route_template = "{$plugin_config->root_url_keyword}/<category_url>/";
			}
		}

		return $common_route_template;
	}
}

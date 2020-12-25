<?php

class shopCatalogreviewsWaPluginRouting
{
	const CATEGORY_ROOT = 'category';

	private $plugin_config;
	private $dispatch_result;
	private $wa_routing;

	public function __construct(
		shopCatalogreviewsPluginConfig $plugin_config,
		shopCatalogreviewsWaPluginRoutingDispatchResult $dispatch_result,
		shopCatalogreviewsWaRouting $wa_routing
	)
	{
		$this->plugin_config = $plugin_config;
		$this->dispatch_result = $dispatch_result;
		$this->wa_routing = $wa_routing;
	}

	public function getReviewsPageUrl($category, $absolute = false)
	{
		$domain = $this->dispatch_result->getDomain();
		$shop_route = $this->dispatch_result->getRoute();

		$route_rule = $this->dispatch_result->getPluginRoutingRule(shopCatalogreviewsRouteName::CATEGORY_REVIEWS);
		if (!$route_rule)
		{
			return '';
		}

		$shop_url_type = ifset($shop_route, 'url_type', '0');
		$root_url = rtrim($shop_route['url'], '*');

		$category_url = $shop_url_type == shopCatalogreviewsWaShopUrlType::PLAIN
			? $category['url']
			: $category['full_url'];
		$reviews_page_url = str_replace('<category_url>', $category_url, $route_rule);


		if ($absolute)
		{
			return 'http' . (waRequest::isHttps() ? 's' : '') . '://' . $domain
				. '/' . $root_url
				. $reviews_page_url;
		}
		else
		{
			$domain_sub_route = '/';
			if ($domain && strpos($domain, '/') !== false)
			{
				if (preg_match('/\/(.+)/', $domain, $matches))
				{
					$domain_sub_route = '/' . trim($matches[1], '/') . '/';
				}
			}

			return  $domain_sub_route . $root_url . $reviews_page_url;
		}
	}

	public function getActiveRouting()
	{
		return $this->dispatch_result->getActiveRouting();
	}
}

<?php

class shopCatalogreviewsWaPluginRoutingDispatchResult
{
	private $domain;
	private $route;
	private $dispatched_url;
	private $plugin_routing_rules;
	private $active_routing = [];
	private $category = null;

	public function __construct(
		$domain,
		array $route,
		$dispatched_url,
		array $plugin_routing_rules,
		$active_routing,
		$category
		//$page_type
	)
	{
		$this->domain = $domain;
		$this->route = $route;
		$this->dispatched_url = $dispatched_url;
		$this->plugin_routing_rules = $plugin_routing_rules;
		$this->active_routing = $active_routing;
		$this->category = $category;
	}

	public function getDomain()
	{
		return $this->domain;
	}

	public function getRoute()
	{
		return $this->route;
	}

	public function getDispatchedUrl()
	{
		return $this->dispatched_url;
	}

	public function getPluginRoutingRules()
	{
		return $this->plugin_routing_rules;
	}

	public function getPluginRoutingRule($route_name)
	{
		return isset($this->plugin_routing_rules[$route_name])
			? $this->plugin_routing_rules[$route_name]
			: null;
	}

	public function getActiveRouting()
	{
		return $this->active_routing;
	}

	public function getCategory()
	{
		return $this->category;
	}

	public function isReviewsCatalogPage()
	{
		return false;
	}

	public function isCategoryPage()
	{
		return false;
	}
}

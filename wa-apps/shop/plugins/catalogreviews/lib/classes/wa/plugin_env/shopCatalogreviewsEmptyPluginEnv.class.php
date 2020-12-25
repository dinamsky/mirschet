<?php

class shopCatalogreviewsEmptyPluginEnv implements shopCatalogreviewsPluginEnv
{
	private $empty_full_config;
	private $plugin_routing;

	public function __construct(
		shopCatalogreviewsFullConfig $empty_full_config,
		shopCatalogreviewsWaPluginRouting $plugin_routing
	)
	{
		$this->empty_full_config = $empty_full_config;
		$this->plugin_routing = $plugin_routing;
	}

	/** @return shopCatalogreviewsFullConfig */
	public function getConfig()
	{
		return $this->empty_full_config;
	}

	/** @return array|null */
	public function getCategory()
	{
		return null;
	}

	/** @return shopCatalogreviewsWaPluginRouting */
	public function getPluginRouting()
	{
		return $this->plugin_routing;
	}

	public function getStorefront()
	{
		return null;
	}

	public function getCurrentReviewsSortMode()
	{
		return null;
	}

	/** @return array */
	public function getActiveRouting()
	{
		return [];
	}
}
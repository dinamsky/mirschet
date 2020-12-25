<?php

class shopCatalogreviewsWaPluginEnv implements shopCatalogreviewsPluginEnv
{
	/** @var shopCatalogreviewsFullConfig */
	private $full_config;
	/** @var shopCatalogreviewsWaPluginRouting */
	private $plugin_routing;
	private $category;
	private $storefront;
	private $sort;
	private $order;
	private $current_sort_mode;

	public function __construct(
		shopCatalogreviewsFullConfig $full_config,
		shopCatalogreviewsWaPluginRouting $plugin_routing,
		$category,
		$storefront,
		$sort,
		$order
	)
	{
		$this->category = $category;
		$this->storefront = $storefront;
		$this->full_config = $full_config;
		$this->plugin_routing = $plugin_routing;
		$this->sort = $sort;
		$this->order = $order;

		$this->current_sort_mode = $this->__getCurrentReviewsSortMode();
	}

	public function getConfig()
	{
		return $this->full_config;
	}

	public function getCategory()
	{
		return $this->category;
	}

	public function getPluginRouting()
	{
		return $this->plugin_routing;
	}

	public function getStorefront()
	{
		return $this->storefront;
	}

	public function getCurrentReviewsSortMode()
	{
		return $this->current_sort_mode;
	}

	public function getActiveRouting()
	{
		return $this->plugin_routing->getActiveRouting();
	}

	private function __getCurrentReviewsSortMode()
	{
		$sort = $this->sort;
		$order = $this->order;

		$is_asc = strtolower($order) === 'asc';

		if ($sort === 'rating')
		{
			$sort_mode = $is_asc
				? shopCatalogreviewsReviewsSort::RATING_ASC
				: shopCatalogreviewsReviewsSort::RATING_DESC;
		}
		elseif ($sort === 'create_datetime')
		{
			$sort_mode = $is_asc
				? shopCatalogreviewsReviewsSort::CREATE_DATETIME_ASC
				: shopCatalogreviewsReviewsSort::CREATE_DATETIME_DESC;
		}
		else
		{
			$sort_mode = $this->full_config->catalog_default_sort;
		}

		return $sort_mode;
	}
}

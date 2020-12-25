<?php

/**
 * Class shopCatalogreviewsProductReviewsCollectionCachedProxy
 *
 * todo: поменять на другой интерфейс, чтобы иметь возможность инвалидировать кеш при изменениях
 */
class shopCatalogreviewsProductReviewsCollectionCachedProxy implements shopCatalogreviewsIProductReviewsCollection
{
	const TWO_HOURS = 2 * 3600;

	private $storefront;
	private $collection;
	private $ttl_in_seconds;

	private $items_cache;
	private $count_cache;

	public function __construct(
		shopCatalogreviewsIProductReviewsCollection $collection,
		$storefront,
		$ttl_in_seconds = self::TWO_HOURS
	)
	{
		$this->storefront = $storefront;
		$this->collection = $collection;
		$this->ttl_in_seconds = $ttl_in_seconds;
	}

	public function setWithReplies($with_replies)
	{
		$this->collection->setWithReplies($with_replies);
		$this->removeCacheInstance();
	}

	public function setIsPlainRepliesStructure($is_plain_replies_structure)
	{
		$this->collection->setIsPlainRepliesStructure($is_plain_replies_structure);
		$this->removeCacheInstance();
	}

	public function setPublishedOnly($published_only)
	{
		$this->collection->setPublishedOnly($published_only);
		$this->removeCacheInstance();
	}

	public function setSort($sort, $order)
	{
		$this->collection->setSort($sort, $order);
		$this->removeCacheInstance();
	}

	public function addReviewsplusPluginFields($add_reviewsplus_plugin)
	{
		$this->collection->addReviewsplusPluginFields($add_reviewsplus_plugin);
		$this->removeCacheInstance();
	}

	public function getReviews($offset, $limit)
	{
		$offset = intval($offset);
		$limit = intval($limit);

		$cache = $this->getItemsCache($offset, $limit);

		if ($cache->isCached())
		{
			$var = $cache->get();

			return $var;
		}

		$reviews = $this->collection->getReviews($offset, $limit);
		$cache->set($reviews);

		return $reviews;
	}

	public function count()
	{
		$cache = $this->getCountCache();

		if ($cache->isCached())
		{
			return $cache->get();
		}

		$count = $this->collection->count();

		$cache->set($count);

		return $count;
	}

	public function getOptions()
	{
		return $this->collection->getOptions();
	}

	private function getCacheHash()
	{
		$hash = "storefront/{$this->storefront}";
		$options = $this->collection->getOptions();
		foreach ($options as $key => $value)
		{
			$hash .= "/{$key}/{$value}";
		}

		return md5($hash);
	}

	private function removeCacheInstance()
	{
		$this->items_cache = null;
		$this->count_cache = null;
	}

	/**
	 * @param $offset
	 * @param $limit
	 * @return shopCatalogreviewsProductReviewsCache
	 */
	private function getItemsCache($offset, $limit)
	{
		if (!isset($this->items_cache))
		{
			$hash = $this->getCacheHash();
			$cache_key = "plugins/catalogreviews/product_reviews_collection/items/{$hash}_{$offset}_{$limit}";

			$this->items_cache = new shopCatalogreviewsProductReviewsSimpleFileCache($cache_key, $this->ttl_in_seconds * (1 + (rand(-10, 10) / 100)));
		}

		return $this->items_cache;
	}

	/**
	 * @return shopCatalogreviewsProductReviewsCache
	 */
	private function getCountCache()
	{
		if (!isset($this->count_cache))
		{
			$hash = $this->getCacheHash();
			$cache_key = "plugins/catalogreviews/product_reviews_collection/count/{$hash}";

			$this->count_cache = new shopCatalogreviewsProductReviewsSimpleFileCache($cache_key, $this->ttl_in_seconds * (1 + (rand(-10, 10) / 100)));
		}

		return $this->count_cache;
	}
}
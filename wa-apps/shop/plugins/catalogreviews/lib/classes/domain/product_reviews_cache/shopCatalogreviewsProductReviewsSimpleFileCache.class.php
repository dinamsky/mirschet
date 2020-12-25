<?php

class shopCatalogreviewsProductReviewsSimpleFileCache implements shopCatalogreviewsProductReviewsCache
{
	private $hash;
	private $ttl_in_seconds;

	private $file_cache;

	public function __construct($key, $ttl_in_seconds)
	{
		$this->hash = $key;
		$this->ttl_in_seconds = $ttl_in_seconds;

		$this->file_cache = new waSerializeCache($key, $ttl_in_seconds, 'shop');
	}

	public function isCached()
	{
		return $this->file_cache->isCached();
	}

	public function get()
	{
		return $this->file_cache->get();
	}

	public function set($value)
	{
		$this->file_cache->set($value);
	}

	public function delete()
	{
		return $this->file_cache->delete();
	}
}
<?php

class shopLinkcanonicalCategoryCanonical implements shopLinkcanonicalIItemCanonical
{
	protected $hash;

	protected $category_id = 0;

	protected $canonical = '';

	protected $storefront = '';

	public function __construct($category_id, $storefront, $canonical)
	{
		$this->category_id = $category_id;
		$this->canonical = $canonical;
		$this->storefront = $storefront;
	}

	public function getHash()
	{
		if ($this->hash === null)
		{
			$this->hash = md5($this->category_id . $this->storefront . $this->canonical);
		}

		return $this->hash;
	}

	public function getId()
	{
		return $this->category_id;
	}

	public function getCanonical()
	{
		return $this->canonical;
	}

	/**
	 * @return bool
	 */
	public function hasCanonical()
	{
		return !empty($this->canonical);
	}

	public function getStorefront()
	{
		return $this->storefront;
	}

	public function getUrl()
	{
		if ($this->hasCanonical() && shopLinkcanonicalViewHelper::isURL($this->getCanonical()))
		{
			return $this->getCanonical();
		}
		$protocol = waRequest::isHttps() ? 'https://' : 'http://';
		if ($this->storefront == '*')
		{
			$routing = new shopLinkcanonicalWaRouting();
			$this->storefront = $routing->getCurrentStorefront();
		}

		$store = str_replace('/*', '', $this->storefront);
		return $protocol . $store . $this->getCanonical();
	}

	public function hasUrl()
	{
		$url = $this->getUrl();
		return !empty($url);
	}
}
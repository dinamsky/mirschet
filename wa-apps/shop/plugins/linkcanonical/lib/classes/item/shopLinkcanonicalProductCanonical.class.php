<?php

class shopLinkcanonicalProductCanonical implements shopLinkcanonicalIItemCanonical
{
	protected $hash;

	protected $product_id = 0;

	protected $canonical = '';

	protected $storefront = '';

	public function __construct($product_id, $storefront, $canonical)
	{
		$this->product_id = $product_id;
		$this->canonical = $canonical;
		$this->storefront = $storefront;
	}

	public function getHash()
	{
		if ($this->hash === null)
		{
			$this->hash = md5($this->product_id . $this->storefront . $this->canonical);
		}

		return $this->hash;
	}

	public function getId()
	{
		return $this->product_id;
	}

	/**
	 * @return string
	 */
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

	/**
	 * @return string
	 */
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
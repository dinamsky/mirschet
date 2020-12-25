<?php

class shopLinkcanonicalCanonicalProduct implements shopLinkcanonicalILinkCanonical
{
	/**
	 * @var shopLinkcanonicalProductCanonicalModel
	 */
	protected $product_canonical_model;

	/**
	 * @var shopProductModel
	 */
	protected $product_model;

	/**
	 * @var shopLinkcanonicalProductCanonical
	 */
	protected $canonical;

	/**
	 * @var bool
	 */
	private $is_tag;

	public function __construct()
	{
		$this->product_canonical_model = new shopLinkcanonicalProductCanonicalModel();
		$this->product_model = new shopProductModel();
	}

	public static function isProduct()
	{
		if (waRequest::param('action') == 'product')
		{
			return true;
		}

		return false;
	}

	protected function setProductCanonical()
	{
		if (!self::isProduct())
		{
			$this->is_tag = false;
			return $this;
		}
		if($this->is_tag === true)
		{
			return $this;
		}

		$routing = new shopLinkcanonicalWaRouting();
		$storefront = $routing->getCurrentStorefront();
		$storefront = ifempty($storefront, '*');

		$product_table_name = $this->product_model->getTableName();
		$canonical_table_name = $this->product_canonical_model->getTableName();
		$escape_product_url = $this->product_model->escape(waRequest::param('product_url'));
		$escape_storefront = $this->product_model->escape($storefront);

		$sql = "SELECT p.id, pc.canonical, pc.storefront FROM `{$product_table_name}` p 
INNER JOIN `{$canonical_table_name}` pc ON pc.product_id=p.id
WHERE p.url='{$escape_product_url}' AND pc.storefront IN ('*', '{$escape_storefront}')";

		$products = $this->product_canonical_model->query($sql)->fetchAll('storefront', 1);
		if (empty($products))
		{
			$this->is_tag = false;
			return $this;
		}

		switch (count($products))
		{
			case 1:
				$GENERAL = shopLinkcanonicalSettings::GENERAL;
				$this->canonical = new shopLinkcanonicalProductCanonical(
					$products[$GENERAL]['id'],
					$GENERAL,
					$products[$GENERAL]['canonical']
				);
				break;
			case 2:
				$this->canonical = new shopLinkcanonicalProductCanonical(
					$products[$storefront]['id'],
					$storefront,
					$products[$storefront]['canonical']
				);
				break;
		}

		$this->is_tag = true;

		return $this;
	}

	public function isTag()
	{
		if ($this->is_tag === null)
		{
			$this->setProductCanonical();
		}

		return $this->is_tag && $this->canonical->hasUrl();
	}

	public function getCanonical($full = false)
	{
		if ($this->setProductCanonical()->isTag())
		{
			$url = $this->canonical->getUrl();

			return !$full
				? $url
				: "<link rel=\"canonical\" href=\"{$url}\"/>";
		}

		return '';
	}
}
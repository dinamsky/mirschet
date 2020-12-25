<?php

class shopLinkcanonicalCanonicalCategory implements shopLinkcanonicalILinkCanonical
{
	private static $have_personal_canonical = false;

	/** @var shopLinkcanonicalCategoryCanonicalModel */
	private $category_canonical_model;

	/** @var shopCategoryModel */
	private $category_model;

	/** @var shopLinkcanonicalCategoryCanonical */
	private $canonical;

	/** @var bool */
	private $is_tag;

	public function __construct()
	{
		$this->category_canonical_model = new shopLinkcanonicalCategoryCanonicalModel();
		$this->category_model = new shopCategoryModel();
	}

	public static function havePersonalCanonical()
	{
		return self::$have_personal_canonical;
	}

	public static function isCategory()
	{
		if (waRequest::param('action') == 'category' && waRequest::param('seofilter_filter_url') == null)
		{
			return true;
		}

		return false;
	}

	public function isTag()
	{
		if ($this->is_tag === null)
		{
			$this->setCategoryCanonical();
		}

		return $this->is_tag && $this->canonical && $this->canonical->hasUrl();
	}

	public function getCanonical($full = false)
	{
		if ($this->setCategoryCanonical()->isTag())
		{
			$url = $this->canonical->getUrl();

			return !$full
				? $url
				: "<link rel=\"canonical\" href=\"{$url}\"/>";
		}

		return '';
	}

	private function setCategoryCanonical()
	{
		if (!self::isCategory())
		{
			$this->is_tag = false;

			return $this;
		}

		$url = new shopLinkcanonicalUrl();
		$settings = shopLinkcanonicalPlugin::getInstance()->getStorefrontSettings();
		if ($settings['canonical_pagination_not'])
		{
			if ($url->hasParam('page') && count($url->getQueryParams()) == 1)
			{
				$this->is_tag = false;

				return $this;
			}
		}

		if ($this->is_tag === true)
		{
			return $this;
		}

		$routing = new shopLinkcanonicalWaRouting();
		$storefront = $routing->getCurrentStorefront();
		$storefront = ifempty($storefront, '*');

		$category_url = waRequest::param('category_url');
		$category_id = waRequest::param('category_id');

		if (!$category_id)
		{
			$url_field = waRequest::param('url_type') == shopLinkcanonicalUrlType::FLAT ? 'url' : 'full_url';
			$category_table_name = $this->category_model->getTableName();
			$canonical_table_name = $this->category_canonical_model->getTableName();
			$escape_category_url = $this->category_model->escape($category_url);
			$escape_storefront = $this->category_model->escape($storefront);

			$sql = "SELECT c.id, cc.canonical, cc.storefront FROM `{$category_table_name}` c 
INNER JOIN `{$canonical_table_name}` cc ON cc.category_id=c.id
WHERE c.{$url_field}='{$escape_category_url}' AND cc.storefront IN ('*', '{$escape_storefront}')";

			$categories = $this->category_canonical_model->query($sql)->fetchAll('storefront', 1);
		}
		else
		{
			$categories = $this->category_canonical_model->where("category_id=" . $category_id)->fetchAll(
				'storefront',
				1
			);
		}



		if (empty($categories))
		{
			$this->is_tag = false;

			return $this;
		}

		if (isset($categories[$storefront]))
		{
			$settings_storefront = $storefront;
		}
		elseif (isset($categories[shopLinkcanonicalSettings::GENERAL]))
		{
			$settings_storefront = shopLinkcanonicalSettings::GENERAL;
		}
		else
		{
			return $this;
		}

		$this->is_tag = true;

		self::$have_personal_canonical = true;

		$this->canonical = new shopLinkcanonicalCategoryCanonical(
			$categories[$settings_storefront]['id'],
			$settings_storefront,
			$categories[$settings_storefront]['canonical']
		);

		return $this;
	}
}
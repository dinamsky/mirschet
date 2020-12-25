<?php

class shopLinkcanonicalSettings
{
	protected $settings = array();
	protected $default = array();

	const GENERAL = '*';

	public function __construct($settings, $storefront = self::GENERAL)
	{
		$this->settings = self::getStorefrontSettings($settings, $storefront);
		$this->default = self::getDefaultSettings();
	}

	public function __set($name, $value)
	{
		$this->settings[$name] = $value;
	}

	public function __get($name)
	{
		if (array_key_exists($name, $this->settings))
		{
			return $this->settings[$name];
		}

		return null;
	}

	public function __isset($name)
	{
		return isset($this->settings[$name]);
	}

	public function __unset($name)
	{
		unset($this->settings[$name]);
	}

	public function getSettings($name = null)
	{
		return is_null($name) ? $this->settings : $this->getSettings($name);
	}

	/**
	 * @param string $name
	 * @return mixed
	 * @throws waException
	 */
	public function getSetting($name)
	{
		if (!isset($this->default[$name]))
		{
			throw new waException($name . ' not found');
		}

		return isset($this->settings[$name]) ? $this->settings[$name] : $this->default[$name];
	}

	public function setSetting($name, $value)
	{
		$this->settings[$name] = $value;
	}

	public function isEnable()
	{
		return $this->getSetting('enable');
	}

	public function isGetParams()
	{
		return $this->getSetting('get');
	}

	/**
	 * Возвращает строку параметров,
	 * которые должны остаться в канонической ссылке,
	 * разделенные черз запятую или пробел.
	 *
	 * @return string
	 */
	public function getQueriesInsert()
	{
		return $this->getSetting('get_words');
	}

	/**
	 * Возвращает массив параметров, которые должны остаться в канонической ссылке.
	 *
	 * @return array
	 */
	public function getQueriesInsertArray()
	{
		$geuries_insert = preg_split('/[\\s,]+/', $this->getQueriesInsert());
		$geuries_insert = explode(',', $this->getQueriesInsert());
		$geuries_insert = array_map('trim', array_filter($geuries_insert));

		return $geuries_insert;
	}

	/**
	 * Простовлять тэг на кананочиеской странице
	 *
	 * @return bool
	 */
	public function isCanonicalPage()
	{
		return $this->getSetting('canonical_page');
	}

	/**
	 * Не простовлять canonical на страницах пагинации
	 *
	 * @return bool
	 */
	public function isNotCanonicalPagination()
	{
		return $this->getSetting('canonical_pagination_not');
	}

	/**
	 * Вставлять rel="prev", rel="next" тэги
	 *
	 * @return mixed
	 */
	public function isSeoPagination()
	{
		return $this->getSetting('seo_pagination');
	}

	/**
	 * Не проставлять тэг со страниц пагинации на страницу категории
	 *
	 * @return bool
	 */
	public function isCategoryPagination()
	{
		return $this->getSetting('category_pagination');
	}

	/**
	 * Не проставлять тег со страниц фильтров на страницу категории
	 *
	 * @return bool
	 */
	public function isCategoryFilter()
	{
		return $this->getSetting('category_filter');
	}

	/**
	 * Не проставлять тег со страниц сортировки на страницу категории
	 *
	 * @return bool
	 */
	public function isCategorySort()
	{
		return $this->getSetting('category_sort');
	}

	/**
	 * Не проставлять тег со страниц пагинации на страницу SEO-фильтра
	 *
	 * @return bool
	 */
	public function isFilterPagination()
	{
		return $this->getSetting('filter_pagination');
	}

	/**
	 * Проставлять тег со страниц отзывов на товар
	 *
	 * @return bool
	 */
	public function isProductReview()
	{
		return $this->getSetting('product_review');
	}

	/**
	 * Проставлять тег со страниц отзывов на товар (только если нет отзывов)
	 *
	 * @return bool
	 */
	public function isProductReviewOfNoReviewsOnly()
	{
		return $this->getSetting('product_review_if_no_reviews_only');
	}

	/**
	 * Проставлять тег со всех подстраниц товара на сам товар
	 *
	 * @return bool
	 */
	public function isProductPages()
	{
		return $this->getSetting('product_pages');
	}

	/**
	 * Не проставлять тег со страниц пагинации на страницу бренда
	 *
	 * @return bool
	 */
	public function isBrandPagination()
	{
		return $this->getSetting('brand_pagination');
	}

	/**
	 * Не проставлять тег со страниц пагинации на страницу подкатегории брендов
	 *
	 * @return bool
	 */
	public function isBrandCategoryPagination()
	{
		return $this->getSetting('brand_category_pagination');
	}

	public function lowercaseIsForced()
	{
		return $this->getSetting('to_lowercase');
	}

	public function showCanonicalOnSearchproPluginPage()
	{
		return !$this->getSetting('searchpro_no_canonical_on_search_page');
	}

	public static function getDefaultSettings()
	{
		return array(
			'enable' => false,
			'get' => false,
			'get_words' => '',
			'canonical_page' => false,
			'canonical_pagination_not' => false,
			'seo_pagination' => false,
			'category_pagination' => false,
			'category_filter' => false,
			'category_sort' => false,
			'filter_pagination' => false,
			'product_review' => false,
			'product_review_if_no_reviews_only' => false,
			'product_pages' => false,
			'brand_pagination' => false,
			'brand_category_pagination' => false,
			'to_lowercase' => false,
			'searchpro_no_canonical_on_search_page' => false,
		);
	}

	public static function getStorefrontSettings($settings, $storefront)
	{
		$default_settings = self::getDefaultSettings();
		if (!is_array($settings))
		{
			return $default_settings;
		}

		foreach ($default_settings as $name => $value)
		{
			$default_settings[$name] = ifset($settings[$storefront . $name], ifset($settings[self::GENERAL . $name], $value));
		}
		$default_settings['storefront'] = $storefront;
		$default_settings['enable'] = $settings[self::GENERAL . 'enable'];

		return $default_settings;
	}
}

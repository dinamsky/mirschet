<?php

class shopLinkcanonicalCanonicalSettings implements shopLinkcanonicalILinkCanonical
{
	/**
	 * @var shopLinkcanonicalUrl
	 */
	private $url;

	/**
	 * @var shopLinkcanonicalCanonicalUrl
	 */
	private $canonical;

	/**
	 * @var shopLinkcanonicalPlugin
	 */
	private $plugin;
	/**
	 * @var shopLinkcanonicalSettings
	 */
	private $settings;

	/**
	 * @var bool возвращать canonical?
	 */
	private $is_tag;

	/**
	 * Класс по сути action. Учитывая настройки плагина создает и возвращает canonical.
	 *
	 * shopLinkcanonicalCanonicalAction constructor.
	 */
	public function __construct()
	{
		$this->url = new shopLinkcanonicalUrl();
		$this->canonical = new shopLinkcanonicalCanonicalUrl();
		$this->plugin = shopLinkcanonicalPlugin::getInstance();
		$routing = new shopLinkcanonicalWaRouting();
		$storefront = $routing->getCurrentStorefront();
		$this->settings = new shopLinkcanonicalSettings($this->plugin->getSettings(), $storefront);
		$this->is_tag = false;
	}

	/**
	 * Устанавливает конфиги в зависимости от экшена.
	 * По условиям настроек устанавливает $is_tag (возращать canonical) true || false.
	 *
	 * @return $this
	 */
	private function setConfig()
	{
		$plugin = waRequest::param('plugin');
		$action = waRequest::param('action');

		if ($action === 'category')
		{
			$this->setCategoryConfig();
		}
		elseif ($action === 'result')
		{
			// Seofilter < v1.6
			$this->setSeofilterConfig();
		}
		elseif ($action === 'product')
		{
			$this->setProductPageConfig();
		}
		elseif ($action === 'productReviews')
		{
			$this->setProductReviewsConfig();
		}
		elseif ($action === 'productPage')
		{
			$this->setProductPageConfig();
		}
		elseif ($action === 'brand')
		{
			$this->setBrandConfig();
		}
		elseif ($plugin === 'seobrand')
		{
			$this->setSeobrandConfig();
		}
		elseif (
			$plugin === 'searchpro' && $action === 'page'
			&& !$this->settings->showCanonicalOnSearchproPluginPage()
		)
		{
			$this->is_tag = false;

			return $this;
		}

		if ($this->settings->isNotCanonicalPagination())
		{
			if ($this->url->hasParam('page') && count($this->url->getQueryParams()) == 1)
			{
				$query = $this->url->getQuery();
				parse_str($query, $query_params);

				if ($query_params['page'] == ('' . (int)$query_params['page']))
				{
					$this->is_tag = false;

					return $this;
				}
				else
				{
					$this->is_tag = true;

					return $this;
				}
			}
		}

		if (in_array($action, array('search', 'cart', 'onestep')))
		{
			$this->is_tag = false;

			return $this;
		}


		$this->canonical->mergeQueriesInsert($this->settings->getQueriesInsertArray());

		/*
		 * if (есть query and нет исключений and s_query)
		 * or (s_каноническая_страница and нет query)
		 * or (есть исключения and (s_каноническая_страница get==true) and есть query)
		*/
		$query = $this->url->getQuery();
		$canonical_query = $this->canonical->getQuery();
		if (!empty($query) && empty($canonical_query) && $this->settings->isGetParams())
		{
			$this->is_tag = true;
		}
		if (empty($query) && $this->settings->isCanonicalPage())
		{
			$this->is_tag = true;
		}
		$has_get = count($this->canonical->getQuriesInsert()) < count($this->url->getQueryParams());
		if (!empty($canonical_query) && !empty($query) && ($this->settings->isCanonicalPage() || $has_get))
		{
			$this->is_tag = true;
		}

		return $this;
	}

	private function setCategoryConfig()
	{
		$seofilter_attributes = shopLinkcanonicalPlugin::getCurrentSeofilter();
		if ($seofilter_attributes)
		{
			$seofilter_filter_url = waRequest::param('seofilter_filter_url');

			if (method_exists($seofilter_attributes, 'getCanonicalUrl'))
			{
				// Seofilter >= v2.10
				$canonicalUrl = $seofilter_attributes->getCanonicalUrl();
				if ($canonicalUrl !== null)
				{
					$this->canonical->setUrl($seofilter_attributes->getCanonicalUrl());
				}

				$this->setSeofilterConfig();
			}
			elseif ($seofilter_filter_url != null)
			{
				// Seofilter > v2.0
				$this->canonical->appendPathInsert($seofilter_filter_url);
				$this->setSeofilterConfig();
			}
		}
		else
		{
			if ($this->settings->isCategoryPagination())
			{
				$this->canonical->appendQueryInsert('page');
			}
			if ($this->settings->isCategorySort())
			{
				$this->canonical->mergeQueriesInsert(
					array(
						'sort',
						'order',
					)
				);
			}
			if ($this->settings->isCategoryFilter())
			{
				parse_str($this->url->getQuery(), $get_params);

				$get_params = shopLinkcanonicalHelper::filterNotFeatureParams($get_params);

				$params = array_keys($get_params);
				if (!(in_array('sort', $params) || in_array('order', $params)))
				{
					$this->canonical->mergeQueriesInsert($params);
				}
			}
		}
	}

	private function setSeofilterConfig()
	{
		if ($this->settings->isFilterPagination())
		{
			$this->canonical->appendQueryInsert('page');
		}
	}

	private function setProductPageConfig()
	{
		$page_url = waRequest::param('page_url');
		if (!empty($page_url) && $this->settings->isProductPages())
		{
			$this->canonical->appendPathExclude($page_url);
			$this->settings->setSetting('canonical_page', true);
		}
	}

	private function setProductReviewsConfig()
	{
		if ($this->settings->isProductReviewOfNoReviewsOnly())
		{
			$reviews = wa()->getView()->getVars('reviews');

			if (is_array($reviews) && !count($reviews))
			{
				$this->canonical->appendPathExclude('reviews');
				$this->settings->setSetting('canonical_page', true);
			}
		}
		if ($this->settings->isProductReview())
		{
			$this->canonical->appendPathExclude('reviews');
			$this->settings->setSetting('canonical_page', true);
		}
	}

	private function setBrandConfig()
	{
		preg_match('/brand\/(.*\/)/', $this->url->getPath(), $matches);
		if (isset($matches[1]))
		{
			$explode = explode("/", $matches[1]);
			if ($this->settings->isBrandCategoryPagination() && count($explode) > 2)
			{
				$this->canonical->appendQueryInsert('page');
			}
			if ($this->settings->isBrandPagination() && count($explode) == 2)
			{
				$this->canonical->appendQueryInsert('page');
			}
		}
	}

	private function setSeobrandConfig()
	{
		if (waRequest::param('action') == 'brandPage' && waRequest::param('brand_page') == 'reviews')
		{
			$reviews = wa()->getView()->getVars('reviews');
			if (!is_array($reviews) || !count($reviews))
			{
				$url = preg_replace('/reviews\/$/', '', $this->url->getUrl());
				$this->canonical->setUrl($url);
			}
		}
	}

	public function isTag()
	{
		return $this->is_tag;
	}

	/**
	 * if $full == true<br>
	 * return '<code><link rel="canonical" href="http://example.com"/></code>'
	 *
	 * @param bool $full
	 * @return null|string
	 */
	public function getCanonical($full = false)
	{
		if ($this->setConfig()->isTag())
		{
			return !$full
				? $this->canonical->createCanonical()->getCanonical()
				: $this->canonical->createCanonical()->getLinkCanonical();
		}

		return '';
	}
}

<?php

class shopLinkcanonicalPlugin extends shopPlugin
{
	/** @var shopSeofilterFilterAttributes */
	private static $current_seofilter = null;

	/**
	 * @return shopLinkcanonicalPlugin
	 */
	public static function getInstance()
	{
		return wa('shop')->getPlugin('linkcanonical');
	}

	public static function getCurrentSeofilter()
	{
		return self::$current_seofilter;
	}

	public function saveSettings($settings = array())
	{
		$default_settings = shopLinkcanonicalSettings::getDefaultSettings();
		$delete = false;
		foreach ($default_settings as $name => $value)
		{
			$delete = $delete || ifempty($settings[$settings['storefront'] . $name]);
		}
		if ($delete)
		{
			unset($settings['storefront']);
			parent::saveSettings($settings);
		}
		else
		{
			$settings_model = new waAppSettingsModel();
			$table_name = $settings_model->getTableName();

			$storefront = $settings_model->escape($settings['storefront']);
			$sql = "DELETE FROM {$table_name} WHERE app_id='shop.linkcanonical' AND `name` LIKE '{$storefront}%'";
			$settings_model->query($sql);

			$cache = new waVarExportCache(
				'app_settings/shop.linkcanonical',
				SystemConfig::isDebug() ? 600 : 86400,
				'webasyst'
			);
			$cache->delete();
		}
	}

	public function getStorefrontSettings()
	{
		$settings = parent::getSettings();
		$routing = new shopLinkcanonicalWaRouting();
		$storefront = $routing->getCurrentStorefront();
		$storefront = ifempty($storefront, shopLinkcanonicalSettings::GENERAL);

		return shopLinkcanonicalSettings::getStorefrontSettings($settings, $storefront);
	}

	/**
	 * Возвращает canonical
	 *
	 * @return null|string
	 */
	public function getLink()
	{
		$settings = $this->getStorefrontSettings();
		if (!$settings['enable'])
		{
			return '';
		}

		$canonical = shopLinkcanonicalLinkAction::getLink();

		if ($settings['to_lowercase'])
		{
			$canonical = mb_strtolower($canonical);
		}

		return $canonical;
	}

	/**
	 * Возвращает теги prev и next
	 *
	 * @param int $pages_count
	 * @return string
	 */
	public static function setLinks($pages_count = 1)
	{
		$settings = self::getInstance()->getStorefrontSettings();

		if (!$settings['enable'] || !$settings['seo_pagination'])
		{
			return '';
		}

		if (shopLinkcanonicalCanonicalCategory::havePersonalCanonical())
		{
			return '';
		}

		$url = new shopLinkcanonicalUrl();
		foreach ($url->getQueryParams() as $param)
		{
			if ($param != 'page')
			{
				return '';
			}
		}

		$page =  waRequest::get('page', 1, 'int');
		$current_canonical_url = new shopLinkcanonicalUrl();

		$canonical_settings = shopLinkcanonicalLinkAction::getLastLinkCanonicalSettings();
		if ($canonical_settings instanceof shopLinkcanonicalCanonicalCategory)
		{
			$current_canonical_url = $canonical_settings->getCanonical();
		}

		$pagination = new shopLinkcanonicalPagination($current_canonical_url, $page, $pages_count);

		return $pagination->getTags();
	}

	public function frontendHead()
	{
		$settings = $this->getStorefrontSettings();
		if (!$settings['enable'])
		{
			return '';
		}

		$canonical = $this->getLink();
		wa()->getView()->assign('canonical', $canonical);

		return '';
	}

	public function backendCategoryDialog($params)
	{
		$settings = $this->getStorefrontSettings();
		if (!$settings['enable'])
		{
			return '';
		}

		$routing = new shopLinkcanonicalWaRouting();
		$storefronts = $routing->getStorefronts();

		$linkcanonical_category = new shopLinkcanonicalCategoryCanonicalModel();
		$canonical_data = $linkcanonical_category->getByField('category_id', $params["id"], true);
		$canonicals = array();
		foreach ($canonical_data as $canonical)
		{
			$canonicals[$canonical['storefront']] = $canonical['canonical'];
		}

		wa()->getView()->assign('linkcanonical_storefronts', $storefronts);
		wa()->getView()->assign('linkcanonical_canonicals', $canonicals);

		$html = wa()->getView()->fetch($this->path . '/templates/Linkcanonical.html');

		return $html;
	}

	public function categorySave($params)
	{
		$canonicals = waRequest::post("linkcanonical_canonicals");
		if ($canonicals != null)
		{
			$list = new shopLinkcanonicalItemCanonicalList();
			foreach ($canonicals as $storefront => $canonical)
			{
				$list->append(
					new shopLinkcanonicalCategoryCanonical(
						$params["id"],
						$storefront,
						$canonical
					)
				);
			}
			$linkcanonical_product = new shopLinkcanonicalCategoryCanonicalModel();
			$linkcanonical_product->addCanonicals($params["id"], $list);
		}
	}

	public function backendProductEdit(shopProduct $product)
	{
		$settings = $this->getStorefrontSettings();
		if (!$settings['enable'])
		{
			return array();
		}

		$routing = new shopLinkcanonicalWaRouting();
		$storefronts = $routing->getStorefronts();

		$product_id = $product->getId();
		$linkcanonical_product = new shopLinkcanonicalProductCanonicalModel();
		$canonical_data = $linkcanonical_product->getByField('product_id', $product_id, true);
		$canonicals = array();
		foreach ($canonical_data as $canonical)
		{
			$canonicals[$canonical['storefront']] = $canonical['canonical'];
		}

		wa()->getView()->assign('linkcanonical_storefronts', $storefronts);
		wa()->getView()->assign('linkcanonical_canonicals', $canonicals);

		$html = wa()->getView()->fetch($this->path . '/templates/Linkcanonical.html');

		return array(
			'basics' => $html,
		);
	}

	public function productSave($params)
	{
		$canonicals = waRequest::post("linkcanonical_canonicals");
		if ($canonicals != null)
		{
			$list = new shopLinkcanonicalItemCanonicalList();
			foreach ($canonicals as $storefront => $canonical)
			{
				$list->append(
					new shopLinkcanonicalProductCanonical(
						$params["data"]["id"],
						$storefront,
						$canonical
					)
				);
			}
			$linkcanonical_product = new shopLinkcanonicalProductCanonicalModel();
			$linkcanonical_product->addCanonicals($params["data"]["id"], $list);
		}
	}

	public function handleSeofilterFrontend(&$params)
	{
		self::$current_seofilter = $params['filter'];
	}
}

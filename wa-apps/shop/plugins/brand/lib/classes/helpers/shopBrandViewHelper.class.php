<?php

class shopBrandViewHelper
{
	private static $plugin_settings = null;

	/**
	 * @param int $brand_id
	 * @return null|shopBrandBrand
	 */
	public static function getBrand($brand_id)
	{
		if (!self::isEnabled())
		{
			return null;
		}

		$storage = new shopBrandBrandStorage();

		return $storage->getById($brand_id);
	}

	/**
	 * @param array|shopProduct|int $product
	 * @return null|shopBrandBrand
	 */
	public static function getProductBrand($product)
	{
		if (!self::isEnabled())
		{
			return null;
		}

		$storage = new shopBrandBrandStorage();

		try
		{
			return $storage->getByProduct($product);
		}
		catch (waException $e)
		{
			return null;
		}
	}

	/**
	 * @param array|shopProduct|int $product
	 * @return shopBrandBrand[]
	 */
	public static function getAllProductBrands($product)
	{
		if (!self::isEnabled())
		{
			return null;
		}

		$storage = new shopBrandBrandStorage();

		try
		{
			$product_brands = $storage->getAllByProduct($product);
		}
		catch (waException $e)
		{
			$product_brands = array();
		}

		return $product_brands;
	}

	/**
	 * @return shopBrandBrand[]
	 */
	public static function getAllBrands()
	{
		if (!self::isEnabled())
		{
			return array();
		}

		$settings_storage = new shopBrandSettingsStorage();

		$brands_collection = shopBrandBrandsCollectionFactory::getBrandsCollection($settings_storage->getSettings());

		return $brands_collection
			->withImagesOnly(false)
			->getBrands();
	}

	public static function getAllBrandsWithImages()
	{
		if (!self::isEnabled())
		{
			return array();
		}

		$settings_storage = new shopBrandSettingsStorage();

		$brands_collection = shopBrandBrandsCollectionFactory::getBrandsCollection($settings_storage->getSettings());

		return $brands_collection
			->withImagesOnly(true)
			->getBrands();
	}

	public static function getCountryHtml($iso3_code)
	{
		if (!self::isEnabled())
		{
			return '';
		}

		$iso3_code = strtolower(trim($iso3_code));

		$country_model = waCountryModel::getInstance();
		$country = $country_model->get($iso3_code);

		if (!$country)
		{
			return '';
		}

		$path = wa()->getDataPath('countries/' . $iso3_code . '.png', true, 'shop', false);
		if (file_exists($path))
		{
			$image_url = wa()->getDataUrl('countries/' . $iso3_code . '.png', true, 'shop');
		}
		else
		{
			$image_url = wa()->getRootUrl() . "wa-content/img/country/{$iso3_code}.gif";
		}

		$country_name = $country['name'];
		if ($iso3_code == 'usa')
		{
			$country_name = 'США';
		}
		elseif ($iso3_code == 'rus')
		{
			$country_name = 'Россия';
		}
		else
		{
			$country_name = preg_replace('/\s*\([^()]+\)($|\s*)/', '$1', $country_name);
		}

		return "
<div class=\"seobrand-country brand-country\">
	<div class=\"title\">Страна производитель</div>
	<img class=\"image\" src=\"{$image_url}\" title=\"{$country_name}\">{$country_name}
</div>
";
	}

	public static function toTree($categories)
	{
		if (!self::isEnabled())
		{
			return array();
		}

		$stack = array();
		$result = array();
		foreach ($categories as $c) {
			$c['children'] = array();

			// Number of stack items
			$l = count($stack);

			// Check if we're dealing with different levels
			while ($l > 0 && $stack[$l - 1]['depth'] >= $c['depth']) {
				array_pop($stack);
				$l--;
			}

			// Stack is empty (we are inspecting the root)
			if ($l == 0) {
				// Assigning the root node
				$i = count($result);
				$result[$i] = $c;
				$stack[] = &$result[$i];
			} else {
				// Add node to parent
				$i = count($stack[$l - 1]['children']);
				$stack[$l - 1]['children'][$i] = $c;
				$stack[] = &$stack[$l - 1]['children'][$i];
			}
		}
		return $result;
	}

	public static function groupCategoriesPlainTreeByColumns($category_plain_tree, $columns_count = 2)
	{
		if (!self::isEnabled())
		{
			return array();
		}

		$limit = ceil(count($category_plain_tree) / $columns_count - 1e-6) - 1;

		$column_categories = array();
		for ($i = 0; $i < $columns_count; $i++)
		{
			$column_categories[$i] = array();
		}

		$current_column = 0;
		$current_count = 0;
		$root_element_depth = -1;

		foreach ($category_plain_tree as $category)
		{
			if ($root_element_depth < 0)
			{
				$root_element_depth = $category['depth'];
			}

			if ($current_count > $limit && $category['depth'] == $root_element_depth)
			{
				$current_column++;
				$root_element_depth = -1;
				$current_count = 0;
			}

			$column_categories[$current_column][] = $category;
			$current_count++;
		}

		return $column_categories;
	}

	public static function getBrandPages($brand_id)
	{
		if (!self::isEnabled())
		{
			return array();
		}

		$page_storage = new shopBrandPageStorage();
		$brand_page_storage = new shopBrandBrandPageStorage();
		$settings_storage = new shopBrandSettingsStorage();
		$page_status_options = new shopBrandPageStatusEnumOptions();
		$page_type_options = new shopBrandPageTypeEnumOptions();

		$settings = $settings_storage->getSettings();

		$pages = array();
		foreach ($page_storage->getAll() as $page)
		{
			if (!$page->isMain())
			{
				if ($page->status != $page_status_options->PUBLISHED)
				{
					continue;
				}

				if ($page->type == $page_type_options->PAGE)
				{
					$brand_page = $brand_page_storage->getPage($brand_id, $page->id);
					if (!$brand_page || strlen(trim($brand_page->content)) == 0)
					{
						continue;
					}
				}
				elseif ($page->type == $page_type_options->CATALOG)
				{
				}
				elseif ($page->type == $page_type_options->REVIEWS)
				{
					if ($settings->hide_reviews_tab_if_empty)
					{
						$reviews_collection = new shopBrandBrandReviewSmartCollection($brand_id);
						$reviews_count = $reviews_collection->count();

						if ($reviews_count == 0)
						{
							continue;
						}
					}
				}
			}

			$page->is_reviews_page = $page->type == $page_type_options->REVIEWS;

			$pages[] = $page;
		}

		return $pages;
	}

	private static function isEnabled()
	{
		if (!shopBrandHelper::isBrandInstalled())
		{
			return false;
		}

		$settings = self::getPluginSettings();

		return $settings->is_enabled;
	}

	private static function getPluginSettings()
	{
		if (self::$plugin_settings === null)
		{
			$settings_storage = new shopBrandSettingsStorage();

			self::$plugin_settings = $settings_storage->getSettings();
		}

		return self::$plugin_settings;
	}
}

<?php

class shopCatalogreviewsCategoryExtender
{
	private $category_source;

	public function __construct(shopCatalogreviewsCategorySource $category_source)
	{
		$this->category_source = $category_source;
	}

	public function extend($storefront, $category, $include_parent_categories)
	{
		if ($include_parent_categories)
		{
			$path_categories = $this->category_source->getCategoryPath($category['id']);
			$parent_categories = array_reverse($path_categories);

			foreach (array_keys($parent_categories) as $index)
			{
				$parent_categories[$index] = $this->extend(
					$storefront,
					$parent_categories[$index],
					false
				);
			}

			$category['parents'] = $parent_categories;
		}

		$category = array_merge(
			$category,
			$this->category_source->getCategoryProductsData($storefront, $category['id'])
		);

		return $category;
	}
}

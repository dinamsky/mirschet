<?php

class shopCatalogreviewsReviewsCatalogViewBufferModifier
{
	private $category_source;
	private $category_extender;

	public function __construct(
		shopCatalogreviewsCategorySource $category_source,
		shopCatalogreviewsCategoryExtender $category_inner_extender
	)
	{
		$this->category_source = $category_source;
		$this->category_extender = $category_inner_extender;
	}

	public function modify($storefront, $category_id, shopCatalogreviewsViewBuffer $view_buffer)
	{
		$category = $this->category_source->getCategory($category_id);

		$category_extended = $this->category_extender->extend($storefront, $category, true);
		$vars = [
			'category' => $category_extended,
			'parent_categories' => $category_extended['parents'],
			'root_category' => reset($category_extended['parents']),
			'parent_category' => end($category_extended['parents']),
			'parent_categories_names' => [],
		];

		foreach ($category_extended['parents'] as $parent)
		{
			$vars['parent_categories_names'][] = $parent['name'];
		}

		$view_buffer->assign($vars);
	}
}

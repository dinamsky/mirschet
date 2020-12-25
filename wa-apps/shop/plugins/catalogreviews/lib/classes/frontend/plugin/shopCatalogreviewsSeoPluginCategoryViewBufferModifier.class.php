<?php

class shopCatalogreviewsSeoPluginCategoryViewBufferModifier implements shopCatalogreviewsCategoryViewBufferModifier
{
	public function modify($storefront, $category_id, shopCatalogreviewsViewBuffer $view_buffer)
	{
		$current_vars = $view_buffer->getVars();
		if (
			!isset($current_vars['category']) || !is_array($current_vars['category'])
			|| intval($current_vars['category']['id']) !== intval($category_id)
		)
		{
			return;
		}

		$category = $current_vars['category'];

		$category_data_collector = shopSeoContext::getInstance()->getCategoryDataCollector();

		$seo_name = $category_data_collector->collectSeoName($storefront, $category['id'], $info);
		$fields = $category_data_collector->collectFieldsValues($storefront, $category['id'], $info);

		if ($seo_name === '')
		{
			$seo_name = $category['name'];
		}

		$category['seo_name'] = $seo_name;
		$category['fields'] = $fields;

		$view_buffer->assign('category', $category);
	}
}

<?php

interface shopCatalogreviewsCategorySource
{
	public function getCategory($category_id);

	public function getRootCategories();

	public function getCategoriesByLeftKey();

	public function getCategoryByUrl($url);

	public function getCategoryByFullUrl($category_full_url);

	public function getCategoryPath($category_id);

	public function getCategoryProductsData($storefront, $category_id);
}

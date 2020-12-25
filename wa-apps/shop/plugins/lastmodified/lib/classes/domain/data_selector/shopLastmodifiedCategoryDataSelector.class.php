<?php


class shopLastmodifiedCategoryDataSelector implements shopLastmodifiedDataSelector
{
	public function getData()
	{
		$category = wa()->getView()->getVars('category');
		$trimmer = new shopLastmodifiedDataTrimmer();
		$category = $trimmer->trimCategory($category);
		
		$products = wa()->getView()->getVars('products');
		
		if (is_array($products))
		{
			foreach ($products as $i => $product)
			{
				$products[$i] = $trimmer->trimProduct($product);
			}
		}
		
		return array(
			'category' => $category,
			'products' => $products,
		);
	}
}
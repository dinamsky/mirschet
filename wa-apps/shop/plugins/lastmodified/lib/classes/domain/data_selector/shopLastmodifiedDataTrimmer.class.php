<?php


class shopLastmodifiedDataTrimmer
{
	public function trimCategory($category)
	{
		if (!is_array($category))
		{
			return $category;
		}
		
		foreach (array('id_1c', 'left_key', 'right_key', 'edit_datetime', 'meta_title', 'meta_keywords', 'meta_description') as $key)
		{
			if (isset($category[$key]))
			{
				unset($category[$key]);
			}
		}
		
		if (isset($category['subcategories']) && is_array($category['subcategories']))
		{
			foreach ($category['subcategories'] as $i => $subcategory)
			{
				$category['subcategories'][$i] = $this->trimCategory($subcategory);
			}
		}
		
		return $category;
	}
	
	public function trimProduct($product)
	{
		if ($product instanceof shopProduct)
		{
			$product = $product->getData();
		}
		
		if (!is_array($product))
		{
			return $product;
		}
		
		foreach ($product as $key => $value)
		{
			if (is_int($key))
			{
				unset($product[$key]);
			}
		}
		
		foreach (array('id_1c', 'total_sales', 'total_sales_html', 'edit_datetime', 'meta_title', 'meta_keywords', 'meta_description') as $key)
		{
			if (array_key_exists($key, $product))
			{
				unset($product[$key]);
			}
		}
		
		if (isset($product['categories']) && is_array($product['categories']))
		{
			foreach ($product['categories'] as $i => $category)
			{
				$product['categories'][$i] = $this->trimCategory($category);
			}
		}
		
		if (isset($product['features_selectable']) && is_array($product['features_selectable']))
		{
			foreach ($product['features_selectable'] as $i => $feature_selectable)
			{
				$product['features_selectable'][$i] = $this->trimFeatureSelectable($feature_selectable);
			}
		}
		
		if (isset($product['skus']) && is_array($product['skus']))
		{
			foreach ($product['skus'] as $i => $sku)
			{
				$product['skus'][$i] = $this->trimSku($sku);
			}
		}
		
		if (isset($product['pages']) && is_array($product['pages']))
		{
			foreach ($product['pages'] as $i => $page)
			{
				$product['pages'][$i] = $this->trimPage($page);
			}
		}
		
		if (isset($product['count']) && $product['count'] !== 0)
		{
			$product['count'] = null;
		}
		
		return $product;
	}
	
	public function trimPage($page)
	{
		if (!is_array($page))
		{
			return $page;
		}
		
		foreach (array('update_datetime', 'meta_title', 'meta_keywords', 'meta_description') as $key)
		{
			if (array_key_exists($key, $page))
			{
				unset($page[$key]);
			}
		}
		
		return $page;
	}
	
	private function trimFeatureSelectable($feature_selectable)
	{
		if (!is_array($feature_selectable))
		{
			return $feature_selectable;
		}
		
		foreach (array('cml1c_id') as $key)
		{
			if (array_key_exists($key, $feature_selectable))
			{
				unset($feature_selectable[$key]);
			}
		}
		
		return $feature_selectable;
	}
	
	private function trimSku($sku)
	{
		if (!is_array($sku))
		{
			return $sku;
		}
		
		foreach (array('id_1c') as $key)
		{
			if (array_key_exists($key, $sku))
			{
				unset($sku[$key]);
			}
		}
		
		if (isset($sku['stock']) && is_array($sku['stock']))
		{
			if (method_exists('shopHelper', 'fillVirtulStock'))
			{
				$sku['stock'] = shopHelper::fillVirtulStock($sku['stock']);
			}
			else
			{
				$stock_model = new shopStockModel();
				$stocks = $stock_model->getAll('id');
				
				foreach ($stocks as $stock_id => $stock)
				{
					if (!array_key_exists($stock_id, $sku['stock']))
					{
						$sku['stock'][$stock_id] = null;
					}
				}
			}
			
			foreach ($sku['stock'] as $stock_id => $count)
			{
				if ($count !== 0)
				{
					$sku['stock'][$stock_id] = null;
				}
			}
		}
		
		if (isset($sku['count']) && $sku['count'] !== 0)
		{
			$sku['count'] = null;
		}
		
		return $sku;
	}
}
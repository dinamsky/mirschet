<?php

class shopBrandPluginFrontendBrandsAction extends shopBrandFrontendActionWithMeta
{
	public function executeBrandPage(shopBrandFetchedLayout $fetched_layout)
	{
		$route_params = array(
			'module' => 'frontend',
			'plugin' => 'brand',
			'action' => 'brands',
		);

		$this->view->assign(array(
			'brands' => $this->getBrands(),
			'h1' => $fetched_layout->h1,
			'description' => $fetched_layout->description,
			'brands_page_url' => wa()->getRouteUrl('shop', $route_params)
		));

		waSystem::popActivePlugin();
	}

	protected function getTemplateLayout()
	{
		$template_collector = new shopBrandTemplateCollector();

		$template_layout = $template_collector->getBrandsPageTemplateLayout($this->storefront);
		$templates = $template_layout->getTemplates();

		$fields_to_check = array(
			'h1',
			'meta_title',
		);

		foreach ($fields_to_check as $field)
		{
			if (!is_string($templates[$field]) || mb_strlen(trim($templates[$field])) == 0)
			{
				$templates[$field] = 'Бренды';
			}
		}

		return new shopBrandTemplateLayout($templates);
	}

	protected function getActionTemplate()
	{
		return new shopBrandBrandsActionTemplate($this->getTheme());
	}

	private function getBrands()
	{
		$settings_storage = new shopBrandSettingsStorage();
		$settings = $settings_storage->getSettings();

		try
		{
			$collection = shopBrandBrandsCollectionFactory::getBrandsCollection($settings);
		}
		catch (Exception $e)
		{
			return array();
		}

		$this->setSort($collection);

		return $collection->getBrands();
	}

	private function setSort(shopBrandBrandsCollection $c)
	{
		$sort = waRequest::request('sort');
		$order = waRequest::request('order');

		if ($sort)
		{
			$c->sort($sort, $order);
		}
	}
}

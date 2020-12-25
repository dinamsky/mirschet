<?php

class shopCatalogreviewsWaRouting implements shopCatalogreviewsStorefrontSource
{
	public function isPageAction()
	{
		return $this->isAction('frontend', 'page');
	}

	public function isCategoryAction()
	{
		return $this->isAction('frontend', 'category');
	}

	public function getStorefronts()
	{
		$routing = wa()->getRouting();
		$domains = $routing->getByApp('shop');

		$storefronts = [];

		foreach ($domains as $domain => $routes)
		{
			foreach ($routes as $route)
			{
				if ((!method_exists($routing, 'isAlias') || !$routing->isAlias($domain)) && isset($route['url']))
				{
					$storefronts[] = $domain . '/' . $route['url'];
				}
			}
		}

		return $storefronts;
	}

	public function getCurrentStorefront()
	{
		$routing = wa()->getRouting();
		$route = $routing->getRoute();

		if ($route['app'] === 'shop')
		{
			$domain = $routing->getDomain();

			return $domain . '/' . $route['url'];
		}

		return null;
	}

	public function getCurrentUrl()
	{
		return wa()->getRouting()->getCurrentUrl();
	}

	public function getCurrentDomain()
	{
		return wa()->getRouting()->getDomain();
	}

	public function getCategoryFromUrl()
	{
	}

	private function isAction($module, $action, $plugin = null)
	{
		return waRequest::param('module') === $module
			&& waRequest::param('action') === $action
			&& waRequest::param('plugin') === $plugin;
	}

	public function getRootUrl()
	{
		return wa()->getRouting()->getRootUrl();
	}
}

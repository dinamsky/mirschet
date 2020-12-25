<?php

class shopCatalogreviewsWaRoutingHandler
{
	private static $env = null;

	private $wa_system;

	public function __construct(waSystem $wa_system)
	{
		$this->wa_system = $wa_system;
	}

	/**
	 * @param $shop_route
	 * @return shopCatalogreviewsPluginEnv
	 */
	public function handle($shop_route)
	{
		if (!isset(self::$env))
		{
			self::$env = $this->wa_system->getEnv() === 'backend'
				? $this->buildBackendPluginEnv()
				: $this->buildWaPluginEnv($shop_route);
		}

		return self::$env;
	}

	/**
	 * @return shopCatalogreviewsBackendPluginEnv
	 */
	private function buildBackendPluginEnv()
	{
		$context = shopCatalogreviewsContext::getFrontendInstance();

		$config_storage = $context->getConfigStorage();

		$plugin_config = $config_storage->getPluginConfig('');
		$empty_dispatch_result = new shopCatalogreviewsWaPluginRoutingDispatchResult('', [], '', [], [], null);
		$empty_full_config = $config_storage->getEmptyFullConfig($plugin_config);

		$plugin_routing = new shopCatalogreviewsWaPluginRouting(
			$plugin_config,
			$empty_dispatch_result,
			$context->getWaRouting()
		);

		return new shopCatalogreviewsBackendPluginEnv($empty_full_config, $plugin_routing);
	}

	/**
	 * @param $shop_route
	 * @return shopCatalogreviewsWaPluginEnv
	 */
	private function buildWaPluginEnv($shop_route)
	{
		$context = shopCatalogreviewsContext::getFrontendInstance();

		$config_storage = $context->getConfigStorage();
		$dispatcher = $context->getWaPluginRoutingDispatcher();
		$wa_routing = $context->getWaRouting();


		$domain = $wa_routing->getCurrentDomain();
		$current_url = $wa_routing->getCurrentUrl();


		$storefront = is_array($shop_route) && isset($shop_route['app']) && $shop_route['app'] === 'shop'
		&& isset($shop_route['url'])
			? $domain . '/' . $shop_route['url']
			: null;
		$plugin_config = $config_storage->getPluginConfig($storefront);

		if ($plugin_config->plugin_is_enabled)
		{
			$dispatch_result = $dispatcher->dispatch(
				$plugin_config,
				$domain,
				$shop_route,
				$current_url
			);
			$category = $dispatch_result->getCategory();

			$full_config = $category
				? $config_storage->getFullConfig($storefront, $category['id'])
				: $config_storage->getEmptyFullConfig($plugin_config);
		}
		else
		{
			$dispatch_result = new shopCatalogreviewsWaPluginRoutingDispatchResult(
				$domain,
				$shop_route,
				$current_url,
				[],
				[],
				null
			);
			$category = null;
			$full_config = $config_storage->getEmptyFullConfig($plugin_config);
		}


		$plugin_routing = new shopCatalogreviewsWaPluginRouting(
			$plugin_config,
			$dispatch_result,
			$context->getWaRouting()
		);

		return new shopCatalogreviewsWaPluginEnv(
			$full_config,
			$plugin_routing,
			$category,
			$storefront,
			waRequest::get('sort'),
			waRequest::get('order')
		);
	}
}

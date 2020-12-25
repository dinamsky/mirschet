<?php

class shopCatalogreviewsPlugin extends shopPlugin
{
	private static $plugin_env;
	/** @var waSystem */
	private static $wa_system;

	/**
	 * @return shopCatalogreviewsPluginEnv
	 */
	public function getEnv()
	{
		return self::$plugin_env;
	}

	/** @return waSystem */
	public function getSystem()
	{
		return self::$wa_system;
	}

	public function getRoutingRules($route)
	{
		self::$wa_system = wa('shop');

		$handler = new shopCatalogreviewsWaRoutingHandler(self::$wa_system);

		self::$plugin_env = $handler->handle($route);

		return self::$plugin_env->getActiveRouting();
	}

	public function handleBackendMenu()
	{
		$handler = new shopCatalogreviewsWaBackendMenuHandler();

		return $handler->handle();
	}

	public function handleFrontendCategory($category)
	{
		$handler = new shopCatalogreviewsWaFrontendCategoryHandler($this);

		return $handler->handle($category);
	}

	public function handleFrontendNav()
	{
		$handler = new shopCatalogreviewsWaFrontendNavHandler($this);

		return $handler->handle();
	}
}

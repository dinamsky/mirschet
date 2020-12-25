<?php

class shopLastmodifiedPlugin extends shopPlugin
{
	private static $config;
	
	public static function getConfiguration()
	{
		if (!isset(self::$config))
		{
			self::$config = new shopLastmodifiedConfiguration();
		}
		
		return self::$config;
	}
	
	public function isEnable()
	{
		$settings_storage = self::getConfiguration()->getSettingsStorage();
		$settings_plugin = $settings_storage->getSettingsPlugin();
		
		return !!$settings_plugin->isEnable() && !waRequest::isXMLHttpRequest();
	}

	public function handleFrontendHead()
	{
		if (!$this->isEnable())
		{
			return;
		}

		$action = waRequest::param('action');

		if (!$action)
		{
			$request_handler = self::getConfiguration()->getRequestHandlerBuilder()->buildBySettingsGroup(
				shopLastmodifiedSettingsGroupId::HOME,
				new shopLastmodifiedHomeDataSelector()
			);
			$request_handler->handle();
		}
		elseif ($action == 'category')
		{
			$request_handler = self::getConfiguration()->getRequestHandlerBuilder()->buildBySettingsGroup(
				shopLastmodifiedSettingsGroupId::CATEGORY,
				new shopLastmodifiedCategoryDataSelector()
			);
			$request_handler->handle();
		}
		elseif ($action == 'product')
		{
			$request_handler = self::getConfiguration()->getRequestHandlerBuilder()->buildBySettingsGroup(
				shopLastmodifiedSettingsGroupId::PRODUCT,
				new shopLastmodifiedProductDataSelector()
			);
			$request_handler->handle();
		}
		elseif ($action == 'page')
		{
			$request_handler = self::getConfiguration()->getRequestHandlerBuilder()->buildBySettingsGroup(
				shopLastmodifiedSettingsGroupId::PAGE,
				new shopLastmodifiedPageDataSelector()
			);
			$request_handler->handle();
		}
		elseif ($action == 'brand')
		{
			$request_handler = self::getConfiguration()->getRequestHandlerBuilder()->buildBySettingsGroup(
				shopLastmodifiedSettingsGroupId::BRAND,
				new shopLastmodifiedBrandDataSelector()
			);
			$request_handler->handle();
		}
	}
}
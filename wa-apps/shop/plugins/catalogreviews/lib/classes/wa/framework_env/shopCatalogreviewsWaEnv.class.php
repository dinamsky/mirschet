<?php

class shopCatalogreviewsWaEnv implements shopCatalogreviewsFrameworkEnv
{
	private static $_plugins_info = [];

	public function isSeoPluginInstalled()
	{
		$seo_plugin_info = $this->getPluginInfo('shop', 'seo');
		if (!is_array($seo_plugin_info) || $seo_plugin_info === [])
		{
			return false;
		}

		return array_key_exists('version', $seo_plugin_info)
			&& version_compare($seo_plugin_info['version'], '3.0', '>=')
			&& version_compare($seo_plugin_info['version'], '4.0', '<');
	}

	public function isSeoPluginEnabled()
	{
		if (!$this->isSeoPluginInstalled())
		{
			return false;
		}

		return shopSeoContext::getInstance()->getPluginSettingsService()->getSettings()->is_enabled;
	}

	public function isReviewsplusPluginInstalled()
	{
		$plugin_info = $this->getPluginInfo('shop', 'reviewsplus');

		return is_array($plugin_info) && $plugin_info !== [];
	}

	public function isReviewsplusPluginEnabled()
	{
		if (!$this->isReviewsplusPluginInstalled())
		{
			return false;
		}

		return shopReviewsplusPlugin::getState();
	}

	private function getPluginInfo($app_id, $plugin_id)
	{
		$key = "{$app_id}/{$plugin_id}";

		if (!array_key_exists($key, self::$_plugins_info))
		{
			self::$_plugins_info[$key] = wa($app_id)->getConfig()->getPluginInfo($plugin_id);
		}

		return self::$_plugins_info[$key];
	}
}

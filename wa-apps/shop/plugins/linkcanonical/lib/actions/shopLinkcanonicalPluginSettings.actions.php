<?php

class shopLinkcanonicalPluginSettingsActions extends waViewActions
{
	public function defaultAction()
	{
		$plugin = shopLinkcanonicalPlugin::getInstance();
		$routing = new shopLinkcanonicalWaRouting();

		$settings = $plugin->getSettings();
		$storefronts = $routing->getStorefronts();

		$settings_model = new waAppSettingsModel();
		$is_storefronts = array();
		foreach ($storefronts as $storefront)
		{
			$count = $settings_model
				->select('COUNT(*)')
				->where('app_id="shop.linkcanonical" AND `name` LIKE "' . $storefront . '%"')
				->fetchField();
			$is_storefronts[$storefront] = $count ? true : false;
		}

		$storefront = waRequest::get('storefront', shopLinkcanonicalSettings::GENERAL);

		$version = floatval($plugin->getVersion());

		$plugin_static_url = $plugin->getPluginStaticUrl(true);
		$css = $plugin_static_url . 'css/linkcanonical.css?v' . $version;
		$js = $plugin_static_url . 'js/linkcanonical.js?v' . $version;
		wa()->getView()->assign(
			array(
				'settings' => $settings,
				'storefronts' => $is_storefronts,
				'storefront' => $storefront,
				'css' => $css,
				'js' => $js,
			)
		);

		$this->setLocalTemplate('actions/settings/Settings.html');
	}

	public function saveAction()
	{
		if (!is_null(waRequest::post('is_submit')))
		{
			$plugin = shopLinkcanonicalPlugin::getInstance();
			$plugin->saveSettings(waRequest::post('settings', array()));
			echo json_encode(array('status' => 'ok'));
		}
		$this->display();
	}

	private function setLocalTemplate($filename)
	{
		$path = wa()->getAppPath('plugins/linkcanonical/templates/' . $filename, 'shop');

		waLocale::loadByDomain(array('shop', 'linkcanonical'));
		waSystem::pushActivePlugin('linkcanonical', 'shop');
		parent::setTemplate($path);
		waSystem::popActivePlugin();
	}

	public function display()
	{
		if ($this->action !== 'save')
		{
			parent::display();
		}
	}
}
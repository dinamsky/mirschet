<?php

class shopCatalogreviewsPluginPluginSettingsGetPluginStorefrontSettingsController extends waJsonController
{
	public function execute()
	{
		$this->response['success'] = false;

		$storefront = waRequest::get('storefront');


		$plugin_settings_controller = shopCatalogreviewsContext::getBackendInstance()->getPluginSettingsController();

		$this->response['state'] = $plugin_settings_controller->getStorefrontSettingsState($storefront);
		$this->response['success'] = true;
	}
}

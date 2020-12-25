<?php

class shopSeoredirectPluginSettingsJsonController extends waJsonController
{
	public function execute()
	{
		$method = waRequest::get('method');
		$method = mb_strtolower($method);
		if ($method == 'get')
		{
			$this->get();
		}
		elseif ($method == 'getdomains')
		{
			$this->getDomains();
		}
		elseif ($method == 'save')
		{
			$this->save();
		}

	}

	public function get()
	{
		$plugin = shopSeoredirectPlugin::getInstance();
		$this->response = $plugin->getSettings();
	}

	public function getDomains()
	{
		$routing = new shopSeoredirectWaRouting();
		$domains = $routing->getDomains();
		$this->response = $domains;
	}

	public function save()
	{
		$plugin = shopSeoredirectPlugin::getInstance();
		$plugin->saveSettings(waRequest::post('settings', array()));
	}
}
<?php

class shopSeoredirectPluginSettingsAction extends waViewAction
{
	public function execute()
	{
		$plugin = shopSeoredirectPlugin::getInstance();
		$version = $plugin->getVersion();
		$routing = new shopSeoredirectWaRouting();
		$domains = $routing->getDomains();
		
		wa()->getView()->assign(array(
			'version' => $version,
			'domains' => $domains,
		));
	}
}
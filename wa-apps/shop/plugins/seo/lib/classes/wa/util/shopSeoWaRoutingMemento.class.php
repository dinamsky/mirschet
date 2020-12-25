<?php

class shopSeoWaRoutingMemento
{
	private $routing;

	public function storeCurrent()
	{
		/** @var waSystemConfig $wa_app_config */
		$wa_app_config = waSystem::getInstance('webasyst')->getConfig();

		$routing_path = $wa_app_config->getPath('config', 'routing');

		$this->routing = include $routing_path;
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	public function getStoredRouting()
	{
		if (!is_array($this->routing)) {
			throw new Exception('No routing stored');
		}

		return $this->routing;
	}
}
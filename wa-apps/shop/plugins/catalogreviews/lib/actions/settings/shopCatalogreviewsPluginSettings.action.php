<?php

class shopCatalogreviewsPluginSettingsAction extends waViewAction
{
	public function execute()
	{
		$backend_context = shopCatalogreviewsContext::getBackendInstance();

		$controller = $backend_context->getPluginSettingsController();

		$this->view->assign('state', $controller->getState([]));
	}
}

<?php

class shopCatalogreviewsWaBackendMenuHandler
{
	public function handle()
	{
		$backend_context = shopCatalogreviewsContext::getBackendInstance();

		if (!$backend_context->userHasRights())
		{
			return [];
		}

		$template_path = shopCatalogreviewsWaHelper::getPath('templates/event_handlers/BackendMenu.CoreLi.html');
		$view = wa()->getView();

		//$is_plugin_page = waSystem::getActiveLocaleDomain() === 'shop_catalogreviews';
		$is_plugin_page = waRequest::get('plugin') === 'catalogreviews';
		$view->assign('is_plugin_page', $is_plugin_page);

		return [
			'core_li' => $view->fetch($template_path),
		];
	}
}

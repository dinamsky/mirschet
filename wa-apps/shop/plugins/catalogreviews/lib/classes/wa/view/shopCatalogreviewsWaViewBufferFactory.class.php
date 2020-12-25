<?php

class shopCatalogreviewsWaViewBufferFactory implements shopCatalogreviewsViewBufferFactory
{
	public function createViewBuffer()
	{
		$view_buffer = new shopCatalogreviewsWaViewBuffer();
		$view_buffer->assign(wa()->getView()->getVars());

		/** @var shopConfig $config */
		$config = wa('shop')->getConfig();
		$vars['host'] = waRequest::server('HTTP_HOST');
		$vars['store_info'] = [
			'name' => $config->getGeneralSettings('name'),
			'phone' => $config->getGeneralSettings('phone'),
		];

		$view_buffer->assign($vars);

		return $view_buffer;
	}
}

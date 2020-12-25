<?php

class shopCatalogreviewsWaPluginsViewBufferModifier implements shopCatalogreviewsCustomViewBufferModifier
{
	public function modify(shopCatalogreviewsViewBuffer $view_buffer)
	{
		$params = $view_buffer->getVars();
		$hook_vars = wa()->event(['shop', 'seo_fetch_templates'], $params);
		$vars = [];

		foreach ($hook_vars as $plugin_id => $_hook_vars)
		{
			$vars = array_merge($vars, $_hook_vars);
		}

		$view_buffer->assign($vars);
	}
}

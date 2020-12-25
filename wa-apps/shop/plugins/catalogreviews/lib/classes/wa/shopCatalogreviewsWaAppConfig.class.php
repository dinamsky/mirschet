<?php

class shopCatalogreviewsWaAppConfig extends waAppConfig
{
	public static function renamePlugin(waAppConfig $config, $new_name)
	{
		if (array_key_exists('catalogreviews', $config->plugins) && array_key_exists('name', $config->plugins['catalogreviews']))
		{
			$config->plugins['catalogreviews']['name'] = $new_name;
		}
	}
}

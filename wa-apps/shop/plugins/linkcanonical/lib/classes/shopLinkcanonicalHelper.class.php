<?php

class shopLinkcanonicalHelper
{
	private static $existing_codes = array(
		'price' => 1,
		'price_min' => 1,
		'price_max' => 1,
	);
	private static $not_existing_codes = array(
		'price' => 1,
		'sort' => 1,
		'order' => 1,
		'page' => 1,
	);

	public static function addBlocks()
	{
		$model = new waModel();

		$sql = 'SELECT COUNT(*) AS `count` FROM `site_block` WHERE id = "shop.seo_pagination"';
		$block_existence = (bool)$model->query($sql)->fetchField('count');

		$sql = "INSERT INTO `site_block` VALUES ('shop.seo_pagination', "
			. '\'{if $wa->shop && !empty($pages_count) && $pages_count > 1 && class_exists("shopLinkcanonicalPlugin")}{shopLinkcanonicalPlugin::setLinks($pages_count)}{/if}\',' .
			" NOW(), 'add links for next and prev page for seo', 0);";

		if($block_existence !== false)
		{
			$model->query('DELETE FROM `site_block` WHERE id = "shop.seo_pagination"');
		}
		$model->query($sql);
	}

	public static function filterNotFeatureParams($params)
	{
		$params_keys = array_keys($params);

		self::checkParamKeys($params_keys);

		foreach ($params_keys as $code)
		{
			if (isset(self::$not_existing_codes[$code]))
			{
				unset($params[$code]);
			}
		}

		return $params;
	}

	public static function filterFeatureParams($params)
	{
		$params_keys = array_keys($params);

		self::checkParamKeys($params_keys);

		foreach ($params_keys as $code)
		{
			if (isset(self::$existing_codes[$code]))
			{
				unset($params[$code]);
			}
		}

		return $params;
	}

	private static function checkParamKeys($params_keys)
	{
		$to_find = array();
		foreach ($params_keys as $code)
		{
			if (!isset(self::$existing_codes[$code]))
			{
				$to_find[] = $code;
			}
		}

		if (count($to_find))
		{
			$model = new shopFeatureModel();
			$found_features = $model->select('code')
				->where('code IN (s:codes)', array('codes' => $to_find))
				->fetchAll('code');
			foreach ($to_find as $code)
			{
				if (isset($found_features[$code]))
				{
					self::$existing_codes[$code] = true;
				}
				else
				{
					self::$not_existing_codes[$code] = true;
				}
			}
		}
	}
}
<?php
return array(
	'shop_catalogreviews_category_settings' => array(
		'category_id' => array('int', 10, 'unsigned' => 1, 'null' => 0),
		'name' => array('varchar', 64, 'null' => 0),
		'value' => array('text'),
		':keys' => array(
			'PRIMARY' => array('category_id', 'name'),
			'category_id' => 'category_id',
		),
	),
	'shop_catalogreviews_category_storefront_settings' => array(
		'storefront' => array('varchar', 255, 'null' => 0),
		'category_id' => array('int', 10, 'unsigned' => 1, 'null' => 0),
		'name' => array('varchar', 64, 'null' => 0),
		'value' => array('text'),
		':keys' => array(
			'PRIMARY' => array('storefront', 'category_id', 'name'),
			'storefront_category_id' => array('storefront', 'category_id'),
		),
	),
	'shop_catalogreviews_plugin_settings' => array(
		'name' => array('varchar', 64, 'null' => 0),
		'value' => array('text'),
		':keys' => array(
			'PRIMARY' => 'name',
		),
	),
	'shop_catalogreviews_plugin_storefront_settings' => array(
		'storefront' => array('varchar', 255, 'null' => 0),
		'name' => array('varchar', 64, 'null' => 0),
		'value' => array('text'),
		':keys' => array(
			'PRIMARY' => array('storefront', 'name'),
			'storefront' => 'storefront',
		),
	),
);

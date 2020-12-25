<?php
return array(
	'shop_seoredirect_errors' => array(
		'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
		'hash' => array('varchar', 32, 'null' => 0),
		'domain' => array('varchar', 255, 'null' => 0),
		'url' => array('varchar', 2083, 'null' => 0),
		'http_referer' => array('varchar', 2083, 'null' => 0),
		'create_datetime' => array('datetime', 'null' => 0),
		'edit_datetime' => array('datetime', 'null' => 0),
		'views' => array('int', 11, 'null' => 0),
		'code' => array('int', 11, 'null' => 0),
		':keys' => array(
			'PRIMARY' => 'id',
			'hash' => 'hash',
		),
	),
	'shop_seoredirect_errors_exclude' => array(
		'error_id' => array('int', 11, 'null' => 0),
		':keys' => array(
			'PRIMARY' => 'error_id',
		),
	),
	'shop_seoredirect_redirect' => array(
		'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
		'hash' => array('varchar', 32, 'null' => 0),
		'domain' => array('varchar', 255, 'null' => 0),
		'url_from' => array('varchar', 2083, 'null' => 0),
		'url_to' => array('varchar', 2083, 'null' => 0, 'default' => ''),
		'param' => array('int', 11, 'null' => 0, 'default' => '0'),
		'type' => array('int', 11, 'null' => 0, 'default' => '0'),
		'status' => array('int', 11, 'null' => 0),
		'sort' => array('int', 11, 'null' => 0, 'default' => '0'),
		'code_http' => array('int', 11, 'null' => 0),
		'create_datetime' => array('datetime', 'null' => 0),
		'edit_datetime' => array('datetime', 'null' => 0),
		'comment' => array('text', 'null' => 0),
		':keys' => array(
			'PRIMARY' => 'id',
			'hash' => 'hash',
		),
	),
	'shop_seoredirect_shop_url_type' => array(
		'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
		'domain' => array('varchar', 255, 'null' => 0),
		'route' => array('varchar', 64, 'null' => 0, 'default' => ''),
		'url_type' => array('int', 11, 'null' => 0),
		'edit_datetime' => array('datetime', 'null' => 0),
		':keys' => array(
			'PRIMARY' => 'id',
			'url_type' => array('domain', 'route', 'url_type', 'unique' => 1),
		),
	),
	'shop_seoredirect_shop_urls' => array(
		'hash' => array('varchar', 32, 'null' => 0),
		'id' => array('int', 11, 'null' => 0),
		'type' => array('int', 11, 'null' => 0),
		'url' => array('varchar', 2083, 'null' => 0),
		'full_url' => array('varchar', 2083, 'null' => 0),
		'parent_id' => array('int', 11, 'null' => 0),
		'create_datetime' => array('datetime', 'null' => 0),
		':keys' => array(
			'hash' => 'hash',
			'id_type' => array('id', 'type'),
			'type_parent_id' => array('type', 'parent_id'),
		),
	),
);

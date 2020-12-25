<?php

return array(
	'shop_lastmodified_hash' => array(
		'url' => array('varchar', 255, 'null' => 0),
		'hash' => array('varchar', 32, 'null' => 0),
		'date' => array('datetime', 'null' => 0),
		':keys' => array(
			'PRIMARY' => array('url'),
		),
	),
	'shop_lastmodified_settings' => array(
		'group' => array('varchar', 32, 'null' => 0),
		'name' => array('varchar', 32, 'null' => 0),
		'value' => array('text', 'null' => 1),
		':keys' => array(
			'PRIMARY' => array('group', 'name'),
		)
	),
);
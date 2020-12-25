<?php

return array(
	'name' => 'Last-Modified',
	'description' => 'Плагин для ускорения индексации интернет-магазина',
	'img' => 'img/lm.png',
	'vendor' => '934303',
	'frontend' => false,
	'shop_settings' => true,
	'version' => '1.5',
	'handlers' => array(
		'frontend_head' => 'handleFrontendHead',
	)
);
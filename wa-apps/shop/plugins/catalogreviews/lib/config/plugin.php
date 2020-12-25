<?php

return [
	'name' => 'Каталог отзывов',
	'description' => '',
	'version' => '2.10',
	'img' => 'img/icon16.png',
	'vendor' => '934303',
	'shop_settings' => true,
	'frontend' => true,
	'handlers' => [
		'routing' => 'getRoutingRules',
		'backend_menu' => 'handleBackendMenu',
		'frontend_nav' => 'handleFrontendNav',
		'frontend_category' => 'handleFrontendCategory',
	],
];

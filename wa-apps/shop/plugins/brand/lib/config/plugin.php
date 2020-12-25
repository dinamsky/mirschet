<?php

return array(
	'name' => 'Бренды PRO',
	'description' => 'Плагин для создания и гибкого управления брендами',
	'version' => '2.13',
	'img' => 'img/icon.png',
	'vendor' => '934303',
	'frontend' => true,
	'custom_settings' => true,
	'handlers' => array(
		'backend_menu' => 'handleBackendMenu',
		'frontend_nav' => 'handleFrontendNav',
		'products_collection' => 'handleProductsCollection',
		'sitemap' => 'handleSitemap',
		'rights.config' => 'handleRightsConfig',
	)
);

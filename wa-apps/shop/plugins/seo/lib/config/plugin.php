<?php

return array(
	'name' => 'SEO-оптимизация',
	'description' => 'Быстрая и гибкая оптимизация вашего магазина',
	'img' => 'img/seo3.png',
	'vendor' => '934303',
	'shop_settings' => true,
	'importexport' => true,
	'version' => '3.7',
	'handlers' => array(
		'*' => array(
			array(
				'event_app_id' => 'site',
				'event' => 'route_save.before',
				'class' => 'shopSeoPlugin',
				'method' => 'handleRouteSaveBefore',
			),
			array(
				'event_app_id' => 'site',
				'event' => 'route_save.after',
				'class' => 'shopSeoPlugin',
				'method' => 'handleRouteSaveAfter',
			),
		),
		'backend_category_dialog' => 'backendCategoryDialog',
		'category_save' => 'categorySave',
		'backend_product_edit' => 'backendProductEdit',
		'product_save' => 'productSave',
		'frontend_nav' => 'frontendNav', // Не совсем то что надо, но других вариантов нету.
		'frontend_search' => 'frontendSearch',
		'frontend_homepage' => 'frontendHomepage',
		'frontend_category' => 'frontendCategory',
		'frontend_product' => 'frontendProduct',
		'frontend_head' => 'frontendHead',
		'routing' => 'routing',
	),
);

<?php

return array(
	'name' => _w('Link Canonical'),
	'description' => _w('Generate automatic URL for the tag link rel="canonical"'),
	'img' => 'img/linkcanonical.png',
	'version' => '1.14',
	'vendor' => '934303',
	'shop_settings' => true,
	'handlers' => array(
		'frontend_head' => 'frontendHead',
		'backend_category_dialog' => 'backendCategoryDialog',
		'backend_product_edit' => 'backendProductEdit',
		'product_save' => 'productSave',
		'category_save' => 'categorySave',
		'shop_seofilter_frontend' => 'handleSeofilterFrontend',
	),
);

<?php

return array(
    'name' 				=> 'Этот товар уже в корзине',
    'img'  				=> 'img/incarts.png',
	'description'		=> 'Визуально выделяет товар, который лежит в корзине.',
	'version'			=> '1.31',
	'vendor'			=> 973724,
	'custom_settings' 	=> true,
	'handlers' => array(
		'frontend_head'		=> 'frontendHead',
	),
);
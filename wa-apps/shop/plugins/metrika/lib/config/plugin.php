<?php

return array(
    'name' => _w('Яндекс.Метрика'),
    'description' => _w('Вся статистика о вашем магазине'),
    'version' => '1.7',
	'shop_settings' => true,
	'vendor' => 898299,
    'frontend' => false,
    'img' => 'img/metrika.png',
    'icons' => array(
		16 => 'img/metrika.png',
    ), 
    'handlers' => array(
		'backend_reports' => 'backendmenu'
    ),
);


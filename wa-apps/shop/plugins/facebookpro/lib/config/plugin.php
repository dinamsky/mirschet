<?php

return array(
    'name'           => 'Facebookpro',
    'description'    => 'Экспорт товаров в каталог продуктов Facebook и Instagram',
//    'email'          => 'joker@tjo.biz',
    'img'            => 'img/facebookpro.png',
    'icon'           => array(
        16 => 'img/facebookpro.png',
        200 => 'img/facebook.png',
    ),
    'logo'           => 'img/facebook.png',
    'vendor'         => '1064599',
    'version'        => '0.0.8',
    'importexport'   => 'profiles',
    'export_profile' => true,
    'frontend'       => true,
    'handlers'       => array(
        'backend_products' => 'backendProductsEvent',
    ),
);
//EOF

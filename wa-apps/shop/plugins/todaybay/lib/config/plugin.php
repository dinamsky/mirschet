<?php

return array(
    'name'        => 'Сегодня купили',
    'description' => 'Вывод товаров, которые были приобретены сегодня',
    'img'        => 'img/todaybay.png',
    'vendor'      => '986052',
    'version'     => '1.3',    
    'shop_settings' => true,
    'handlers'    => array('frontend_nav' => 'useHook',),
);

//EOF

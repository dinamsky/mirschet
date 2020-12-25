<?php

return array(
    'name'        => 'DopInfo',
    'description' => 'Плагин выводит в бекэнд заказа дополнительную информацию',
    'img'         => 'img/dopinfo.png',
    'vendor'      => '986052',
    'version'     => '3.2',
    'shop_settings' => true,
    'handlers'    => array('backend_order' => 'getInfo',),
);

//EOF

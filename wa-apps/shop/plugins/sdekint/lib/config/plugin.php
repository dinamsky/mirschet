<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2015-2020
 */
return array(
    'name'          => 'Интеграция СДЭК',
    'img'           => 'img/icon16.png',
    'version'       => '4.4.2',
    'vendor'        => '670917',
    'shop_settings' => true,
    'handlers'      =>
        array(
            'backend_menu'      => 'handlerBackendMenu',
            'backend_order'     => 'handlerBackendOrder',
            'backend_orders'    => 'handlerBackendOrders',
//            'backend_settings'  => 'handlerBackendSettings',
            'frontend_my_order' => 'handlerFrontendMyOrder',
            'reset'             => 'handlerReset',
            'syrnik_shipping.*' => 'handlerSyrnikShipping',
        ),
    'frontend'      => true,
);

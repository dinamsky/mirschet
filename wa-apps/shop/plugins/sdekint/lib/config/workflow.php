<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @version 2.1.0
 * @copyright Serge Rodovnichenko, 2015-2016
 * @license http://www.webasyst.com/terms/#eula Webasyst
 */
return array(
    'actions' => array(
        'sdek-send'    =>
            array(
                'name'      => 'В СДЭК',
                'options'   =>
                    array(
                        'log_record'   => 'Оформлен заказ СДЭК',
                        'icon'         => 'ss shipping-bw',
                        'position'     => '',
                        'button_class' => '',
                        'border_color' => '1d780a',
                    ),
                'id'        => 'sdek-send',
                'classname' => 'shopSdekintPluginSdekSendAction',
            ),
        'sdek-dismiss' =>
            array(
                'name'      => 'Отозвать из СДЭК',
                'id'        => 'sdek-dismiss',
                'classname' => 'shopSdekintPluginSdekDismissAction',
                'options'   =>
                    array(
                        'log_record' => 'Заказ отзван из СДЭК',
                        'icon'       => 'cross',
                        'position'   => 'top',
                    ),
            )
    )
);

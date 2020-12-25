<?php

return array(
    'generate_product' => array(
        'value'        => 1,
        'title'        => 'Генерировать ЧПУ для товаров',
        'description'  => '<span style="color:#F44336;">* При генерации ЧПУ "Для новых и обновляемых товаров", в обновляемых товарах ЧПУ будет генерироваться только если текущий URL товара равен его ID.</span>',
        'options'      => array(
            array(
                'value' => 0,
                'title' => 'Не генерировать ЧПУ'
            ),
            array(
                'value' => 1,
                'title' => 'Только для новых товаров'
            ),
            array(
                'value' => 2,
                'title' => 'Для новых и обновляемых товаров'
            )
        ),
        'control_type' => waHtmlControl::SELECT
    ),
);
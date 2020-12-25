<?php

return array(

    'order_create' => array(
        'title' => 'Заказ создан',
        'description' => 'Количество дней, за которые учитывать заказы. Если товар есть в заказе, созданном не ранее этой даты, такой товар не попадёт в список невостребованных.',
        'value' => 60,
        'class' => 'short numeric',
        'control_type' => waHtmlControl::INPUT
    ),

    'product_create' => array(
        'title' => 'Товар создан',
        'description' => 'Количество дней от даты создания товара. Если товар создан позже, такой товар не попадёт в список невостребованных даже если с ним пока ещё нет ни одного заказа.',
        'value' => 60,
        'class' => 'short numeric',
        'control_type' => waHtmlControl::INPUT
    ),

    'product_status' => array(
        'title' => 'Статус товара',
        'description' => 'Отображать в списке только опубликованные товары',
        'value' => 1,
        'control_type' => waHtmlControl::CHECKBOX
    )
);

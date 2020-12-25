<?php
return array(
    'name' => 'Генерация ЧПУ в карточке товара',
    'description' => 'Ручная генерация ЧПУ в карточке товара',
    'img' => 'img/copychpu.png',
    'version' => '0.0.2',
    'vendor' => '1024551',

    'handlers' =>
        array(
            'backend_product' => 'backendProductEdit',
        ),
);

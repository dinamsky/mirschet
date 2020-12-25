<?php
return array(
    'shop_region' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'sort' => array('int', 5),
        'region_ru' => array('varchar', 255),
        'region_en' => array('varchar', 255),
        'subdomain' => array('varchar', 255),
        'domain' => array('varchar', 255),
        'header_ru' => array('text'),
        'header_en' => array('text'),
        ':keys' => array(
            'PRIMARY' => 'id',
        ),
    ),
);

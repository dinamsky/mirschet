<?php
return array(
    'shop_vkshop_albums' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'name' => array('varchar', 100),
        'type' => array('varchar', 10, 'null' => 0),
        'shop_id' => array('varchar', 64),
        'album_id' => array('varchar', 64, 'null' => 0),
        'group_id' => array('int', 11),
        ':keys' => array(
            'PRIMARY' => 'id',
        ),
    ),
    'shop_vkshop_category' => array(
        'category_id' => array('int', 11, 'null' => 0),
        'group_id' => array('int', 11, 'null' => 0),
        'album_id' => array('int', 11, 'null' => 0),
        ':keys' => array(
            'PRIMARY' => array('category_id', 'group_id'),
        ),
    ),
    'shop_vkshop_images' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'product_id' => array('int', 11, 'null' => 0),
        'vk_photo_id' => array('varchar', 64, 'null' => 0),
        'group_id' => array('int', 11),
        'type' => array('varchar', 10),
        ':keys' => array(
            'PRIMARY' => 'id',
        ),
    ),
    'shop_vkshop_products' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'product_id' => array('int', 11, 'null' => 0),
        'group_id' => array('int', 11),
        'item_id' => array('varchar', 64, 'null' => 0),
        'datetime' => array('datetime'),
        'status' => array('tinyint', 1),
        ':keys' => array(
            'PRIMARY' => 'id',
        ),
    ),
    'shop_vkshop_products_albums' => array(
        'product_id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'group_id' => array('int', 11, 'null' => 0),
        'album_id' => array('int', 11, 'null' => 0),
        ':keys' => array(
            'PRIMARY' => array('product_id', 'group_id', 'album_id'),
        ),
    ),
    'shop_vkshop_products_cron' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'product_id' => array('int', 11, 'null' => 0),
        'group_id' => array('int', 11),
        'action' => array('varchar', 10),
        ':keys' => array(
            'PRIMARY' => 'id',
        ),
    ),
    'shop_vkshop_products_disabled' => array(
        'product_id' => array('int', 11, 'null' => 0),
        ':keys' => array(
            'PRIMARY' => 'product_id',
        ),
    ),
    'shop_vkshop_products_queue' => array(
        'product_id' => array('int', 11, 'null' => 0),
        'action' => array('varchar', 10),
        ':keys' => array(
            'PRIMARY' => 'product_id',
        ),
    ),
    'shop_vkshop_products_temp_queue' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'product_id' => array('int', 11, 'null' => 0),
        'group_id' => array('int', 11),
        'action' => array('varchar', 10),
        'count' => array('tinyint', 1, 'null' => 0, 'default' => '0'),
        ':keys' => array(
            'PRIMARY' => 'id',
        ),
    ),
    'shop_vkshop_group' => array(
        'id' => array('int', 11, 'null' => 0),
        'group_name' => array('varchar', 100, 'null' => 0, 'default' => ''),
        'settlement' => array('varchar', 250),
        'app_id' => array('int', 11),
        'app_secret' => array('varchar', 50),
        'user_id' => array('int', 11),
        'first_name' => array('varchar', 50),
        'last_name' => array('varchar', 50),
        'photo_50' => array('varchar', 500),
        'access_token' => array('varchar', 250),
        'secret' => array('varchar', 50),
        'token_datetime' => array('datetime'),
        ':keys' => array(
            'PRIMARY' => 'id',
        ),
    ),
);

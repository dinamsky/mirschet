<?php
return array(
    'shop_vkgoods_product' => array(
        'id' => array('int', 32, 'null' => 0, 'autoincrement' => 1),
        'pid' => array('int', 32, 'null' => 0),
        'gid' => array('int', 32, 'null' => 0),
        'aid' => array('varchar', 512),
        'vkcat_id' => array('int', 32, 'null' => 0),
        'vk_pid' => array('int', 64, 'null' => 0),
        'main_photo_id' => array('int', 32, 'null' => 0),
        'photo_ids' => array('text'),
        'date' => array('datetime', 'null' => 0),
        'storefront' => array('varchar', 256, 'null' => 0),
        ':keys' => array(
            'PRIMARY' => 'id',
        ),
    ),
    'shop_vkgoods_wait_category' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'cid' => array('int', 32, 'null' => 0),
        'subcategories' => array('int', 1, 'null' => 0, 'default' => '0'),
        'gid' => array('int', 32, 'null' => 0),
        'storefront' => array('varchar', 256, 'null' => 0),
        'aid' => array('int', 32, 'null' => 0),
        'category_id' => array('int', 32, 'null' => 0),
        'desc' => array('text', 'null' => 0),
        'all_photo' => array('int', 1, 'null' => 0),
        'f_price' => array('int', 1, 'null' => 0),
        ':keys' => array(
            'PRIMARY' => 'id',
        ),
    ),
    'shop_vkgoods_wait_product' => array(
        'pid' => array('int', 32, 'null' => 0),
        'gid' => array('int', 32, 'null' => 0),
        'storefront' => array('varchar', 256, 'null' => 0),
        'aid' => array('int', 32, 'null' => 0),
        'category_id' => array('int', 32, 'null' => 0),
        'desc' => array('text', 'null' => 0),
        'all_photo' => array('int', 1, 'null' => 0),
        'f_price' => array('int', 1, 'null' => 0),
        ':keys' => array(
            'pid' => array('pid', 'gid', 'unique' => 1),
        ),
    ),
);

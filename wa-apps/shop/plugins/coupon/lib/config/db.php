<?php
return array(
    'shop_coupon_plugin_template' => array(
        'id' => array('int', 10, 'unsigned' => 1, 'null' => 0, 'autoincrement' => 1),
        'type' => array('varchar', 3, 'null' => 0),
        'prefix' => array('varchar', 255, 'null' => 0),
        'limit' => array('int', 11, 'default' => 1),
        'value' => array('decimal', "15,4"),
        'comment' => array('text'),
        'expire_hours' => array('smallint', 5, 'unsigned' => 1, 'default' => 24),
        'num' => array('tinyint', 1, 'null' => 0, 'default' => '1'),
        'length' => array('tinyint', 3, 'unsigned' => 1, 'null' => 0, 'default' => 3),
        'latin_lowercase' => array('tinyint', 1, 'null' => 0, 'default' => 0),
        'latin_uppercase' => array('tinyint', 1, 'null' => 0, 'default' => 0),
        'cyr_lowercase' => array('tinyint', 1, 'null' => 0, 'default' => 0),
        'cyr_uppercase' => array('tinyint', 1, 'null' => 0, 'default' => 0),
        'other' => array('tinyint', 1, 'null' => 0, 'default' => 0),
        'create_contact_id' => array('int', 10, 'unsigned' => 1, 'null' => 0),
        ':keys' => array(
            'PRIMARY' => 'id',
        ),
    ),
);

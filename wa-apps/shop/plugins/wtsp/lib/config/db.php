<?php
return array(
    'shop_wtsp' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'contact_id' => array('int', 11),
        'name' => array('text', 'null' => 0),
        'description' => array('text', 'null' => 0),
        'sort' => array('int', 4, 'null' => 0, 'default' => '0'),
        ':keys' => array(
            'PRIMARY' => 'id',
        ),
    ),
);

<?php
return array(
    'shop_linkcanonical_category_canonical' => array(
        'hash' => array('varchar', 32, 'null' => 0),
        'category_id' => array('int', 11, 'null' => 0),
        'storefront' => array('varchar', 2048, 'null' => 0),
        'canonical' => array('varchar', 2048, 'null' => 0),
        ':keys' => array(
            'hash' => 'hash',
        ),
    ),
    'shop_linkcanonical_product_canonical' => array(
        'hash' => array('varchar', 32, 'null' => 0),
        'product_id' => array('int', 11, 'null' => 0),
        'storefront' => array('varchar', 2048, 'null' => 0),
        'canonical' => array('varchar', 2048, 'null' => 0),
        ':keys' => array(
            'hash' => 'hash',
        ),
    ),
);

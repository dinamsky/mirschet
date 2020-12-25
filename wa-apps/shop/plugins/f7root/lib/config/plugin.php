<?php

return array(
    'name' => _wp('Items without category, without pictures, hidden, with a certain characteristic, and also other filters of items'),
    'description' => _wp('fast search of items without category, without pictures, hidden, with a certain characteristic, and also other filters of items'),
    'vendor' => 917874,
    'version' => '3.3',
    'img' => 'img/f7logo16.png',
    'handlers' => array(
        'products_collection' => 'f7rootproductsCollection',
        'backend_products' => 'f7backendProducts',
    ),
);


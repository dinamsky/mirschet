<?php
return array(
    'name' => _wp('VK Shop'),
    'description' => _wp("Allows you to send the products to the store's Vkontakte."),
    'icon' => 'img/vkshop16.png',
    'img' => 'img/vkshop16.png',
    'version' => '4.3.3',
    'vendor' => '964801',
    'importexport' => 1,//'profiles',
    'import_profile' => false,
    'shop_settings' => true,
    'handlers' =>
        array(
            'backend_product'               => 'backendProduct',
            'backend_products'              => 'backendProducts',
            'product_delete'                => 'productDelete',
            'product_save'                  => 'productSave',
            'backend_category_dialog'       => 'backendCategoryDialog',
            'category_save'                 => 'categorySave',
            'category_delete'               => 'categoryDelete',
        ),
);

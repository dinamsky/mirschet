<?php
return array(
    'name' => 'ВКонтакте: Товары',
    'description' => 'Экспорт и актуализация товаров в сервис ВК "Товары"',
    'img' => 'img/vkgoods.png',
    'version' => '4.3.0',
    'vendor' => '834834',
    'importexport' => true,
    'handlers' => array(
        'backend_product' => 'handlerBackendProduct',
        'backend_products' => 'handlerBackendProducts',
        'backend_category_dialog' => 'handlerBackendCategoryDialog',
        'products_collection' => 'handlerProductsCollection',
        'category_save' => 'handlerCategorySave',
        'category_delete' => 'handlerCategoryDelete',
    ),
);
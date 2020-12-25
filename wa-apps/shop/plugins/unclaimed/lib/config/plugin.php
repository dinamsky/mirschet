<?php
return array (
  'name' => 'Невостребованные товары',
  'description' => 'В разделе «Товары» выводит список невостребованных товаров',
  'img' => 'img/unclaimed.png',
  'version' => '1.0.1',
  'vendor' => '972539',
  'handlers' => 
  array (
      'backend_products' => 'backendProducts',
      'products_collection' => 'productsCollection',
  ),
);

<?php
// Удаление всего, что связано с вызовом курьера и списком вызовов. т.к. все это перенесено на отдельную вкладку
$plugin_dir = wa('shop')->getConfig()->getPluginPath('sdekint');
waFiles::delete($plugin_dir . '/lib/actions/courier');
waFiles::delete($plugin_dir . '/templates/actions/courier');
waFiles::delete($plugin_dir . '/js/sdekint_backend_orders.js');
waFiles::delete($plugin_dir . '/js/sdekint_backend_orders.min.js');
waFiles::delete($plugin_dir . '/js/sdekint_backend_order.js');
waFiles::delete($plugin_dir . '/js/sdekint_backend_order.min.js');

// иконки объединяем в отдельный файл.
waFiles::delete($plugin_dir . '/img/doc_pdf.png');

// CSS объединяем в единый файл
waFiles::delete($plugin_dir . '/css/sdekint_backend_order_info.css');
waFiles::delete($plugin_dir . '/css/sdekint_backend_order_info.scss');

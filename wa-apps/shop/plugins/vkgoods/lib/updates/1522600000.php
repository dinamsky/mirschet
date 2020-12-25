<?php
$plugin_path = wa('shop')->getConfig()->getPluginPath('vkgoods') . '/lib/actions/';
$file_to_del = array(
    'shopVkgoodsPluginBackendWaitproduct.controller.php',
    'shopVkgoodsPluginBackendGetvkdata.controller.php',
);
foreach ($file_to_del as $file) {
    try {
        waFiles::delete($plugin_path . $file);
    } catch (Exception $e) {
        waLog::log("Ошибка при удалении файлов в скрипте обновления " . __FILE__ . ": " . $e->getMessage(), 'vkgoods.error.log');
    }
};

$model = new waModel();
$model->exec('CREATE TABLE IF NOT EXISTS `shop_vkgoods_wait_category` (
  `cid` int(32) NOT NULL,
  `subcategories` int(1) NOT NULL DEFAULT \'0\',
  `gid` int(32) NOT NULL,
  `storefront` varchar(256) NOT NULL,
  `aid` int(32) NOT NULL,
  `category_id` int(32) NOT NULL,
  `desc` text NOT NULL,
  `all_photo` int(1) NOT NULL,
  `f_price` int(1) NOT NULL,
  PRIMARY KEY (`cid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8');
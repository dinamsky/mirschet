<?php

$model = new waModel();

try {
    $model->exec("SELECT storefront FROM `shop_vkgoods_product` WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE `shop_vkgoods_product` ADD storefront VARCHAR (256) NOT NULL DEFAULT '' AFTER date");
}

	$model->exec('CREATE TABLE IF NOT EXISTS `shop_vkgoods_wait_product` (
  `pid` int(32) NOT NULL,
  `gid` int(32) NOT NULL,
  `storefront` varchar(256) NOT NULL,
  `aid` int(32) NOT NULL,
  `category_id` int(32) NOT NULL,
  `desc` text NOT NULL,
  `all_photo` int(1) NOT NULL,
  `f_price` int(1) NOT NULL,
  UNIQUE KEY `pid` (`pid`,`gid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8');

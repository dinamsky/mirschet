<?php

$model = new waModel();

$index_exists = count($model->query('SHOW INDEX FROM `shop_linkcanonical_product_canonical` WHERE Key_name=\'product_id\'')->fetchAll()) > 0;
if (!$index_exists)
{
	$sql = '
ALTER TABLE `shop_linkcanonical_product_canonical`
	ADD INDEX `product_id` (`product_id`);
';

	$model->exec($sql);
}

$index_exists = count($model->query('SHOW INDEX FROM `shop_linkcanonical_category_canonical` WHERE Key_name=\'category_id\'')->fetchAll()) > 0;
if (!$index_exists)
{
	$sql = '
ALTER TABLE `shop_linkcanonical_category_canonical`
	ADD INDEX `category_id` (`category_id`);
';

	$model->exec($sql);
}
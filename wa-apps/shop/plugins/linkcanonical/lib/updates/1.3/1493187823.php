<?php

$model = new waModel();

$sql1 = "
CREATE TABLE IF NOT EXISTS `shop_linkcanonical_category_canonical` (
	`hash` VARCHAR(32) NOT NULL,
	`category_id` INT(11) NOT NULL,
	`storefront` VARCHAR(2048) NOT NULL,
	`canonical` VARCHAR(2048) NOT NULL,
	INDEX `hash` (`hash`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
";
$sql2 = "
CREATE TABLE IF NOT EXISTS `shop_linkcanonical_product_canonical` (
	`hash` VARCHAR(32) NOT NULL,
	`product_id` INT(11) NOT NULL,
	`storefront` VARCHAR(2048) NOT NULL,
	`canonical` VARCHAR(2048) NOT NULL,
	INDEX `hash` (`hash`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
";

try
{
	$model->query($sql1);
	$model->query($sql2);
}
catch (waDbException $e)
{

}

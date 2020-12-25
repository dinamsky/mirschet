<?php
/**
 * Created by PhpStorm.
 * User: snark | itfrogs.ru
 * Date: 11/18/17
 * Time: 9:10 PM
 */

try {
    $model = new waModel();
    $model->exec('DELETE FROM `shop_vkshop_products_queue` WHERE 1');
    $model->exec('DELETE FROM `shop_vkshop_products_temp_queue` WHERE 1');
    $model->exec('ALTER TABLE `shop_vkshop_products_queue` ADD COLUMN `action` VARCHAR(10) NULL DEFAULT NULL AFTER `product_id`');
    $model->exec('ALTER TABLE `shop_vkshop_products_temp_queue` 	ADD COLUMN `action` VARCHAR(10) NULL DEFAULT NULL AFTER `group_id`');
    $model->exec('ALTER TABLE `shop_vkshop_products_temp_queue` 	ADD COLUMN `id` INT(11) NOT NULL AUTO_INCREMENT FIRST, DROP PRIMARY KEY, ADD PRIMARY KEY (`id`)');
}
catch (waException $e) {

}
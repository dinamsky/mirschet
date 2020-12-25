<?php
/**
 * Created by PhpStorm.
 * User: snark | itfrogs.ru
 * Date: 4/22/17
 * Time: 9:10 PM
 */

$model = new waModel();
try {
    $model->exec('ALTER TABLE `shop_vkshop_albums` ADD COLUMN `group_id` INT(11) NULL AFTER `album_id`');
    $model->exec('ALTER TABLE `shop_vkshop_images` ADD COLUMN `group_id` INT(11) NULL AFTER `vk_photo_id`');
    $model->exec('ALTER TABLE `shop_vkshop_products` ADD COLUMN `group_id` INT(11) NULL AFTER `product_id`');
    $model->exec('ALTER TABLE `shop_vkshop_products_temp_queue` ADD COLUMN `group_id` INT(11) NULL AFTER `product_id`');
} catch (waException $e) {

}
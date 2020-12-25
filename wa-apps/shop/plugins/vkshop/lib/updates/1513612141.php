<?php
/**
 * Created by PhpStorm.
 * User: snark | itfrogs.ru
 * Date: 12/18/17
 * Time: 6:49 PM
 */

try {
    $model = new waModel();
    $model->exec(
        'CREATE TABLE `shop_vkshop_products_cron` ( '
        . '`id` INT(11) NOT NULL AUTO_INCREMENT, '
        . '`product_id` INT(11) NOT NULL, '
        . '`group_id` INT(11) NULL DEFAULT NULL, '
        . '`action` VARCHAR(10) NULL DEFAULT NULL, '
        . 'PRIMARY KEY (`id`)) '
    );
}
catch (waException $e) {

}

<?php
/**
 * Created by PhpStorm.
 * User: snark | itfrogs.ru
 * Date: 11/13/18
 * Time: 11:29 AM
 */

try {
    $model = new shopVkshopPluginProductsModel();
    if (!$model->fieldExists('datetime')) {
        $model->exec(
            'ALTER TABLE `shop_vkshop_products`	ADD COLUMN `datetime` DATETIME NULL DEFAULT NULL AFTER `item_id`'
        );
    }

    if (!$model->fieldExists('status')) {
        $model->exec(
            'ALTER TABLE `shop_vkshop_products`	ADD COLUMN `status` TINYINT(1) NULL DEFAULT NULL AFTER `datetime`'
        );
    }
}
catch (waException $e) {

}
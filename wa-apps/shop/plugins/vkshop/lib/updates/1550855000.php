<?php
/**
 * Created by PhpStorm.
 * User: snark | itfrogs.ru
 * Date: 2/22/19
 * Time: 8:03 PM
 */

try {
    $model = new shopVkshopPluginProductsModel();

    if (!$model->fieldExists('status')) {
        $model->exec(
            'ALTER TABLE `shop_vkshop_products`	ADD COLUMN `status` TINYINT(1) NULL DEFAULT NULL AFTER `datetime`'
        );
    }
}
catch (waException $e) {

}
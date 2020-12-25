<?php
/**
 * Created by PhpStorm.
 * User: snark | itfrogs.ru
 * Date: 3/11/18
 * Time: 5:35 PM
 */

try {
    $model = new shopVkshopPluginImagesModel();
    if ($model->fieldExists('pid')) {
        $model->exec(
            'ALTER TABLE `shop_vkshop_images` DROP COLUMN `pid`'
        );
    }
}
catch (waException $e) {

}
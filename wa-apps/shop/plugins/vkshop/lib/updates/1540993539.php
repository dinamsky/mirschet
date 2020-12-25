<?php
/**
 * Created by PhpStorm.
 * User: snark | itfrogs.ru
 * Date: 10/31/18
 * Time: 4:45 PM
 */

try {
    $model = new waModel();
    $model->exec(
        'CREATE TABLE IF NOT EXISTS `shop_vkshop_products_albums` ( '
        . '`product_id` INT(11) NOT NULL AUTO_INCREMENT, '
        . '`album_id` INT(11) NOT NULL, '
        . '`group_id` INT(11) NOT NULL, '
        . 'PRIMARY KEY (`product_id`, `album_id`, `group_id`) '
        . ')  '
    );
}
catch (waException $e) {

}
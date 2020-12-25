<?php
/**
 * Created by PhpStorm.
 * User: snark | itfrogs.ru
 * Date: 11/15/18
 * Time: 2:55 PM
 */

try {
    $model = new waModel();
    $model->exec(
        'CREATE TABLE IF NOT EXISTS `shop_vkshop_category` ( '
        . '`category_id` INT(11) NOT NULL, '
        . '`group_id` INT(11) NOT NULL, '
        . '`album_id` INT(11) NOT NULL, '
        . 'PRIMARY KEY (`category_id`, `group_id`, `album_id`) '
        . ')  '
    );
}
catch (waException $e) {

}
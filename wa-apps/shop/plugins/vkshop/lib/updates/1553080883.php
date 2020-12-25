<?php
/**
 * Created by PhpStorm.
 * User: snark | itfrogs.ru
 * Date: 3/20/19
 * Time: 2:21 PM
 */

try {
    $model = new waModel();
    $model->exec(
        'CREATE TABLE IF NOT EXISTS `shop_vkshop_group` ( '
        . '`id` INT(11) NOT NULL, '
        . '`group_name` VARCHAR(100) NOT NULL DEFAULT "", '
        . '`settlement` VARCHAR(250) NULL DEFAULT NULL, '
        . '`app_id` INT(11) NULL DEFAULT NULL, '
        . '`app_secret` VARCHAR(50) NULL DEFAULT NULL, '
        . '`user_id` INT(11) NULL DEFAULT NULL, '
        . '`first_name` VARCHAR(50) NULL DEFAULT NULL, '
        . '`last_name` VARCHAR(50) NULL DEFAULT NULL, '
        . '`photo_50` VARCHAR(500) NULL DEFAULT NULL, '
        . '`access_token` VARCHAR(250) NULL DEFAULT NULL, '
        . '`secret` VARCHAR(50) NULL DEFAULT NULL, '
        . '`token_datetime` DATETIME NULL DEFAULT NULL, '
        . 'PRIMARY KEY (`id`) '
        . ') '
    );

    $asm = new waAppSettingsModel();
    $groups = $asm->get(array('shop', 'vkshop'), 'groups');
    $app_id = $asm->get(array('shop', 'vkshop'), 'app_id');
    $app_secret = $asm->get(array('shop', 'vkshop'), 'app_secret');
    if (!empty($groups)) {
        $groups = json_decode($groups);
        $group_model = new shopVkshopPluginGroupModel();
        foreach ($groups as $group) {
            $data = array(
                'id'            => $group->id,
                'group_name'    => $group->name,
                'app_id'        => $app_id,
                'app_secret'    => $app_secret,
            );

            $group_model->insert($data, 1);
        }
    }
}
catch (waException $e) {

}

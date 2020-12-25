<?php
/**
 * Created by PhpStorm.
 * User: snark | itfrogs.ru
 * Date: 4/22/17
 * Time: 9:23 PM
 */

try {
    $asm = new waAppSettingsModel();
    $groups = json_decode($asm->get(array('shop', 'vkshop'), 'groups'));

    if (!empty($groups)) {
        $group = reset($groups);

        $model = new waModel();
        $model->exec('UPDATE shop_vkshop_albums SET group_id = i:group_id WHERE group_id IS NULL', array('group_id' => $group->id));
        $model->exec('UPDATE shop_vkshop_images SET group_id = i:group_id WHERE group_id IS NULL', array('group_id' => $group->id));
        $model->exec('UPDATE shop_vkshop_products SET group_id = i:group_id WHERE group_id IS NULL', array('group_id' => $group->id));
        $model->exec('UPDATE shop_vkshop_products_temp_queue SET group_id = i:group_id WHERE group_id IS NULL', array('group_id' => $group->id));
    }
}
catch (waException $e) {

}
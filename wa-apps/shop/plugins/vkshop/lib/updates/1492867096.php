<?php
/**
 * Created by PhpStorm.
 * User: snark | itfrogs.ru
 * Date: 4/22/17
 * Time: 4:18 PM
 */

try {
    $asm = new waAppSettingsModel();
    $group_id = $asm->get(array('shop', 'vkshop'), 'group_id');

    if (!empty($group_id)) {
        $asm->set(array('shop', 'vkshop'), 'groups', json_encode(array(0 => array('id' => $group_id, 'name' => $group_id))));
    }
}
catch (waException $e) {

}
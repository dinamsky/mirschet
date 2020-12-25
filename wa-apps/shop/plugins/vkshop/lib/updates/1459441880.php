<?php
/**
 * Created by PhpStorm.
 * User: snark | itfrogs.ru
 * Date: 3/31/16
 * Time: 7:30 PM
 */

$model = new waModel();
try {
    $model->exec('ALTER TABLE `shop_vkshop_albums` ADD COLUMN `name` VARCHAR(100) NULL AFTER `id`');
} catch (waException $e) {

}


try {
    $albums = $model->query('SELECT * FROM shop_vkshop_albums')->fetchAll();
    foreach ($albums as $album) {
        if ($album['type'] == 'main') {
            $album['name'] = _wp('Main album');
            $model->exec('UPDATE shop_vkshop_albums SET name = "' . $model->escape($album['name']) . '" WHERE id = ' . intval($album['id']));
        } elseif ($album['type'] == 'set') {
            $set = $model->query('SELECT * FROM shop_set WHERE id = "' . intval($album['shop_id']) . '"')->fetchAssoc();
            $album['name'] = $set['name'];
            $model->exec('UPDATE shop_vkshop_albums SET name = "' . $model->escape($album['name']) . '" WHERE id = ' . intval($album['id']));
        } elseif ($album['type'] == 'type') {
            $type = $model->query('SELECT * FROM shop_type WHERE id = ' . intval($album['shop_id']))->fetchAssoc();
            $album['name'] = $type['name'];
            $model->exec('UPDATE shop_vkshop_albums SET name = "' . $model->escape($album['name']) . '" WHERE id = ' . intval($album['id']));
        }
    }
} catch (waException $e) {
}
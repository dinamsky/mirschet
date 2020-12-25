<?php
/**
 * Database update 1.0.0 -> 1.1.0
 *
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @version 1.1.0
 * @copyright Serge Rodovnichenko, 2016
 */
$model = new waModel();

try {
    $model->query('SELECT owner FROM shop_sdekint_pvz WHERE 1 LIMIT 1');
} catch (waDbException $e) {
    $model->exec('ALTER TABLE `shop_sdekint_pvz` ADD `owner` VARCHAR(64) DEFAULT NULL');
    $model->exec('ALTER TABLE `shop_sdekint_pvz` ADD INDEX(`owner`)');
}

try {
    $model->query('SELECT `point_type` FROM shop_sdekint_pvz WHERE 1 LIMIT 1');
} catch (waDbException $e) {
    $model->exec('ALTER TABLE `shop_sdekint_pvz` ADD `point_type` VARCHAR(32) DEFAULT NULL');
    $model->exec('ALTER TABLE `shop_sdekint_pvz` ADD INDEX(`point_type`)');
}

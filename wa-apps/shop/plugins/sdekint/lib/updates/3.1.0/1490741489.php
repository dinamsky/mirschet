<?php

$model = new waModel();
try {
    $r = $model->query('SELECT `order_no` FROM `shop_sdekint_courier_calls` WHERE 1 LIMIT 1');
} catch (waDbException $e) {
    try {
        $model->exec('ALTER TABLE `shop_sdekint_courier_calls` ADD `order_no` VARCHAR(60) DEFAULT NULL NULL');
        $model->exec('CREATE INDEX `order_no_index` ON `shop_sdekint_courier_calls` (order_no)');
    } catch (waException $e) {

    }
}

try {
    $r = $model->query('SELECT `number_ttn` FROM `shop_sdekint_courier_calls` WHERE 1 LIMIT 1');
} catch (waDbException $e) {
    try {
        $model->exec('ALTER TABLE `shop_sdekint_courier_calls` ADD `number_ttn` VARCHAR(60) DEFAULT NULL NULL');
        $model->exec('CREATE INDEX `ttn_index` ON `shop_sdekint_courier_calls` (number_ttn)');
    } catch (waException $e) {

    }
}

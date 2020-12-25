<?php
/**
 * @since 3.0.0 Изменения в таблице с вызовами курьера
 */
// ALTER TABLE shop_sdekint_courier_calls MODIFY COLUMN code VARCHAR(50) NULL;
// ALTER TABLE shop_sdekint_courier_calls MODIFY COLUMN weight INT NULL;
// ALTER TABLE shop_sdekint_courier_calls ADD dispatch_number VARCHAR(50) NULL;
$model = new waModel();
try {
    $model->exec('ALTER TABLE `shop_sdekint_courier_calls` MODIFY COLUMN `code` VARCHAR(50) NULL');
} catch (waDbException $e) {

}

try {
    $model->exec('ALTER TABLE `shop_sdekint_courier_calls` MODIFY COLUMN `weight` INT NULL');
} catch (waDbException $e) {

}

try {
    $r = $model->query('SELECT `dispatch_number` FROM `shop_sdekint_courier_calls` WHERE 1 LIMIT 1');
} catch (waDbException $e) {
    $model->exec('ALTER TABLE `shop_sdekint_courier_calls` ADD `dispatch_number` VARCHAR(50) NULL');
}

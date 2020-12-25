<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @version 2.5.1
 * @copyright Serge Rodovnichenko, 2017
 *
 * Hotfix issue #64: shop_sdekint_cities.region_code column MUST be nullable
 */
$model = new waModel();

try {

    $model->exec('ALTER TABLE `shop_sdekint_cities` MODIFY COLUMN `region_code` VARCHAR(8) NULL;');

} catch (waException $e) {

}
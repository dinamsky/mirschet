<?php
/**
 * Db update 0.0.2 -> 0.0.3
 *
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @version 0.0.3
 * @copyright Serge Rodovnichenko, 2015
 */

$model = new waModel();

try {
    $model->query('SELECT * FROM shop_sdekint_pvz WHERE 0');
} catch (waDbException $e) {
    $sql = 'CREATE TABLE IF NOT EXISTS `shop_sdekint_pvz` (
  `code` VARCHAR(10) NOT NULL,
  `name` VARCHAR(50) NOT NULL,
  `city_code` INT(11) NOT NULL,
  `city` VARCHAR(50) NOT NULL,
  `work_time` VARCHAR(100) NOT NULL,
  `address` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(100) NOT NULL,
  `note` VARCHAR(255) NOT NULL,
  `coord_x` DECIMAL(10,6) NOT NULL,
  `coord_y` DECIMAL(10,6) NOT NULL,
  `min_weight` INT(11) DEFAULT NULL,
  `max_weight` INT(11) DEFAULT NULL
) ENGINE=MyISAM CHARSET=utf8;';
    $model->exec($sql);
}
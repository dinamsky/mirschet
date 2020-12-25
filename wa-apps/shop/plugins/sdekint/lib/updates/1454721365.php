<?php
/**
 * Db update 1.0.0 -> 2.0.0
 *
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @version 2.0.0
 * @copyright Serge Rodovnichenko, 2015
 */

$model = new waModel();

try {
    $model->query('SELECT * FROM shop_sdekint_courier_calls WHERE 0');
} catch (waDbException $e) {
    $sql = 'CREATE TABLE IF NOT EXISTS `shop_sdekint_courier_calls` (
  `id` INT(11) NOT NULL,
  `code` VARCHAR(50) NOT NULL,
  `call_date` DATE NOT NULL,
  `time_beg` TIME NOT NULL,
  `time_end` TIME NOT NULL,
  `send_phone` VARCHAR(60) NOT NULL,
  `send_name` VARCHAR(255) NOT NULL,
  `weight` INT(11) NOT NULL,
  `comment` VARCHAR(255) NOT NULL,
  `city_code` INT(11) NOT NULL,
  `address_street` VARCHAR(50) NOT NULL,
  `address_house` VARCHAR(30) NOT NULL,
  `address_flat` VARCHAR(10) NOT NULL,
  `sent` DATETIME NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;';
    $model->exec($sql);

    $sql = 'ALTER TABLE `shop_sdekint_courier_calls`
  ADD PRIMARY KEY (`id`),
  ADD KEY `code_index` (`code`),
  ADD KEY `call_date_index` (`call_date`),
  ADD KEY `time_beg_index` (`time_beg`);';
    $model->exec($sql);
    $sql = 'ALTER TABLE `shop_sdekint_courier_calls`
  MODIFY `id` INT(11) NOT NULL AUTO_INCREMENT;';
    $model->exec($sql);

}
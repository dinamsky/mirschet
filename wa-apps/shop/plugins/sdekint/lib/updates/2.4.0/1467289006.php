<?php
/**
 * Db update. New table with order states processing rules
 *
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @version 2.4.0
 * @copyright Serge Rodovnichenko, 2016
 */

$model = new waModel();

try {
    $model->query('SELECT * FROM shop_sdekint_order_actions WHERE 0');
} catch (waDbException $e) {
    $sql = 'CREATE TABLE IF NOT EXISTS `shop_sdekint_order_actions` (
         id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
         shop_state VARCHAR(255) NOT NULL,
         sdek_state INT NOT NULL,
         wf_action VARCHAR(255) NOT NULL,
         UNIQUE INDEX shop_sdek (shop_state, sdek_state)
) ENGINE=MyISAM CHARSET=utf8;';
    $model->exec($sql);
}
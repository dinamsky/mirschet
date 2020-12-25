<?php
$model = new waModel();

try {
    $model->query('SELECT phrase_ru FROM shop_region');
} catch (waDbException $e) {
    $sql = 'ALTER TABLE `shop_region` CHANGE COLUMN `header` `header_ru` TEXT NOT NULL AFTER `domain`, ADD COLUMN `header_en` TEXT NOT NULL AFTER `header_ru`, CHANGE COLUMN `contacts` `contacts_ru` TEXT NOT NULL AFTER `header_en`, ADD COLUMN `contacts_en` TEXT NOT NULL AFTER `contacts_ru`, CHANGE COLUMN `delivery` `delivery_ru` TEXT NOT NULL AFTER `contacts_en`, ADD COLUMN `delivery_en` TEXT NOT NULL AFTER `delivery_ru`, ADD COLUMN `phrase_ru` TEXT NOT NULL AFTER `delivery_en`, ADD COLUMN `phrase_en` TEXT NOT NULL AFTER `phrase_ru`;';
    $model->exec($sql);
}
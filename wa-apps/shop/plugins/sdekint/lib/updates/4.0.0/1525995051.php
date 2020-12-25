<?php

$m = new waModel();

try {
    $m->query('SELECT * FROM shop_sdekint_widget_settings WHERE 1=1 LIMIT 1');
} catch (waDbException $e) {
    try {
        $m->exec(
            'CREATE TABLE `shop_sdekint_widget_settings` ' .
            '(' .
            '`id` int PRIMARY KEY NOT NULL AUTO_INCREMENT, ' .
            '`name` VARCHAR(150), ' .
            '`type` VARCHAR(30) NOT NULL, '.
            '`settings` TEXT NOT NULL ' .
            ')'
        );
    } catch (Exception $e) {
        waLog::log('Ошибка при создании таблицы в файле обновления ' . __FILE__ . ': ' . $e->getMessage());
    }

}

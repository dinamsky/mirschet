<?php
$m = new waModel();

try {
    $m->query('SELECT * FROM shop_sdekint_calc_rules WHERE 1=1 LIMIT 1');
} catch (waDbException $e) {
    try {
        $m->exec(
            'CREATE TABLE `shop_sdekint_calc_rules` ' .
            '(' .
            '`id` int PRIMARY KEY NOT NULL AUTO_INCREMENT, ' .
            '`sort` int DEFAULT 0 NOT NULL, ' .
            '`status` int(1) DEFAULT 1 NOT NULL, '.
            '`name` varchar(255) DEFAULT \'\' NOT NULL, ' .
            '`methods` text, '.
            '`courier` text, '.
            '`point` text, '.
            '`conditions` text, '.
            '`condition_join_type` varchar(3) DEFAULT \'and\' NOT NULL '.
            ')'
        );
        $m->exec('CREATE INDEX `shop_sdekint_calc_rules_sort_index` ON `shop_sdekint_calc_rules` (`sort`)');
    } catch (Exception $e) {
        waLog::log('Ошибка при создании таблицы в файле обновления ' . __FILE__ . ': ' . $e->getMessage());
    }

}

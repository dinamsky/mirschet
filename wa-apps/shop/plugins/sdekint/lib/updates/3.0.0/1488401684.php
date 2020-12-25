<?php
$plugin_dir = wa('shop')->getConfig()->getPluginPath('sdekint');

// это файл тоже заливает обновленный sql-дамп в базу. Он нам не нужен больше
waFiles::delete($plugin_dir . '/lib/updates/2.5.0/1482024780.php');

$model = new waModel();
/**
 * Чтоб обновление больше не срабатывало если вдруг

try {
    $model->exec('ALTER TABLE `shop_sdekint_cities` MODIFY COLUMN `region_code` VARCHAR(8) NULL;');

    $sql_file = $plugin_dir . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'shop_sdekint_cities.sql';
    if (file_exists($sql_file) && is_readable($sql_file) && is_file($sql_file)) {
        $sqls = file_get_contents($sql_file);
        $sqls = preg_split("/;\r?\n/", $sqls);
        foreach ($sqls as $sql) {
            if (trim($sql)) {
                $model->exec($sql);
            }
        }
    }
} catch (waException $e) {

}
 **/

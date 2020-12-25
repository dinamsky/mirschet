<?php
/**
 * Для предотвращения многократной загрузки
 *
$plugin_dir = wa('shop')->getConfig()->getPluginPath('sdekint');

$model = new waModel();
$sql_file = $plugin_dir . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'shop_sdekint_cities.sql';

try {
    if (!file_exists($sql_file)) {
        throw new waException('Не существует файла ' . $sql_file);
    }
    if (!is_readable($sql_file)) {
        throw new waException('Файл не может быть открыт для чтения:' . $sql_file);
    }
    if (!is_file($sql_file)) {
        throw new waException('Файл не файл: ' . $sql_file);
    }
    $sqls = file_get_contents($sql_file);
    $sqls = preg_split("/;\r?\n/", $sqls);
    foreach ($sqls as $sql) {
        if (trim($sql)) {
            $model->exec($sql);
        }
    }
} catch (waException $e) {
    waLog::log('Обновление списка городов. ' . $e->getMessage(), 'shop/sdekint.log');
}
 */

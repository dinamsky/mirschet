<?php
/**
 * Для предотвращения многократной загрузки списка
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
    if ($sqls === false) {
        throw new waException('Ошибка file_get_contents при обновлении списка городов');
    }

    try {
        $model->exec('TRUNCATE `shop_sdekint_cities`');
    } catch (waDbException $e) {
        waLog::log('Ошибка TRUNCATE списка городов', 'shop/sdekint.log');
        $model->exec('DELETE * FROM `shop_sdekint_cities`');
    } catch (Exception $e) {
        throw $e;
    }

    $sqls = preg_split("/;\r?\n/", $sqls);
    foreach ($sqls as $sql) {
        if (trim($sql)) {
            $model->exec($sql);
        }
    }

    waLog::log('Успешно выполнено обновление списка городов ' . __FILE__, 'shop/sdekint.log');
} catch (Exception $e) {
    waLog::log('Обновление списка городов. ' . $e->getMessage(), 'shop/sdekint.log');
}
 */

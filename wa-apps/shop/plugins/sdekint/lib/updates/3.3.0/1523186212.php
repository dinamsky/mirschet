<?php
/**
 * Для предотвращения срабатывания загрузки

$plugin_dir = wa('shop')->getConfig()->getPluginPath('sdekint');
$lock_file = wa('shop')->getTempPath('plugin' . DIRECTORY_SEPARATOR . 'sdekint' . DIRECTORY_SEPARATOR) . 'update.lock';

if(file_exists($lock_file)) {
    waLog::log('Обновление: ' . __FILE__ .' уже выполняется. Пресечена попытка второго запуска', 'shop/sdekint.log');
    return;
}

waFiles::create($lock_file);

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
    } catch (Exception $e) {
        waLog::log('Ошибка TRUNCATE списка городов', 'shop/sdekint.log');
        $model->exec('DELETE * FROM `shop_sdekint_cities` WHERE 1=1');
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

waFiles::delete($lock_file);
*/

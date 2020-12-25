<?php
$plugin_path = wa('shop')->getConfig()->getPluginPath('sdekint');
$file = $plugin_path . '/js/plugin.sdekint.backend_settings.js';
try {
    waFiles::delete($file);
} catch (Exception $e) {
    waLog::log("Ошибка при выполнении удаления ненужных файлов в скрипте обновления " . __FILE__ . ": " . $e->getMessage(), 'sdekint.log');
}
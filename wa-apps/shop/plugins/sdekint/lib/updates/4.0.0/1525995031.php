<?php
$plugin_path = wa('shop')->getConfig()->getPluginPath('sdekint');
foreach (array(
             $plugin_path . '/lib/actions/orderactions/shopSdekintPluginOrderactionsEdit.action.php',
             $plugin_path . '/lib/actions/orderactions/shopSdekintPluginOrderactionsAdd.action.php',
             $plugin_path . '/lib/actions/orderactions/shopSdekintPluginOrderactionsDisplay.action.php',
             $plugin_path . '/templates/actions/orderactions'
         ) as $file) {
    try {
        waFiles::delete($file);
    } catch (Exception $e) {
        waLog::log("Ошибка при выполнении удаления ненужных файлов в скрипте обновления " . __FILE__ . ": " . $e->getMessage(), 'sdekint.log');
    }
};

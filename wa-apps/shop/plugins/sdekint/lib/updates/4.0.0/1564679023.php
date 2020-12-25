<?php
$action_dir = wa('shop')->getConfig()->getPluginPath('sdekint') . '/lib/actions';

$files = ['/shopSdekintPluginSettings.action.php'];

foreach ($files as $file) {
    try {
        waFiles::delete($action_dir . $file);
    } catch (Exception $e) {
        waLog::log('Cdek Integration plugin update (' . __FILE__ . "): Error deleting old file $file: " . $e->getMessage());
    }
}

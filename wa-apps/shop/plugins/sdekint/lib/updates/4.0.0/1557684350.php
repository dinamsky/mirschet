<?php
$vendor_dir = wa('shop')->getConfig()->getPluginPath('sdekint') . '/lib/vendors';

$files = [
    '/syrnik/hash',
    '/syrnik/text',
    '/syrnik/xml'
];

foreach ($files as $file) {
    try {
        waFiles::delete($vendor_dir . $file);
    } catch (Exception $e) {
        waLog::log('Cdek Integration plugin update (' . __FILE__ . "): Error deleting old vendor file $file: " . $e->getMessage());
    }
}

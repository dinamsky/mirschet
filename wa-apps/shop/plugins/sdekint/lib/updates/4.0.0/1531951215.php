<?php
$vendor_dir = wa('shop')->getConfig()->getPluginPath('sdekint') . '/lib/vendors';

$files = array(
    '/syrnikHash.class.php',
    '/syrnikString.class.php',
    '/syrnikXml.class.php',
    '/syrnikXmlException.class.php',
);
foreach ($files as $file) {
    try {
        waFiles::delete($vendor_dir . $file);
    } catch (Exception $e) {
        waLog::log('Cdek Integration plugin update (' . __FILE__ . "): Error deleting old vendor file $file: " . $e->getMessage());
    }
}

// у нас файловая система нерегистрозависимая, будем ковырять по одному файлу
if (file_exists($vendor_dir . '/webit/Util')) {
    try {
        waFiles::delete($vendor_dir . '/webit/Util');
    } catch (Exception $e) {
        waLog::log('Cdek Integration plugin update (' . __FILE__ . "): Error deleting old vendor file /webit/Util: " . $e->getMessage());
    }
} elseif(!file_exists($vendor_dir . '/Webit/eval-math')) {
    try {
        waFiles::delete($vendor_dir . '/Webit');
    } catch (Exception $e) {
        waLog::log('Cdek Integration plugin update (' . __FILE__ . "): Error deleting old vendor file /Webit: " . $e->getMessage());
    }
}

if(file_exists($vendor_dir . '/syrnik/Hash.php')) {
    $files = array(
        '/syrnik/Hash.php',
        '/syrnik/Html.php',
        '/syrnik/Text.php',
        '/syrnik/Xml/Exception.php',
        '/syrnik/Xml/Xml.php',
        '/syrnik/Text/Inflector.php',
    );

    foreach ($files as $file) {
        try {
            waFiles::delete($vendor_dir . $file);
        } catch (Exception $e) {
            waLog::log('Cdek Integration plugin update (' . __FILE__ . "): Error deleting old vendor file $file: " . $e->getMessage());
        }
    }
} elseif(!file_exists($vendor_dir . '/Syrnik/hash/composer.json')) {
    try {
        waFiles::delete($vendor_dir . '/Syrnik');
    } catch (Exception $e) {
        waLog::log('Cdek Integration plugin update (' . __FILE__ . '): Error deleting old vendor lib /Syrnik: ' . $e->getMessage());
    }
}

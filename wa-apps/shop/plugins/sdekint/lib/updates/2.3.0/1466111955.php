<?php
/**
 * Remove invalid named file
 * Update 2.2.3 -> 2.3.0
 */

$plugin_root = wa('shop')->getConfig()->getPluginPath('sdekint');

$filelist = array(
    '/css/sdekint_backend.css.map',
    '/css/sdekint_backend.scss',
    '/css/sdekint_backend_order_info.css.map',
    '/css/sdekint_backend_order_info.scss',
    '/css/sdekint.css',
    '/CHANGELOG.md',
    '/lib/actions/backend',
);

foreach ($filelist as $file) {
    waFiles::delete( $plugin_root . $file, true);
}



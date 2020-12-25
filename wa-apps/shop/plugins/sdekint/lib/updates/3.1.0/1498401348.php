<?php
/**
 * Больше не нужны сторонние библиотеки
 */
$plugin_path = wa('shop')->getConfig()->getPluginPath('sdekint');
waFiles::delete( $plugin_path . '/js/sugar.min.js');
waFiles::delete( $plugin_path . '/js/dependsOn-1.0.2.js');
waFiles::delete( $plugin_path . '/js/dependsOn-1.0.2.min.js');
waFiles::delete( $plugin_path . '/css/sdekint_backend.scss');
waFiles::delete( $plugin_path . '/css/sdekint_backend.css');

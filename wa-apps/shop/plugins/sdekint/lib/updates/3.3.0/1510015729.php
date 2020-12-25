<?php
$plugin_dir = wa('shop')->getConfig()->getPluginPath('sdekint');

$plugin_path = wa('shop')->getConfig()->getPluginPath('sdekint');
waFiles::delete( $plugin_path . '/lib/classes/shopSdekintPluginViewHelper.class.php');

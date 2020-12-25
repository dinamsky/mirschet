<?php
$plugin_path = wa('shop')->getConfig()->getPluginPath('sdekint');
waFiles::delete( $plugin_path . '/js/plugin.sdekint.backend_order.js');

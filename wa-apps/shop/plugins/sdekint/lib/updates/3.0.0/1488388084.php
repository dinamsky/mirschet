<?php
$plugin_dir = wa('shop')->getConfig()->getPluginPath('sdekint');
waFiles::delete($plugin_dir . '/js/sdekint_settings.js');
waFiles::delete($plugin_dir . '/js/sdekint_settings.min.js');

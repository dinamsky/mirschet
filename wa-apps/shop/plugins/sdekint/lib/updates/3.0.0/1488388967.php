<?php
$plugin_dir = wa('shop')->getConfig()->getPluginPath('sdekint');
waFiles::delete($plugin_dir . '/img/sdek-bw-16.png');
waFiles::delete($plugin_dir . '/css/sdekint.scss');
waFiles::delete($plugin_dir . '/css/sdekint.css');

// Больше не нужен парсер CSV
waFiles::delete($plugin_dir . '/lib/cli/shopSdekintLoadcities.cli.php');
waFiles::delete($plugin_dir . '/lib/classes/shopSdekintPluginReqExtException.class.php');
waFiles::delete($plugin_dir . '/lib/classes/shopSdekintPluginItemFilter.class.php');
waFiles::delete($plugin_dir . '/lib/classes/vendors');

//Больше не используем этот js
waFiles::delete($plugin_dir . '/js/jquery.inputmask.min.js');
waFiles::delete($plugin_dir . '/js/jquery.inputmask.date.extensions.min.js');
waFiles::delete($plugin_dir . '/js/inputmask.min.js');

<?php
$plugin_path = wa('shop')->getConfig()->getPluginPath('sdekint');
waFiles::delete( $plugin_path . '/lib/classes/shopSdekintPluginOrdersPrintRequest.class.php');
waFiles::delete( $plugin_path . '/lib/classes/shopSdekintPluginDeliveryRequest.class.php');
waFiles::delete( $plugin_path . '/lib/classes/shopSdekintPluginOrderHelper.class.php');


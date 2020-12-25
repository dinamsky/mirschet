<?php
/**
 * Remove invalid named file
 * Update 2.2.2 -> 2.2.3
 */

waFiles::delete(wa('shop')->getConfig()->getPluginPath('sdekint') . '/lib/classes/shopSdekintPluginReqExtException.php');

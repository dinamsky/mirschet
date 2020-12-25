<?php
/**
 * Remove unused class. Since we use waNet we do not need a separate cURL exception.
 *
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @version 2.4.0
 * @copyright Serge Rodovnichenko, 2016
 */
waFiles::delete(wa('shop')->getConfig()->getPluginPath('sdekint') . '/lib/classes/shopSdekintPluginCurlException.class.php');

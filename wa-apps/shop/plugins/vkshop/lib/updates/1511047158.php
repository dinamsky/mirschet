<?php
/**
 * Created by PhpStorm.
 * User: snark | itfrogs.ru
 * Date: 11/19/17
 * Time: 2:19 AM
 */

try {
    $path = wa()->getAppPath(null, 'shop') . '/plugins/vkshop/lib/classes/';
    $paths = array(
        $path . 'shopVkshopPluginSend.class.php',
    );
    foreach ($paths as $path) {
        waFiles::delete($path, true);
    }
}
catch (waException $e) {

}
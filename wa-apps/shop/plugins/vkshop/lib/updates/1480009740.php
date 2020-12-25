<?php
/**
 * Created by PhpStorm.
 * User: snark | itfrogs.ru
 * Date: 11/24/16
 * Time: 8:49 PM
 */

try {
    $js_path = wa()->getAppPath(null, 'shop') . '/plugins/vkshop/js/';
    $paths = array(
        $js_path . 'image-picker.min.min.js',
        $js_path . 'sugar.min.min.js',
        $js_path . 'vkshop-export.min.min.js',
        $js_path . 'vkshop.min.min.js',
        $js_path . 'vkshop_settings.min.min.js',
    );
    foreach ($paths as $path) {
        waFiles::delete($path, true);
    }
}
catch (waException $e) {

}
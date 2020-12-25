<?php
/**
 * Created by PhpStorm.
 * User: snark | itfrogs.ru
 * Date: 3/20/19
 * Time: 2:29 PM
 */

try {
    $tpl_path = wa()->getAppPath(null, 'shop') . '/plugins/vkshop/templates/';
    $paths = array(
        $tpl_path . 'errors.html',
        $tpl_path . 'login.html',
        $tpl_path . 'category.html',
    );
    foreach ($paths as $path) {
        waFiles::delete($path, true);
    }
}
catch (waException $e) {

}

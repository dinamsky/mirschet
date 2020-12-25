<?php
/**
 * Created by PhpStorm.
 * User: snark | itfrogs.ru
 * Date: 8/1/16
 * Time: 8:54 PM
 */

try {
    $path = wa()->getAppPath(null, 'shop') . '/plugins/vkshop/templates/locale/';
    waFiles::delete($path, true);
}
catch (waException $e) {

}
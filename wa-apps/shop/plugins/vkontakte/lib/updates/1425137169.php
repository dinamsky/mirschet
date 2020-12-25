<?php
/**
 * Created by PhpStorm.
 * User: snark | itfrogs.ru
 * Date: 2/28/15
 * Time: 1:27 AM
  */

try {
    $Settings = new waAppSettingsModel();
    $Settings->set(array('shop', 'vkontakte'), 'desc', 'description');
} catch (waException $ex) {
    // Что-то делать, если что-то пошло не так
}
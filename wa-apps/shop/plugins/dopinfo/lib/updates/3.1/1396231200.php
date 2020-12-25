<?php

$plugin_id = array('shop', 'dopinfo');
$app_settings_model = new waAppSettingsModel();

/*Задаем новые настройки*/

//Статус плагина (Включен|Выключен)
$app_settings_model->set($plugin_id, 'state', '0');
//Заказы по статусам
$app_settings_model->set($plugin_id, 'state_info', '0');
//Заказы по email
$app_settings_model->set($plugin_id, 'email_check', '0');
//Проверка по ip
$app_settings_model->set($plugin_id, 'ip_check', '0');
//Проверка по телефону
$app_settings_model->set($plugin_id, 'phone_check', '0');
//Цифр для поиска
$app_settings_model->set($plugin_id, 'phone_number', '7');
//Проверка по черному списку
$app_settings_model->set($plugin_id, 'black_list_state', '0');
//Вывод групп покупателя
$app_settings_model->set($plugin_id, 'client_group', '0');


/*Удаляем старые настройки*/
$app_settings_model->del($plugin_id, 'ip');
$app_settings_model->del($plugin_id, 'orders');
$app_settings_model->del($plugin_id, 'phone');
$app_settings_model->del($plugin_id, 'statuses');
$app_settings_model->del($plugin_id, 'blacklist');

/*Удаляем ненужные файлы*/
$paths = array(
    'lib/config/settings.php',
    'templates/dopinfo.html'

);
foreach ($paths as $path) {
    waFiles::delete('wa-apps/shop/plugins/'.$path, true);
}




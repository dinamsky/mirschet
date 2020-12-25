<?php

$plugin_id = array('shop', 'dopinfo');
$app_settings_model = new waAppSettingsModel(); 
//Изначально плагин выключен
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

        


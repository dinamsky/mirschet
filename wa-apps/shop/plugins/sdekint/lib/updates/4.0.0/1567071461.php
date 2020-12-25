<?php
$app_settings_model = new waAppSettingsModel();

$sellers = $app_settings_model->get('shop.sdekint', 'sellers');
if(!is_array($sellers)) {
    $seller_name = (string)$app_settings_model->get('shop.sdekint', 'seller_name');
    $sellers = array(
        ['address' => '', 'name' => $seller_name, 'inn' => '', 'phone' => '', 'ownership_form' => '', 'id'=>waString::uuid(), '_is_default'=>true]
    );
    $app_settings_model->set('shop.sdekint', 'sellers', json_encode($sellers));
}
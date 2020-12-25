<?php

class shopDopinfoPluginBackendSaveController extends waJsonController
{
    
    protected $plugin_id = array('shop', 'dopinfo');
    
    public function execute()
    {
        try {
            $app_settings_model = new waAppSettingsModel();
            $settings = waRequest::post('settings');
            $available_state = waRequest::post('available_state');            
            
            //Включение/выключение плагина
            if(isset($settings['state'])){
                $app_settings_model->set($this->plugin_id, 'state', (int) $settings['state']);
            }else{
                $app_settings_model->set($this->plugin_id, 'state', 0);
            }
            
            //Информация по статусам
            if(isset($settings['state_info'])){
                $app_settings_model->set($this->plugin_id, 'state_info', (int) $settings['state_info']);
            }else{
                $app_settings_model->set($this->plugin_id, 'state_info', 0);
            }
            
            //Проверка по email
            if(isset($settings['email_check'])){
                $app_settings_model->set($this->plugin_id, 'email_check', (int) $settings['email_check']);
            }else{
                $app_settings_model->set($this->plugin_id, 'email_check', 0);
            }           
            
            
            //Проверка по телефону
            if(isset($settings['phone_check'])){
                $app_settings_model->set($this->plugin_id, 'phone_check', (int) $settings['phone_check']);
            }else{
                $app_settings_model->set($this->plugin_id, 'phone_check', 0);
            }
            
            //Проверка по телефону
            if(isset($settings['phone_number'])){
                $app_settings_model->set($this->plugin_id, 'phone_number', (int) $settings['phone_number']);
            }else{
                $app_settings_model->set($this->plugin_id, 'phone_number', 7);
            }
            
            //Проверка по ip
            if(isset($settings['ip_check'])){
                $app_settings_model->set($this->plugin_id, 'ip_check', (int) $settings['ip_check']);
            }else{
                $app_settings_model->set($this->plugin_id, 'ip_check', 0);
            }
            
            //Проверка на черный список
            if(isset($settings['black_list_state'])){
                $app_settings_model->set($this->plugin_id, 'black_list_state', (int) $settings['black_list_state']);
            }else{
                $app_settings_model->set($this->plugin_id, 'black_list_state', 0);
            } 
            
            //Черный список
            if(isset($settings['black_list'])){
                $app_settings_model->set($this->plugin_id, 'black_list', (int) $settings['black_list']);
            }
               
            /*
            //Информация по статусам
            if(isset($available_state) && !empty($available_state)){
                $app_settings_model->set($this->plugin_id, 'available_state', json_encode($available_state));
            }else{
                $app_settings_model->set($this->plugin_id, 'available_state', 0);
            } 
            
            //Для каких статусов выводить
            if(isset($settings['order_info'])){
                $app_settings_model->set($this->plugin_id, 'order_info', (int) $settings['order_info']);
            }else{
                $app_settings_model->set($this->plugin_id, 'order_info', 0);
            }
             
             */
            
            //Информация по группам
            if(isset($settings['client_group'])){
                $app_settings_model->set($this->plugin_id, 'client_group', (int) $settings['client_group']);
            }else{
                $app_settings_model->set($this->plugin_id, 'client_group', 0);
            }
            
            $this->response['message'] = "Сохранено";
            
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }
}
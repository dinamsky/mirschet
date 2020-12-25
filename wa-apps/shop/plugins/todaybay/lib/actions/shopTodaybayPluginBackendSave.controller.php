<?php

class shopTodaybayPluginBackendSaveController extends waJsonController {    
    
    protected $plugin_id = array('shop', 'todaybay');
    protected $todaybay = 'plugins/todaybay/templates/Todaybay.html';
    protected $todaybaylist = 'plugins/todaybay/templates/todaybaylist.html';


    public function execute()
    {
        try {
            $app_settings_model = new waAppSettingsModel();
            $settings = waRequest::post('settings');            
            
            //Включено/выключено
            if(isset($settings['state'])){
                $app_settings_model->set($this->plugin_id, 'state', (int) $settings['state']);
            }else{
                $app_settings_model->set($this->plugin_id, 'state', 0);
            }
            
            //Количество выводимых товаров
            if(isset($settings['count'])){
                $app_settings_model->set($this->plugin_id, 'count', (int) $settings['count']);
            }else{
                $app_settings_model->set($this->plugin_id, 'state', 5);
            }            
                                 
            //Ничего не куплено
            if(isset($settings['nothingshow'])){
                $app_settings_model->set($this->plugin_id, 'nothingshow', $app_settings_model->escape($settings['nothingshow']));
            }else{
                $app_settings_model->set($this->plugin_id, 'nothingshow', 'nothing');
            }
            
            //Товаров меньше чем показывать
            if(isset($settings['dowhat'])){
                $app_settings_model->set($this->plugin_id, 'dowhat', $app_settings_model->escape($settings['dowhat']));
            }else{
                $app_settings_model->set($this->plugin_id, 'dowhat', 'nothing');
            }
            
            //Есть ли список из которого добавлять
            if(isset($settings['list'])){
                //Есть
                $app_settings_model->set($this->plugin_id, 'list', $app_settings_model->escape($settings['list']));
            }else{
                //Нет. Меняем настройки "Ничего не куплено"  и "Товаров меньше чем показывать"
                $app_settings_model->set($this->plugin_id, 'nothingshow', 'nothing');
                $app_settings_model->set($this->plugin_id, 'dowhat', 'nothing');
            }
            
            //Использовать хук или хелпер
            if(isset($settings['usehook'])){
                $app_settings_model->set($this->plugin_id, 'usehook', $app_settings_model->escape($settings['usehook']));
            }else{
                $app_settings_model->set($this->plugin_id, 'usehook', 'hook');
            }
            
            //Как выводить. Шаблон или список.
            $app_settings_model->set($this->plugin_id, 'output', $app_settings_model->escape($settings['output']));            
            
            $output = $settings['output'];
                       
            if($output == 'template'){
                $path = $this->todaybay;
                $post_template = waRequest::post('template');               
            }
            
            if($output == 'list'){
                $path = $this->todaybaylist;                
                $post_template = waRequest::post('listtemplate'); 
            } 
            
            if(isset($settings['reset_tml']) || isset($settings['reset_list'])){
                $template_path = wa()->getDataPath($path, false, 'shop', false);
                waFiles::delete($template_path);
                $this->response['message'] = "Сохранено";
                return;
            }
            
            if(!$post_template){
                throw new waException('Внимание! Не указан шаблон');
            }            
            
            $template_path = wa()->getDataPath($path, false, 'shop', false);
            
            if(!file_exists($template_path)){
                $template_path = wa()->getAppPath($path, 'shop');                
            }
            
            $template = file_get_contents($template_path);
           
            if($template != $post_template){
                $template_path = wa()->getDataPath($path, false, 'shop', true);
                waFiles::write($template_path, $post_template);
            }
           
            $this->response['message'] = "Сохранено";
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }
}
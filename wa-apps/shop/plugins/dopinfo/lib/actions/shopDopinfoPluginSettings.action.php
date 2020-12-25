<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

class shopDopinfoPluginSettingsAction extends waViewAction
{
    protected $plugin_id = array('shop', 'dopinfo');

    public function execute()
    {
        $app_settings_model = new waAppSettingsModel();        
        $settings = $app_settings_model->get($this->plugin_id);        
          
        /*$workflow = new shopWorkflow();
        //Получаем существующие статусы заказов
	$available_state = array();        
	foreach($workflow->getAvailableStates() as $state_id => $val){
            $available_state[$state_id] = array('name' => $val['name'],
                                                 'style' => $val['options']['style'],
                                                 'icon' => $val['options']['icon']
                                                );
        }
        
        //Получаем настроенные статусы
        if(isset($settings['available_state'])){            
            $available_state_settings = json_decode($settings['available_state'], true);
            $this->view->assign('available_state_settings', $available_state_settings);
        }*/
        
        //Получаем категории покупателей
        $category_model = new waContactCategoryModel();
        
        $cat = $category_model->getAll();
        
        if(isset($cat) && !empty($cat)){
            $this->view->assign('contact_category', $cat);
        } 
        
        //$this->view->assign('available_state', $available_state);        
        $this->view->assign('settings', $settings);        
    } 
    
}

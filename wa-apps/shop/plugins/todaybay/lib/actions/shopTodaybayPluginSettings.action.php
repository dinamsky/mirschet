<?php

class shopTodaybayPluginSettingsAction extends waViewAction
{       
    protected $plugin_id = array('shop', 'todaybay');
    
    public function execute()
    {
        //Настройки
        $app_settings_model = new waAppSettingsModel();
        $settings = $app_settings_model->get($this->plugin_id);
        $this->view->assign('settings', $settings);
        
        //Образец 1
        $sample1_path = wa()->getAppPath('plugins/todaybay/templates/sample/sample1.html',  'shop');
        $sample1_template = file_get_contents($sample1_path);
        $this->view->assign('sample1_template', $sample1_template);
        
        //Образец 2
        $sample2_path = wa()->getAppPath('plugins/todaybay/templates/sample/sample2.html',  'shop');
        $sample2_template = file_get_contents($sample2_path);
        $this->view->assign('sample2_template', $sample2_template);
        
        //Списки товаров
        $set_model = new shopSetModel();
        $set = $set_model->getAll();        
        $sets = array();
        foreach($set as $val => $key){
            $sets[$key['id']] = $key['name'];
        }
        
        if(!empty($sets)){
            $this->view->assign('sets', $sets);
        }
        
        //Изменение шаблона 
        $tml_change = false;
        
        $template_path = wa()->getDataPath('plugins/todaybay/templates/Todaybay.html', false, 'shop', false);
        if(file_exists($template_path)){
            $tml_change = true;
        }else{
            $template_path = wa()->getAppPath('plugins/todaybay/templates/Todaybay.html',  'shop');
        }
        
        $template = file_get_contents($template_path);
        
        $this->view->assign('template', $template);
        $this->view->assign('tml_change', $tml_change);
        
        //Изменение шаблона списка
        $list_change = false;
        
        $list_path = wa()->getDataPath('plugins/todaybay/templates/todaybaylist.html', false, 'shop', false);
        if(file_exists($list_path)){
            $list_change = true;
        }else{
            $list_path = wa()->getAppPath('plugins/todaybay/templates/todaybaylist.html',  'shop');
        } 
        
        $listtemplate = file_get_contents($list_path);
        
        $this->view->assign('listtemplate', $listtemplate);
        $this->view->assign('list_change', $list_change);
    }
}

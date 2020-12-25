<?php

class shopRegionPluginSettingsAddController extends waJsonController {
    
    public function execute()
    {
        $plugin_id = array('shop', 'region');    
        try
        {    
            $db = new shopRegionModel();
            $result = $db->insert(array("domain" => shopRegionPlugin::getRootDomain(),"sort" => 9999), 0);
            $this->response['message'] = $result; //возвращает ID добавленного региона в скрипт
        }
        catch (Exception $e) 
        {
           $this->setError($e->getMessage());
        }
    }
}
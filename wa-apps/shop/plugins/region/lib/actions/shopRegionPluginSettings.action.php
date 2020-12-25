<?php

class shopRegionPluginSettingsAction extends waViewAction
{
    
    public function execute()
    {
		$arRegion = $domain_setting = array();
        $rootDomain = shopRegionPlugin::getRootDomain();
		$fields = shopRegionPlugin::getFieldList();
        
		$model_settings = new waAppSettingsModel();
        $settings = $model_settings->get($key = array('shop', 'region'));         
        
		foreach($settings as $key => $value)
		{
			if(stripos($key, "_".$rootDomain) !== 0)
			{
				if($key == 'title_'.$rootDomain AND $value == "")
					$value = "#title# - #region#";
					
				$setting[str_replace("_".$rootDomain,"",$key)] = $value;
			}
		}
		$this->view->assign('settings', $setting);
		$this->view->assign('root_domain', $rootDomain);
		$this->view->assign('fields', $fields);
        
        $this->getTemplateHelper('ru', $rootDomain);
        $this->getTemplateHelper('en', $rootDomain);
        
        $db = new shopRegionModel();
		$result = $db->query("SELECT * FROM `".$db->table."` WHERE `domain` = '".$rootDomain."' ORDER BY `sort`;")->fetchAll();
		if(count($result) == 0)
		{
			$db->query("INSERT INTO `".$db->table."` (`sort`, `subdomain`, `domain`) VALUES (99999, 'www', '".$rootDomain."');");
			$result = $db->query("SELECT * FROM `".$db->table."` WHERE `domain` = '".$rootDomain."' ORDER BY `sort`;")->fetchAll();
		}
		
        foreach($result as $region)
        {
            $arRegion[] = $region;
        }
		$this->view->assign('regions', $arRegion);	
	}    

    private function getTemplateHelper($lang, $rootDomain)
    {
        $template_path = wa()->getDataPath('plugins/region/templates/helper.'.$rootDomain.'.'.$lang.'.html', false, 'shop', true);
        if (!file_exists($template_path)) 
            $template_path = wa()->getAppPath('plugins/region/templates/helper.'.$lang.'.html', 'shop');         
        $template_content = file_get_contents($template_path);
        $this->view->assign('template_'.$lang, $template_content);
    }
}
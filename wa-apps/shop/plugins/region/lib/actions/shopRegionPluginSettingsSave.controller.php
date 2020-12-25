<?php
class shopRegionPluginSettingsSaveController extends waJsonController {
    
    private function saveRegionDB($fields, $rootDomain)
    {
        $db = new shopRegionModel();
        wa('site');
        $db_ = new siteDomainModel();
        $result = $db->query("UPDATE `".$db->table."` SET `sort` = 0 WHERE `domain` = '".$rootDomain."';"); //чтобы отследить отсутствующие поддомены всем присваиваем нулевую сортировку
   
        $filepath = wa()->getConfig()->getRootPath().'/wa-config/routing.php';
        $file = fopen ($filepath,"a");//открытие файла роутинга
        flock($file,LOCK_EX);//блокировка файла на запись
        $routingArray = include($filepath); //чтение конфигурации в массив
        
        foreach($routingArray as $key=>$value) //удаляем все зеркала основного домена
        {
            if($value == $rootDomain)
                unset($routingArray[$key]);
        }
        
        $i = 0;
		
		$dbFields = shopRegionPlugin::getFieldList();
		
        foreach($fields as $key => $region)
        {
            $region['subdomain'] = trim(str_replace(array($rootDomain,'\'','"'), array("","",""), $region['subdomain']));
			$arData = 
				array(
					'sort' => ++$i, 
					'region_ru' => $region['region_ru'],
					'region_en' => $region['region_en'],
					'subdomain' => $region['subdomain'],
					'header_ru' => $region['header_ru'],
					'header_en' => $region['header_en']);
			
			foreach($dbFields as $row)
			{
				$arData[$row.'_ru'] = $region[$row.'_ru'];
				$arData[$row.'_en'] = $region[$row.'_en'];
			}
			
            //$db->updateById($key, $arData);  не сохраняет почему-то
			foreach($arData as $name => $value)
			{
				if($name != 'sort')
					$db->query("UPDATE `shop_region` SET ".$name." = s:value WHERE id = i:id", array("id" => $key, "value" => $value));
				else
					$db->query("UPDATE `shop_region` SET ".$name." = i:value WHERE id = i:id", array("id" => $key, "value" => $value));
			}


			if(strpos($region['subdomain'], '.') !== false) {//добавляем существующие зеркала
                $routingArray[$region['subdomain']] = $rootDomain;
                $db_->insert(array(
                    'name' => $region['subdomain'],
                    'title' => NULL,
                    'style' => NULL
                ), 1);
            } else if($region['subdomain'] != 'www' and $region['subdomain'] != '') {
                $db_->insert(array(
                    'name' => $region['subdomain'] . "." . $rootDomain,
                    'title' => NULL,
                    'style' => NULL
                ), 1);
                $routingArray[$region['subdomain'] . "." . $rootDomain] = $rootDomain;
            }
        }
        
        $result = $db->query("DELETE FROM `".$db->table."` WHERE `sort` = 0;"); //удаляем регионы, которые не присвоена сортировка, так как пользователь их удалил
        
        ftruncate($file, 0);//очищаем содержимое файла конфигурации 
        fputs($file, "<?php\nreturn ".var_export($routingArray, true).";");//записываем новые данные 
        fflush($file);//очищаем файловый буфер и записываем в файл 
        flock($file,LOCK_UN);//снимаем блокировку 
        fclose($file);
    }
    
    public function execute()
    {
        $plugin_id = array('shop', 'region');
		$rootDomain = shopRegionPlugin::getRootDomain();	
		
        try
        {    
            $application_settings_model = new waAppSettingsModel();
            $region = waRequest::post('settings');
            $regionDB = waRequest::post('region');
			
            $application_settings_model->set($plugin_id, 'column_'.$rootDomain, $region['column']);
			$application_settings_model->set($plugin_id, 'type_'.$rootDomain, $region['type']);
            $application_settings_model->set($plugin_id, 'uitheme_'.$rootDomain, $region['uitheme']);
            $application_settings_model->set($plugin_id, 'columnWidth_'.$rootDomain, $region['columnWidth']);
            $application_settings_model->set($plugin_id, 'status_'.$rootDomain, isset($region['status']) ? 1 : 0);
			$application_settings_model->set($plugin_id, 'autoSave_'.$rootDomain, isset($region['autoSave']) ? 1 : 0);
			$application_settings_model->set($plugin_id, 'redactor_'.$rootDomain, isset($region['redactor']) ? 1 : 0);
			$application_settings_model->set($plugin_id, 'title_'.$rootDomain, $region['title'] == '' ? '#title# - #region#' : $region['title']);
			$application_settings_model->set($plugin_id, 'description_'.$rootDomain, $region['description'] == '' ? '#description# - #region#' : $region['description']);
            $application_settings_model->set($plugin_id, 'update_time', time());
            
            $this->saveTemplateHelper('ru', $rootDomain);
            $this->saveTemplateHelper('en', $rootDomain);
            $this->saveRegionDB($regionDB, $rootDomain);

            $this->response['message'] = _w('Saved');
        }
        catch (Exception $e) 
        {
           $this->setError($e->getMessage());
        }
    }
    private function saveTemplateHelper($lang, $rootDomain)
    {
        $template = waRequest::post('template_'.$lang);
        $template_path = wa()->getDataPath('plugins/region/templates/helper.'.$rootDomain.'.'.$lang.'.html', false, 'shop', true);
        
        if(file_exists($template_path))
            @unlink($template_path);
        
        if($template != "")
        {
            $file = fopen($template_path, 'w');
            if (!$file)
            {
                throw new waException(_w('Access error! You cannot save the template. Check write permissions in the folder ') . $template_path);
            }
            fwrite($file, $template);
            fclose($file);
        }
    }
}
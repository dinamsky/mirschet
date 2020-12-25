<?php 
class shopRegionPlugin extends shopPlugin
{
    static private function status() //статус модуля (вкл/выкл)
    {
        $plugin_id = array('shop', 'region');
		$settings = new waAppSettingsModel();
        $status = $settings->get($plugin_id, 'status_'.self::getRootDomain());
        if (!$status) 
            return false;
        else
            return true;
    }
	
	static public function getFieldList()
    {   
		$model = new waModel();
		$arField = $model->query("SHOW COLUMNS FROM `shop_region` WHERE `Type` = 'text' AND `Field` LIKE '%_ru' AND `Field` != 'header_ru';")->fetchAll();
		$fields = array();
		foreach($arField as $value)
		{
			$fields[] = substr($value['Field'], 0, -3);
		}
		return $fields;
	}
	
	static public function getField($name, $lang = "")
    {   
        if (!self::status())
            return;
		
		if($lang != '')
		{
			$lang = $lang == 'ru' ? 'ru' : 'en';
		}	
		else
			$lang = substr(wa()->getLocale(), 0, 2);
		
		$db = new shopRegionModel();
        $result = $db->getByField(array('subdomain' => self::getSubDomain(), 'domain' => self::getRootDomain()));
		return isset($result[$name.'_'.$lang]) ? $result[$name.'_'.$lang] : "";
	}
	
	static public function getContact($lang = "")
    {   
		return self::getField('contacts');
	}
	
	static public function getRegion($lang = "")
    {   
        return self::getField('region');
	}
		
	static public function getPhrase($lang = "")
    {   
        return self::getField('phrase');
	}
	
	static public function getShipping($lang = "")
    {   
        return self::getField('delivery');
	}
   
    static public function getHelper($lang = "")
    {   
        if (!self::status())
            return;
        
        $plugin_id = array('shop', 'region');
        $settings = new waAppSettingsModel();
        $column = $settings->get($plugin_id, 'column_'.self::getRootDomain());
		$html = "";
		
		if($lang != '')
		{
			$lang = $lang == 'ru' ? 'ru' : 'en';
		}	
		else
			$lang = substr(wa()->getLocale(), 0, 2);
        
		$template_path = wa()->getDataPath('plugins/region/templates/helper.'.self::getRootDomain().'.'.$lang.'.html', false, 'shop', true);
        if(!file_exists($template_path)) 
            $template_path = wa()->getAppPath('plugins/region/templates/helper.'.$lang.'.html', 'shop');
        
        $db = new shopRegionModel();
        $result = $db->query("SELECT * FROM `".$db->table."` WHERE `subdomain` = '".self::getSubDomain()."' AND `domain` = '".self::getRootDomain()."'")->fetchAll();
        if(empty($result) && self::getSubDomain() == self::getRootDomain()){
            $result = $db->query("SELECT * FROM `".$db->table."` WHERE `subdomain` = 'www' AND `domain` = '".self::getRootDomain()."'")->fetchAll();
        }
        
        $helper = wa()->getView();
		$domainSelectorType = $settings->get($plugin_id, 'type_'.self::getRootDomain(),0);
		
		if($domainSelectorType == 0)
		{
			if(isset($result[0]['region_'.($lang == 'ru' ? 'ru' : 'en')]) AND trim($result[0]['region_'.($lang == 'ru' ? 'ru' : 'en')]) != '')
			{
				$html .= '<div class="regionHelperDiv">'.str_replace("#region#",$result[0]['region_'.($lang == 'ru' ? 'ru' : 'en')],$helper->fetch($template_path)).'</div>';
				$html .= '<div id="regionDialog" style="display:none;" title="'.($lang == 'ru' ? 'Ваш город' : 'Your city').'">';
				
				$arResult = $db->query("SELECT * FROM `".$db->table."` WHERE `subdomain` != '' AND `region_".$lang."` != '' AND `domain` = '".self::getRootDomain()."' GROUP BY `region_".$lang."` ORDER BY `sort`")->fetchAll();
				$cycle = round(count($arResult) / $column + 0.49);
				
				if(count($arResult) < $column) // если элементов меньше чем столбцов, то количество столбцов уменьшаем 
					$column = count($arResult);
				
				for($i = 0; $i < $cycle; $i++)    
				{
					for($j = 0; $j < $column; $j++)
					{
						if(isset($arResult[$j * $cycle + $i])) 
							$arResultSort[] = $arResult[$j * $cycle + $i];
						else
							$arResultSort[] = 0;
					}
				}
				
				for($i = 0; $i < count($arResultSort); $i++)
				{
					if($arResultSort[$i] != 0)
						if(strpos($arResultSort[$i]['subdomain'],'.') !== false)
							$html .= '<div style="width:'.$settings->get($plugin_id, 'columnWidth_'.self::getRootDomain()).'px; float:left;"><a class="regionSelectLink" href="//'.$arResultSort[$i]['subdomain'].waRequest::server('REQUEST_URI').'">'.$arResultSort[$i]['region_'.($lang == 'ru' ? 'ru' : 'en')].'</a></div>';
						else
							$html .= '<div style="width:'.$settings->get($plugin_id, 'columnWidth_'.self::getRootDomain()).'px; float:left;"><a class="regionSelectLink" href="//'.($arResultSort[$i]['subdomain'] == 'www' ? '' : $arResultSort[$i]['subdomain'].'.').self::getRootDomain(true).waRequest::server('REQUEST_URI').'">'.$arResultSort[$i]['region_'.($lang == 'ru' ? 'ru' : 'en')].'</a></div>';
					else
						$html .= '<div style="width:'.$settings->get($plugin_id, 'columnWidth_'.self::getRootDomain()).'px; float:left;">&nbsp;</div>';
					
					if(($i+1) % $column == 0)
						$html .= '<div style="clear:both;"></div>';
				}        
				
				$html .= '</div>
				<script>
					$("#regionDialog").dialog({
					  modal: true, 
					  autoOpen: false,
					  width: '.($settings->get($plugin_id, 'columnWidth_'.self::getRootDomain()) * $column + 40).',
					  minHeight: 1,
					});
				  $(".regionHelperDiv a").on("click", function(){$("#regionDialog").dialog("open"); return false;});
				</script>';	 
			}
		}
		else
		{
			$html .= '<div class="regionHelperDiv">'.str_replace("#region#",$result[0]['region_'.($lang == 'ru' ? 'ru' : 'en')],$helper->fetch($template_path)).'</div>';
			
			$select = '<select class="regionHelperSelect"'.($domainSelectorType == 2 ? '' : 'style="display:none;"').'>';     
			$arResult = $db->query("SELECT * FROM `".$db->table."` WHERE `subdomain` != '' AND `region_".$lang."` != '' AND `domain` = '".self::getRootDomain()."' GROUP BY `region_".$lang."` ORDER BY `sort`")->fetchAll();
			for($i = 0; $i < count($arResult); $i++)
			{
				if(strpos($arResult[$i]['subdomain'],'.') !== false)
					$select .= '<option value="//'.$arResult[$i]['subdomain'].waRequest::server('REQUEST_URI').'"'.($result[0]['id'] == $arResult[$i]['id'] ? ' selected' : '').'>'.$arResult[$i]['region_'.($lang == 'ru' ? 'ru' : 'en')].'</option>';
				else
					$select .= '<option value="//'.($arResult[$i]['subdomain'] == 'www' ? '' : $arResult[$i]['subdomain'].'.').self::getRootDomain(true).waRequest::server('REQUEST_URI').'"'.($result[0]['id'] == $arResult[$i]['id'] ? ' selected' : '').'>'.$arResult[$i]['region_'.($lang == 'ru' ? 'ru' : 'en')].'</option>';
			}       
			$select .= '</select>';
			
			if($domainSelectorType == 1)
			{
				$html .= '<script>
			    !window.jQuery && document.write(decodeURI(\'%3Cscript src="'.wa()->getRootUrl(false).'wa-content/js/jquery/jquery-1.11.1.min.js"%3E%3C/script%3E\'));
				$("head").append(\'<link href="'.wa()->getRootUrl(false).'wa-apps/shop/plugins/region/css/select2.css" rel="stylesheet" type="text/css">\');
				$(".regionHelperDiv a").after(\''.$select.'\');
				$(".regionHelperDiv a").on("click", function(){$(".regionHelperDiv a, .regionHelperDiv select").toggle(); var regionSelect = $(".regionHelperDiv select").select2({language: "ru"}); regionSelect.on("change", function (e) {location.href = $(".regionHelperDiv select").val()});  regionSelect.select2("open"); return false;});
				</script>';	 
			}
			else
			{
				$html .= '<script>
			    !window.jQuery && document.write(decodeURI(\'%3Cscript src="'.wa()->getRootUrl(false).'wa-content/js/jquery/jquery-1.11.1.min.js"%3E%3C/script%3E\'));
				$(".regionHelperDiv a").after(\''.$select.'\');
				$(".regionHelperDiv a").remove();
				$(".regionHelperSelect").on("change", function (e) {location.href = $(".regionHelperDiv .regionHelperSelect").val()}); 
				</script>';	 
			}
		}
		
		return $html;
    }
    
    public function frontendHead()
    {
        if (!self::status()) 
           return;
	   
		$plugin_id = array('shop', 'region');
        $settings = new waAppSettingsModel();
        $theme = $settings->get($plugin_id, 'uitheme_'.self::getRootDomain());
		$locale = (substr(wa()->getLocale(), 0, 2) == 'ru' ? 'ru' : 'en');
		
		if($settings->get($plugin_id, 'type_'.self::getRootDomain(),0) == 0)
		{
			$html = '<script>
			!window.jQuery && document.write(decodeURI(\'%3Cscript src="'.wa()->getRootUrl(false).'wa-content/js/jquery/jquery-1.11.1.min.js"%3E%3C/script%3E\'));
			typeof $.ui == "undefined" && document.write(decodeURI(\'%3Cscript src="'.wa()->getRootUrl(false).'wa-content/js/jquery-ui/jquery.ui.core.min.js"%3E%3C/script%3E\'));
			typeof $().position() == "undefined" && document.write(decodeURI(\'%3Cscript src="'.wa()->getRootUrl(false).'wa-content/js/jquery-ui/jquery.ui.position.min.js"%3E%3C/script%3E\'));
			typeof $.Widget == "undefined" && document.write(decodeURI(\'%3Cscript src="'.wa()->getRootUrl(false).'wa-content/js/jquery-ui/jquery.ui.widget.min.js"%3E%3C/script%3E\'));
			typeof $().dialog == "undefined" && document.write(decodeURI(\'%3Cscript src="'.wa()->getRootUrl(false).'wa-content/js/jquery-ui/jquery.ui.dialog.min.js"%3E%3C/script%3E\'));
			</script>';
			if($theme != '')
				$html .= '<link href="//code.jquery.com/ui/1.11.4/themes/'.$theme.'/jquery-ui.css" rel="stylesheet" type="text/css">';
		}
		else
		{
			$html = '<script>
			!window.jQuery && document.write(decodeURI(\'%3Cscript src="'.wa()->getRootUrl(false).'wa-content/js/jquery/jquery-1.11.1.min.js"%3E%3C/script%3E\'));
			typeof $().select2 == "undefined" && document.write(decodeURI(\'%3Cscript src="'.wa()->getRootUrl(false).'wa-apps/shop/plugins/region/js/select2.full.min.js"%3E%3C/script%3E\'));	
			</script>';
		}
		
		$db = new shopRegionModel();
        $result = $db->query("SELECT * FROM `".$db->table."` WHERE `subdomain` = '".$this->getSubDomain()."' AND `domain` = '".self::getRootDomain()."'")->fetchAll();
        $html .= isset($result[0]['header_'.$locale]) ? $result[0]['header_'.$locale] : "";
        
        return $html;
    }
	
    public function frontendFooter()
    {
        if (!self::status()) 
           return;
        $db = new shopRegionModel();
		
		$plugin_id = array('shop', 'region');
        $settings = new waAppSettingsModel();
        $templateTit = $settings->get($plugin_id, 'uitheme_'.self::getRootDomain());
		
        $result = $db->query("SELECT * FROM `".$db->table."` WHERE `subdomain` = '".$this->getSubDomain()."' AND `domain` = '".self::getRootDomain()."'")->fetchAll();
        
		if(isset ($result[0]['domain']))
		{
			if(!isset($result[0]['phrase_ru']))
			{
				$result[0]['phrase_ru'] = $result[0]['phrase_en'] = '';
			}
			
			$title = str_replace(array("#title#","#region#","#phrase#"), array(wa()->getResponse()->getTitle(), $result[0]['region_'.(substr(wa()->getLocale(), 0, 2) == 'ru' ? 'ru' : 'en')], $result[0]['phrase_'.(substr(wa()->getLocale(), 0, 2) == 'ru' ? 'ru' : 'en')]), $settings->get($plugin_id, 'title_'.self::getRootDomain()));
			{wa()->getResponse()->setTitle($title);}
			
			$description = str_replace(array("#description#","#region#","#phrase#"), array(wa()->getResponse()->getMeta('description'), $result[0]['region_'.(substr(wa()->getLocale(), 0, 2) == 'ru' ? 'ru' : 'en')], $result[0]['phrase_'.(substr(wa()->getLocale(), 0, 2) == 'ru' ? 'ru' : 'en')]), $settings->get($plugin_id, 'description_'.self::getRootDomain()));
			{wa()->getResponse()->setMeta('description', $description);}
		}
        return;
    }
    
    static public function getRootDomain($onlyDomain = false)
    {
		$rootDomain = wa()->getRouting()->getDomain();
		if($onlyDomain)
		{
			$expDomain = explode("/", $rootDomain);
			$rootDomain = $expDomain[0];
		}			
		return $rootDomain;
    }
    
    static public function getSubDomain()
    {
        $expHost = explode(".", waRequest::server('HTTP_HOST'));
		if(self::getRootDomain(true) != $expHost[count($expHost)-2].'.'.$expHost[count($expHost)-1]) //проверяем поддомен или другой домен
			return waRequest::server('HTTP_HOST');
		return count($expHost) == 2 ? 'www' : $expHost[0];
    }
}

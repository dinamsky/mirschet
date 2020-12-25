<?php
class shopRegionPluginSettingsDropFieldController extends waJsonController
{
    public function execute()
    {
		$fieldName = waRequest::get('name', '', 'string');
		
		if (preg_match('/^[a-z0-9]{1,}$/i', $fieldName) AND $fieldName != 'region' AND $fieldName != 'header')
		{
			$db = new waModel();
			$db->exec("ALTER TABLE `shop_region` DROP COLUMN `".$fieldName."_ru`, DROP COLUMN `".$fieldName."_en`;");
		}
	}    
}
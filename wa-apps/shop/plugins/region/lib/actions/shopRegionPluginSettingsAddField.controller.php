<?php
class shopRegionPluginSettingsAddFieldController extends waJsonController
{
    public function execute()
    {
		$fieldName = waRequest::get('name', '', 'string');
		
		if (preg_match('/^[a-z0-9]{1,}$/i', $fieldName))
		{
			$db = new waModel();
			$db->exec("ALTER TABLE `shop_region` ADD COLUMN `".$fieldName."_ru` TEXT NULL, ADD COLUMN `".$fieldName."_en` TEXT NULL;");
		}
	}    
}
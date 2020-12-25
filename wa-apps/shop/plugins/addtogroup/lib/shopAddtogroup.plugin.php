<?php


class shopAddtogroupPlugin extends shopPlugin
{




		//Срабатывает при регистрации
		public static function signup($contact)
{ 
		//Получаем настройки плагина
		$plugin = wa('shop')->getPlugin('addtogroup');
		$settings = $plugin->getSettings(); 
		
		
		$is_on = $settings['vkljuchen'];
		if ($is_on == "1"){

		// применяем группу из настроек плагина
		$data['category_id'] = $settings['group'];
		
		// применяем Id контакта из хука
		$newcontact = $contact->getId();
		$data['contact_id'] = $newcontact;

		//получаем модель БД
		$wacontactcategories = new shopAddtogroupPluginWacontactcategoriesModel();
		$wacontactcategories->insert($data);

		}


	
}




// Выводит все статусы в настройки
		public static function getGroup() {



	//получаем модель БД
	$wacontactcategory = new shopAddtogroupPluginWacontactcategoryModel();

	$result = $wacontactcategory->query("SELECT id, name FROM  wa_contact_category");
	$Group = $result->fetchAll('id');


	//print_r($Group);

	$i = 0;
	foreach($Group as $arr) {
	$data[$i]['value']=$arr['id'];
	$data[$i]['title']=$arr['name'];
$i++;
}
    return $data;




}



}

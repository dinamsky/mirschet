<?php

return array(
    'name' => 'Автоназначение категории контакта для вновь зарегистрированных пользователей',
	'description' => 'Переносит всех вновь зарегистрированных покупателей в выбранную категорию',
    'version' => '1.0',
    'img'  => 'img/addtogroup.png',
	'vendor' => '942951',
	'handlers' => array(
	'signup' => 'signup',	
),
	
	
	
	
);

//EOF

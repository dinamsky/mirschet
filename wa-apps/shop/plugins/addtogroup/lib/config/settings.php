<?php

return array(




    'vkljuchen' => array(
        'title' => _wp('Вкл./Выкл. плагина'),
        'description' => _wp('Чтобы плагин работал должна стоять галочка'),
        'control_type' => waHtmlControl::CHECKBOX,
        'value' => 1,
    ),


    'group' => array(
        'title' => _wp('Статус'),
        'description' => _wp('Выберете категорию, в которую попадут вновь зарегистрированные контакты'),
        'control_type' => waHtmlControl::RADIOGROUP,
		'options_callback' => array('shopAddtogroupPlugin', 'getGroup'),
        'value' => 1,
    ),



	
	
);	

<?php

return array(
	'engage_plugin' => array(
        'title' => _wp('Включить плагин'),
		'value' => 0,
        'description' => _wp('Если убрать галочку, плагин будет отключен и не будет обрабатывать заказы, автоматически генерировать и отзывать сертификаты'),
        'control_type' => waHtmlControl::CHECKBOX,
    ),
	'giftcardid' => array(
        'title' => _wp('ID товара-карты'),
		'value' => '',
        'description' => _wp('Укажите идентификатор товара, который является подарочной картой (зайдите в редактирование товара, и Вы увидите его в правом верхнем углу)'),
        'control_type' => waHtmlControl::INPUT,
    ),
	'frontend_title' => array(
        'title' => _wp('Заголовок (title) страницы подарочного сертификата'),
		'value' => 'Купить подарочный сертификат',
        'description' => _wp('Заголовок (title) специальной страницы /giftcard/, отвечающей за отдельное представление подарочного сертификата.'),
        'control_type' => waHtmlControl::INPUT,
    ),
	'frontend_keywords' => array(
        'title' => _wp('META Keywords страницы подарочного сертификата'),
		'value' => '',
        'description' => _wp('META Keywords специальной страницы /giftcard/, отвечающей за отдельное представление подарочного сертификата.'),
        'control_type' => waHtmlControl::INPUT,
    ),
	'frontend_description' => array(
        'title' => _wp('META Description страницы подарочного сертификата'),
		'value' => '',
        'description' => _wp('META Description специальной страницы /giftcard/, отвечающей за отдельное представление подарочного сертификата.'),
        'control_type' => waHtmlControl::TEXTAREA,
    ),
    );
<?php
return array(
    'system_text' => array(
        'title' => '',
        'description' => '',
        'control_type' => waHtmlControl::CUSTOM . ' ' . 'shopVkgoodsPlugin::getSettingsTextSystem'
    ),
    'app_site' => array(
        'title' => 'ID приложения ВКонтакте',
        'description' => 'Укажите ID приложения с типом \'Сайт\'. Используется для создания альбомов и размещения в них фотографий',
        'value' => '',
        'control_type' => waHtmlControl::INPUT
    ),
    'app_site_secret' => array(
        'title' => 'Защищенный ключ приложения',
        'description' => 'Укажите значение защищенного ключа приложения ВК с типом \'Сайт\'',
        'value' => '',
        'control_type' => waHtmlControl::PASSWORD
    ),
    'sketch_size' => array(
        'title' => 'Размер эскиза',
        'description' => 'Укажите размер эскиза изображения для публикации ВКонтакте. Убедитесь что заданный размер эскиза существует и используется в одной из тем оформления витрины вашего магазина. Эта информация обычно опубликована на странице настройки темы дизайна (Магазин -> Витрина -> Внешний вид)',
        'value' => '750',
        'control_type' => waHtmlControl::INPUT
    ),
    'upd_text' => array(
        'title' => '',
        'description' => '',
        'control_type' => waHtmlControl::CUSTOM . ' ' . 'shopVkgoodsPlugin::getSettingsTextUpdate'
    ),
    'auto_public' => array(
        'title' => 'Отложенные публикации вместе с автоматическим обновлением',
        'description' => 'Если опция включена - подготовленные отложенные публикации будут экспортированы в ВКонтакте при запуске автоматического обновления через планировщик заданий',
        'control_type' => waHtmlControl::CHECKBOX,
        'value' => 1
    ),
    'upd_null' => array(
        'title' => 'Публикация товаров которых нет в наличии',
        'description' => 'Если опция выключена - при публикации товары с остатком 0 будут пропущены. При обновлении публикаций, если товара нет в наличии - публикация будет удалена из ВКонтакте',
        'control_type' => waHtmlControl::CHECKBOX,
        'value' => 1
    ),
    'price_null' => array(
        'title' => 'Публикация товаров с нулевой ценой',
        'description' => 'Если опция включена, то товары с ценой равной 0 будут опубликованы. При этом цена будет установлена равной 1, т.к ВК не принимает к публикации товары с ценой равной нулю',
        'control_type' => waHtmlControl::CHECKBOX,
        'value' => 0
    ),
    'prd_set' => array(
        'title' => 'Помещать товары которые не удалось опубликовать в список',
        'description' => 'Если опция включена, товары при публикации которых возникли сбои или было отказано в публикации будут помещены в выбранный список',
        'control_type' => waHtmlControl::CHECKBOX,
        'value' => 0
    ),
    'no_check_update' => array(
        'title' => 'Не проверять актуальность публикации при обновлении',
        'description' => 'Если опция включена, то публикации будут обновляться в любом случае, вне зависимости от того, изменялся товар в магазине или нет',
        'control_type' => waHtmlControl::CHECKBOX,
        'value' => 0
    ),
    'set_id' => array(
        'title' => 'Список',
        'description' => 'Выберите список в который будут добавлены товары при публикации которых возникли сбои или ВК отказал в публикации таких товаров',
        'control_type' => waHtmlControl::SELECT,
        'options_callback' => array(
            'shopVkgoodsPlugin',
            'settingsSetList'
        )
    ),
    'br_replace_new' => array(
        'title' => 'Переводы строк',
        'description' => 'Укажите через запятую, какие закрывающие теги (или другие наборы символов) заменять на перевод строки в описаниях товара при экспорте в ВК',
        'value' => '<br>,</p>,</li>,</h1>',
        'control_type' => waHtmlControl::INPUT
    ),
    'format_price' => array(
        'title' => 'Выбор цены товара',
        'description' => 'В случае когда товару не удается сопоставить точную цену (например, при нескольких значениях наименований артикулов), будет применен выбранный вариант указания цены. Если у товара, например, всего один артикул и одно значение стоимости, то будет использовано это значение вне зависимости от данной настройки',
        'control_type' => waHtmlControl::RADIOGROUP,
        'options' => array(
            array(
                'value' => 0,
                'title' => 'Базовая цена',
                'description' => ''
            ),
            array(
                'value' => 1,
                'title' => 'Минимальная цена',
                'description' => ''
            ),
            array(
                'value' => 2,
                'title' => 'Максимальная цена',
                'description' => ''
            )
        )
    ),
    'features' => array(
        'title' => 'Характеристики',
        'description' => 'Выберите характеристики значения которых необходимо включать в описание товаров при использовании переменной %features%',
        'control_type' => waHtmlControl::GROUPBOX,
        'options_callback' => array(
            'shopVkgoodsPlugin',
            'settingsFeatures'
        )
    ),
    'fdelimeter' => array(
        'title' => 'Разделитель характеристик',
        'description' => 'Выберите разделитель характеристик',
        'control_type' => waHtmlControl::SELECT,
        'options' => array(
            array(
                'value' => ', ',
                'title' => 'Запятая',
                'description' => ''
            ),
            array(
                'value' => '; ',
                'title' => 'Точка с запятой',
                'description' => ''
            ),
            array(
                'value' => '<br>',
                'title' => 'Перевод строки',
                'description' => ''
            )
        )
    ),
    'desc_template' => array(
        'title' => 'Шаблон описания товара ВКонтакте',
        'description' => 'Данный щаблон будет использоваться при автоматическом обновлении, а также предлагаться при создании новых публикаций. При публикации товаров шаблон можно будет изменить.
				Для осуществления экспорта необходио авторизоваться в ВКонтакте на странице плагина в резеделе "<a href="?action=importexport#/vkgoods/">Импорт/Экспорт</a>" Магазина.
            В шаблоне описания товара допускается использование произвольного текста и следующих переменных:
            <ul>
                <li><b>%url%</b> - ссылка на товар на выбранной витрине магазина</li>
                <li><b>%name%</b> - название товара</li>
                <li><b>%tags%</b> - тэги товара (преобразуются в хэштеги ВК)</li>
                <li><b>%summary%</b> - краткое описание товара</li>
				<li><b>%features%</b> - значения характеристик товара</li>
                <li><b>%desc%</b> - полное описание товара</li>
                <li><b>%id%</b> - ID товара</li>
                <li><b>%sku%</b> - Название основного артикула товара</li>
                <li><b>%sku_code%</b> - Код основного артикула товара</li>
            </ul>',
        'value' => 'Посмотреть: %url% %desc% %tags%',
        'control_type' => waHtmlControl::TEXTAREA
    ),
    'token' => array(
        'title' => '',
        'description' => '',
        'value' => '',
        'control_type' => waHtmlControl::HIDDEN
    ),
    'vk_user_id' => array(
        'title' => '',
        'description' => '',
        'value' => '',
        'control_type' => waHtmlControl::HIDDEN
    ),
    'last_sync' => array(
        'title' => '',
        'description' => '',
        'value' => '',
        'control_type' => waHtmlControl::HIDDEN
    )
);
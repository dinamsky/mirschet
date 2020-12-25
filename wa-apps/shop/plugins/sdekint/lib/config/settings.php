<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @version 2.3.0
 * @copyright Serge Rodovnichenko, 2015-2016
 * @license
 */
return array(
    'api_auth'               => array(
        'title'        => 'ID аккаунта',
        'description'  => 'Ключ для доступа к API. Выдается менеджером СДЭК по запросу',
        'control_type' => waHtmlControl::INPUT,
        'value'        => '',
        'subject'      => 'general'
    ),
    'api_key'                => array(
        'title'        => 'Пароль к API',
        'description'  => 'Secure Password для доступа к API. Выдается менеджером СДЭК по запросу. Не совпадает с паролем к ЛК СДЭК!',
        'control_type' => waHtmlControl::INPUT,
        'value'        => '',
        'subject'      => 'general'
    ),
    'api_timeout'            => array(
        'title'        => 'Таймаут, с',
        'description'  => 'Время в секундах, сколько плагин ждет ответа от сервера СДЭК. <b>Не меняйте, если не знаете, зачем это нужно</b>',
        'control_type' => waHtmlControl::INPUT,
        'class'        => 'short numerical',
        'value'        => '20',
        'subject'      => 'general'
    ),
    'sender_city'            => array(
        'title'        => 'Город отправки',
        'control_type' => 'CitySelectorMulti',
        'value'        => array('id' => 44, 'name' => 'Москва'),
        'subject'      => 'general'
    ),
    // @deprecated
    'seller_name'            => array(
        'title'        => 'Наименование отправителя',
        'description'  => 'Название реального отправителя',
        'control_type' => waHtmlControl::INPUT,
        'value'        => 'Интернет-магазин',
        'subject'      => 'general'
    ),
    'sellers' => ['value' => [
        ['address' => '', 'name' => '', 'inn' => '', 'phone' => '', 'ownership_form' => 0, '_is_default' => true]
    ]],
    'service'                => array(
        'title'        => 'Услуги по умолчанию',
        'description'  => 'Услуги, которые должны быть включены по умолчанию',
        'control_type' => waHtmlControl::GROUPBOX,
        'value'        => array(),
        'options'      => array(
            array('value' => 3, 'title' => 'Доставка в выходной день'),
            array('value' => 30, 'title' => 'Примерка на дому'),
            array('value' => 36, 'title' => 'Частичная доставка'),
            array('value' => 37, 'title' => 'Осмотр вложения')
        ),
        'subject'      => 'general'
    ),
    'track_suggest'          => array(
        'title'        => 'Автоподстановка трек-номера',
        'description'  => 'Возможность подставлять трек-номер при отправке',
        'control_type' => waHtmlControl::CHECKBOX,
        'value'        => false,
        'subject'      => 'general'
    ),
    'tariffs'                => array(
        'title'            => 'Тарифы',
        'description'      => 'Используемые тарифы и их предпочитаемый порядок',
//        'control_type' => waHtmlControl::GROUPBOX,
        'control_type'     => 'SortableGroupbox',
        'value'            => array(),
        'options_callback' => array('shopSdekintPluginSettingsAction', 'tariffOptions'),
        'subject'          => 'general'
    ),
    'appraised_price'        => array(
        'title'        => 'Оценочная стоимость',
        'description'  => 'Какую оценочную стоимость подставлять в накладную',
        'control_type' => waHtmlControl::SELECT,
        'value'        => 'default',
        'options'      => array(
            'default'    => 'Розничная цена товара',
            'discounted' => 'Розничная цена с учетом скидки',
            'purchase'   => 'Закупочная цена',
            'fix'        => 'Фиксированное значение'
        ),
        'subject'      => 'general',
        'class'        => 'appraised_price'
    ),
    'fix_appraised_price'    => array(
        'title'        => 'Значение оценочной стоимости',
        'description'  => 'Значение, которое нужно подставлять в качестве оценочной стоимости каждого товара, если ' .
            'выбрана фиксированная оценочная стоимость',
        'control_type' => waHtmlControl::INPUT,
        'value'        => '1',
        'subject'      => 'general',
        'class'        => 'fix_appraised_price'
    ),
    'ru_vat_delivery'        => array(
        'title'        => 'НДС услуги доставки по РФ',
        'description'  => 'Для доставки в город на территории РФ долежн быть указан НДС',
        'control_type' => waHtmlControl::SELECT,
        'options'      => array('VATX' => 'Без НДС', 'VAT0' => '0%', 'VAT10' => '10%', 'VAT18' => '18%', 'VAT20' => '20%'),
        'value'        => 'VATX',
        'subject'      => 'general'
    ),
    'ru_vat_product'         => array(
        'title'        => 'Нулевая ставка налога',
        'description'  => 'Если в заказе указан размер налога &laquo;0&raquo;, то плагин подставит указанное здесь значение налога',
        'control_type' => waHtmlControl::SELECT,
        'value'        => 'VATX',
        'options'      => array('VATX' => 'Без НДС', 'VAT0' => 'НДС 0%'),
        'subject'      => 'general'
    ),
    'contract_currency'      => array(
        'title'            => 'Валюта договора',
        'description'      => 'В этой валюте будет указываться оценочная стоимость товара',
        'control_type'     => waHtmlControl::SELECT,
        'value'            => 'RUB',
        'options_callback' => array('shopSdekintPluginSettingsAction', 'currenciesOptions'),
        'subject'          => 'general'
    ),
    'ru_phone_normalize'     => array(
        'title'        => 'Нормализовать номер телефона клиента',
        'description'  => 'Только РФ. Плагин попытается оставить только цифры и преобразовать номер к виду 7XXXXXXXXXX',
        'control_type' => waHtmlControl::CHECKBOX,
        'value'        => '0',
        'subject'      => 'general'
    ),
    'print_version'          => array(
        'title'        => 'Версия для печати',
        'description'  => 'Что нужно показывать или скрывать в карточке заказа в в режиме "Версии для печати"',
        'control_type' => waHtmlControl::SELECT,
        'value'        => 'show_all',
        'options'      => array(
            'show_all'        => 'Показать все',
            'dispatch_number' => 'Только номер накладной',
            'none'            => 'Скрыть все'
        ),
        'subject'      => 'general'
    ),
    'overwrite_paid'         => array(
        'title'        => 'Ручная установка признака оплаты',
        'description'  => 'Включите, если нужно самостоятельно указывать признак оплаты. По умолчанию плагин использует системный признак оплаты',
        'control_type' => waHtmlControl::CHECKBOX,
        'value'        => '0',
        'subject'      => 'general'
    ),
    'front_my_order_point'   => array(
        'title'        => 'Информация о ПВЗ для покупателя',
        'description'  => 'В Личном Кабинете покупателя, при просмотре заказа, можно выводить информацию о пункте ' .
            'выдачи или постамате, на доставку в который оформлена накладная. Работает только при оформлении ' .
            'накладной через плагин. Информация для заказов в состоянии &quot;Выполнен&quot;, &quot;Отменен&quot;, ' .
            '&quot;Возврат&quot; <b>не показывается</b>. Опционально можно показать еще карту с примерным ' .
            'расположением пункта выдачи (постомата)',
        'control_type' => waHtmlControl::SELECT,
        'value'        => 'off',
        'options'      => array('off' => 'Не показывать', 'info' => 'Информация', 'infomap' => 'Информация и карта'),
        'subject'      => 'general'
    ),
    'auto_change_states'     => array(
        'title'        => 'Автоматическая смена статусов',
        'description'  => 'Включение и выключение проверки и автоматической смены статусов заказов специальным фоновым обработчиком',
        'control_type' => waHtmlControl::SELECT,
        'value'        => '0',
        'options'      => array(
            '0'     => 'Выключено',
            '3600'  => 'Каждый час',
            '7200'  => 'Каждые 2 часа',
            '10800' => 'Каждые 3 часа',
            '21600' => 'Каждые 6 часов',
            '43200' => 'Каждые 12 часов'
        ),
        'subject'      => 'general',
    ),
    'schedule_enabled'       => array(
        'title'        => 'Расписание доставки',
        'description'  => '<i class="icon10 exclamation"></i> ' .
            '<b>В договоре с ИМ</b> определяется условие кто именно, ИМ или СДЭК, запрашивает у ' .
            'получателя расписание для доставки/забора отправления. В случае, если ИМ самостоятельно запрашивает ' .
            'расписание, включите этот пункт и в оформлении накладной появится еще один раздел',
        'control_type' => waHtmlControl::CHECKBOX,
        'value'        => 0,
        'subject'      => 'general'
    ),
    'copy_count'             => array(
        'title'        => 'Количество накладных',
        'description'  => 'Сколько копий накладных выводить в PDF. Рекомкндуется не менее 2',
        'control_type' => waHtmlControl::INPUT,
        'value'        => '4',
        'class'        => 'short numerical',
        'subject'      => 'general'
    ),
    'barcode_format'         => array(
        'title'        => 'Формат страницы для печати штрихкодов',
        'control_type' => waHtmlControl::SELECT,
        'value'        => 'A4',
        'options'      => array(
            ['value' => 'A4', 'title' => 'A4'],
            ['value' => 'A5', 'title' => 'A5'],
            ['value' => 'A6', 'title' => 'A6']
        ),
        'subject'      => 'general'
    ),
    'barcode_copy_count'     => array(
        'title'        => 'Количество копий штрихкодов',
        'description'  => 'Сколько копий штрихкодов выводить в PDF',
        'control_type' => waHtmlControl::INPUT,
        'value'        => '1',
        'class'        => 'short numerical',
        'subject'      => 'general'
    ),
    'debug_requests'         => array(
        'title'        => 'Логировать запросы и ответы сервера',
        'description'  => 'В режиме отладки записывать запросы и ответы сервера СДЭК в лог-файл shop/plugins/sydsek.log',
        'control_type' => waHtmlControl::CHECKBOX,
        'value'        => '0',
        'subject'      => 'general'
    ),
    'default_weight'         => array(
        'title'        => 'Вес товара по умолчанию (кг.)',
        'description'  => 'Если у товара не указан вес, какой нужно подставлять в накладную?',
        'control_type' => waHtmlControl::INPUT,
        'class'        => 'short numerical',
        'value'        => '0.1',
        'subject'      => 'general'
    ),
    'package_size'           => array(
        'title'        => 'Размеры упаковки',
        'description'  => 'Можно указать размеры стандартных упаковок. Размер будет подставляться в зависимости от веса заказа. Его всегда можно изменить вручную при оформлении накладной',
        'value' => ['table' => [['min_weight' => 0, 'width' => 20, 'height' => 20, 'length' => 20]]],
        'subject'      => 'general'
    ),
    'address_fields_section' => array(
        'value'                      => 'Поля адреса',
        'description'                => 'Если для номера дома и квартиры/офиса у вас добавлены свои собственные, нештатные поля адреса, ' .
            'укажите их в настройках ниже и плагин будет использовать их значения для подставновки в накладную. ' .
            '<b>Можно оставить пустыми</b>',
        'control_type'               => waHtmlControl::TITLE,
        'custom_description_wrapper' => '<p style="font-size: 85%%">%s</p>',
        'custom_control_wrapper'     => '<div class="hr field" style="margin-top: 1em;background-color: #fafafa; padding: 0.5em 3px">%2$s%3$s</div>',
        'subject'                    => 'general'
    ),
    'address_field_street'   => array(
        'title'            => 'Поле Улица',
        'description'      => 'Выбреите поле адреса, в котором сохраняется название улицы',
        'control_type'     => waHtmlControl::SELECT,
        'value'            => '',
        'options_callback' => array($this, 'optionsAddressFields'),
        'subject'          => 'general'
    ),
    'address_field_house'    => array(
        'title'            => 'Поле Номер дома',
        'description'      => 'Выбреите поле адреса, в котором сохраняется номер дома',
        'control_type'     => waHtmlControl::SELECT,
        'value'            => '',
        'options_callback' => array($this, 'optionsAddressFields'),
        'subject'          => 'general'
    ),
    'address_field_flat'     => array(
        'title'            => 'Поле Номер квартиры',
        'description'      => 'Выбреите поле адреса, в котором сохраняется номер квартиры/офиса',
        'control_type'     => waHtmlControl::SELECT,
        'value'            => '',
        'options_callback' => array($this, 'optionsAddressFields'),
        'subject'          => 'general'
    ),
    '_iddqd_'                => array(
        'control_type' => waHtmlControl::HIDDEN,
        'value'        => '0',
        'subject'      => 'general'
    ),
    'ship_methods'           => array(
        'title'            => 'Методы доставки',
        'description'      => 'Укажите для каких методов доставки разрешен этот плагин',
        'control_type'     => 'AssocGroupbox',
        'value'            => array(),
        'options_callback' => array('shopSdekintPluginSettingsAction', 'deliveryMethodsOptions'),
        'subject'          => 'delivery_methods'
    ),
    'use_sku_name'           => array(
        'title'        => 'Наименования SKU',
        'description'  => 'Если указаны наименования SKU, использовать их вместо ID. Возможна проблема с уникальностью артикулов!',
        'control_type' => waHtmlControl::CHECKBOX,
        'value'        => 0,
        'subject'      => 'item_list'
    ),
    'drop_quot_amp'          => array(
        'title'        => 'Удалять кывчки и амперсанды',
        'description'  => 'Если включено, из названий товаров будут удаляться символы двойных кавычке <b>&quot;</b> и амперсандов <b>&amp;</b>. А то при передаче в СДЭК они выглядят некрасиво в накладной',
        'control_type' => waHtmlControl::CHECKBOX,
        'value'        => '0',
        'subject'      => 'item_list'
    ),
    'item_template'          => array(
        'title'        => 'Шаблон замены наименований',
        'description'  => 'Оставьте пустым, если названия заменять не нужно. Можно использовать переменную {$sku}, ' .
            'вместо которой будет подставлено наименование или ID SKU (см. предыдущую настройку)',
        'control_type' => waHtmlControl::INPUT,
        'value'        => '',
        'subject'      => 'item_list'
    ),
    'reduce_count'           => array(
        'title'        => 'Порог детализации',
        'description'  => 'Число наименований в накладной, которое делает ее "слишком длинной" и начиная с которого ' .
            'все наименования будут автоматически заменяться на одну строку. Оставьте пустым или укажите 0, чтобы выключить.',
        'control_type' => waHtmlControl::INPUT,
        'value'        => '0',
        'subject'      => 'item_list'
    ),
    'reduce_template'        => array(
        'title'        => 'Общее наименование',
        'description'  => 'Название товара, заменяющего все строки в соответствии с предыдущей настройкой. Артикул у него всегда будет "001"',
        'control_type' => waHtmlControl::INPUT,
        'value'        => 'Товар интернет-магазина',
        'subject'      => 'item_list'
    ),
);

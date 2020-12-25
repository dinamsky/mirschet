<?php
return array(
    'types'  => array(

        'simple'       => array(
            'title'   => 'Упрощенное описание',
            'fields' => array(
                'id'                    => true,
                'url'                   => true,
                'price'                 => true,
                'compare_price'         => true,
                'currencyId'            => true,
                'categoryId'            => true,
                'picture'               => false,
                'title'                  => true,
                'brand'                => false,
                'g:color'                => false,
                'availability'            => false,
                'description'           => false,
                'g:google_product_category' => false,
                'g:material' 							 =>	false,
                'g:product_type'           => false,
                'GTIN'                     => false,
                'mpn'                      => false,
                'custom_label_0'           => false,
                'custom_label_1'           => false,
                'custom_label_2'           => false,
                'custom_label_3'           => false,
                'custom_img'                   => false,
                'param.*'               => false,
            ),
        ),
        'brand.model' => array(
            'title'   => 'Произвольный товар (brand.model)',
            'fields' => array(
                'id'                    => true,
                'url'                   => true,
                'price'                 => true,
                'currencyId'            => true,
                'categoryId'            => true,
                'market_category'       => false,
                'picture'               => true,
                /**
                 *
                 * Ссылка на картинку соответствующего товарного предложения. Недопустимо давать ссылку на «заглушку»,
                 * т.е. на страницу, где написано «картинка отсутствует», или на логотип магазина.
                 * Максимальная длина URL — 512 символов.
                 *
                 * Для товарных предложений, относящихся к категории «Одежда и обувь», является обязательным элементом.
                 * Для всех остальных категорий – необязательный элемент.
                 **/
                'store'                 => false,
                'pickup'                => false,
                'delivery'              => false,
                'local_delivery_cost'   => false,
                'typePrefix'            => false,
                /**
                 *
                 * Группа товаров/категория.
                 *
                 * Необязательный элемент.
                 **/
                'brand'                => true,
                'availability'            => false,
                'model'                 => true,
                'description'           => false,
                'sales_notes'           => false,
                'manufacturer_warranty' => false,
                'seller_warranty'       => false,
                'country_of_origin'     => true,
                'downloadable'          => false,
                'adult'                 => false,
                'age'                   => false,
                'barcode'               => false,
                'cpa'                   => false,
                /**
                 *
                 * Элемент предназначен для управления участием товарных предложений в программе «Покупка на Маркете».
                 *
                 * Необязательный элемент.
                 **/
                'rec'                   => false,
                /**
                 *
                 * Элемент обозначает товары, рекомендуемые для покупки вместе с текущим.
                 *
                 * Необязательный элемент.
                 **/
                'expiry'                => false,
                'weight'                => false,
                'dimensions'            => true,
                'param.*'               => false,
                /**
                 *
                 * Элемент предназначен для указания характеристик товара. Для описания каждого параметра используется
                 * отдельный элемент <param>.
                 *
                 * Необязательный элемент. Элемент <offer> может содержать несколько элементов <param>.
                 */
            ),
        ),
        'book'         => array(
            'title'   => 'Книги (book)',
            'fields' => array(
                'available'           => true,
                'id'                  => true,
                'url'                 => true,
                'price'               => true,
                'currencyId'          => true,
                'categoryId'          => true,
                'market_category'     => false,
                'picture'             => false,
                'store'               => false,
                'pickup'              => false,
                'delivery'            => false,
                'local_delivery_cost' => false,
                'author'              => false,
                'title'                => true,
                'publisher'           => false,
                'series'              => false,
                'year'                => false,
                'ISBN'                => false,
                'volume'              => false,
                'part'                => false,
                'language'            => false,
                'binding'             => false,
                'page_extent'         => false,
                'table_of_contents'   => false,
                'description'         => false,
                'downloadable'        => false,
                'age'                 => false,
                'cpa'                 => false,
            ),
        ),
        'audiobook'    => array(
            'title'   => 'Аудиокниги (audiobook)',
            'fields' => array(
                'available'         => true,
                'id'                => true,
                'url'               => true,
                'price'             => true,
                'currencyId'        => true,
                'categoryId'        => true,
                'market_category'   => false,
                'picture'           => false,
                'author'            => false,
                'title'              => false,
                'publisher'         => false,
                'series'            => false,
                'year'              => false,
                'ISBN'              => false,
                'volume'            => false,
                'part'              => false,
                'language'          => false,
                'table_of_contents' => false,
                'performed_by'      => false,
                'performance_type'  => false,
                'storage'           => false,
                'format'            => false,
                'recording_length'  => false,
                'description'       => false,
                'downloadable'      => false,
                'age'               => false,
                'cpa'               => false,
            ),
        ),
        'artist.title' => array(
            'title'   => 'Музыкальная и видео продукция (artist.title)',
            'fields' => array(
                'available'       => true,
                'id'              => true,
                'url'             => true,
                'price'           => true,
                'currencyId'      => true,
                'categoryId'      => true,
                'market_category' => false,
                'picture'         => false,
                'store'           => false,
                'pickup'          => false,
                'delivery'        => false,
                'artist'          => false,
                'title'           => true,
                'year'            => false,
                'media'           => false,
                'starring'        => false,
                /**
                 * Актеры.
                 **/
                'director'        => false,
                /**
                 * Режиссер.
                 **/
                'originaltitle'    => false,
                /**
                 * Оригинальное название.
                 **/
                'country'         => false,
                /**
                 * Страна.
                 */

                'description'     => false,
                'adult'           => false,
                'age'             => false,
                'barcode'         => false,
                'GTIN'         => false,
                'cpa'             => false,
            ),
        ),
        'tour'         => array(
            'title'   => 'Туры (tour)',
            'fields' => array(
                'available'       => true,
                'id'              => true,
                'url'             => true,
                'price'           => true,
                'currencyId'      => true,
                'categoryId'      => true,
                'market_category' => false,
                'picture'         => false,
                'store'           => false,
                'pickup'          => false,
                'delivery'        => false,
                'worldRegion'     => false,
                /**
                 * Часть света.
                 **/
                'country'         => false,
                /**
                 * Страна.
                 **/
                'region'          => false,
                /**
                 * Курорт или город.
                 **/
                'days'            => true,
                'dataTour'        => false,
                'title'            => true,
                'hotel_stars'     => false,
                'room'            => false,
                'meal'            => false,
                'included'        => true,
                'transport'       => true,
                'description'     => false,
                'age'             => false,
            ),
        ),
        'event-ticket' => array(

            'title'   => 'Билеты на мероприятие (event-ticket)',
            'fields' => array(
                'available'       => true,
                'id'              => true,
                'url'             => true,
                'price'           => true,
                'currencyId'      => true,
                'categoryId'      => true,
                'market_category' => false,
                'picture'         => false,
                'store'           => false,
                'pickup'          => false,
                'delivery'        => false,
                'title'            => true,
                'place'           => true,
                'hall'            => false,
                /**
                 * Ссылка на изображение с планом зала.
                 **/
                'date'            => true,
                'is_premiere'     => false,
                /**
                 * Признак премьерности мероприятия.
                 **/
                'is_kids'         => false,
                /**
                 * Признак детского мероприятия.
                 **/
                'age'             => false,
            ),
        ),
    ),
    'fields' => array(

        'id'                    => array(
            'type'        => 'fixed',
            'title'        => 'идентификатор товарного предложения',
            'description' => '',
            'attribute'   => true,
            'source'      => 'field:id',
            'field'       => 'offer',
        ),
        'url'                   => array(
            'type'        => 'fixed',
            'title'        => 'URL — адрес страницы товара',
            'description' => '',
            'format'      => '%0.512s',
            'source'      => 'field:frontend_url',
        ),
        'price'                 => array(
            'type'        => 'fixed',
            'title'        => 'Цена',
            'description' => 'Цена товарного предложения округляеся и выводится в зависимости от настроек пользователя',
            //'format'      => '%0.2f',
            'source'      => 'field:price',
        ),
        'compare_price'                 => array(
            'type'        => 'fixed',
            'title'        => 'Зачёркнутая цена',
            'description' => 'Цена товарного предложения округляеся и выводится в зависимости от настроек пользователя',
            'format'      => '%0.2f',
            'source'      => 'field:compare_price',
        ),
        'currencyId'            => array(
            'type'        => 'fixed',
            'title'        => 'Идентификатор валюты товара',
            'description' => 'Для корректного отображения цены в национальной валюте необходимо использовать идентификатор с соответствующим значением цены',
            'values'      => array(
                'RUB',
                'USD',
                'UAH',
                'KZT',
                'BYR',
                'EUR',
            ),
            'source'      => 'field:currency',
        ),
        'categoryId'            => array(
            'type'        => 'fixed',
            'title'        => 'Идентификатор категории товара ',
            'description' => '(целое число не более 18 знаков). Товарное предложение может принадлежать только к одной категории',
            'source'      => 'field:category_id',
        ),
/*        'market_category'       => array(
            'type'        => 'fixed',
            'title'        => 'Идентификатор категории товара ',
            'description' => '',
            'source'      => 'field:market_category',
        ),*/
        'picture'               => array(
            'type'   => 'fixed',
            'title'   => 'Ссылка на изображение соответствующего товарного предложения',
            'source' => 'field:images',
        ),
        'downloadable'          => array(
            'type'        => 'fixed',
            'title'        => 'Цифровой товар',
            'description' => 'Обозначение товара, который можно скачать',
            'source'      => 'field:file_title',
        ),
        /**
         * adjustable
         */


        'brand'                => array(
            'type'        => 'adjustable',
            'title'        => 'Производитель',
            'description' => 'Если есть возможнось, то выбираем Характеристику "Бренд", если такой возможности нет, то поле "пропустить"',
        ),
        'g:color'                => array(
            'type'        => 'adjustable',
            'title'        => 'Цвет',
            'description' => 'Выбираем Характеристику "Цвет", если такой возможности нет, то поле "пропустить"',
        ),
        'availability'            => array(
            'type'        => 'adjustable',
            'title'        => 'Наличие единиц',
            'description' => 'Выбираем Основное свойство товара "в наличии"',
        ),
        'model'                 => array(
            'type'        => 'adjustable',
            'title'        => 'Модель',
            'description' => '',
        ),
        'title'                 => array(
            'type'        => 'adjustable',
            'title'        => 'Название',
            'description' => 'Название фильма или альбома',
            'source'      => 'field:title',
        ),
        'title'                  => array(
            'type'        => 'adjustable',
            'title'        => 'Название',
            'description' => 'Выбираем Основное свойство товара "Наименование"',
            'source'      => 'field:title',
        ),
        'artist'                => array(
            'type'   => 'adjustable',
            'title'   => 'Исполнитель',
            'source' => 'feature:artist',
        ),
        'author'                => array(
            'type'   => 'adjustable',
            'title'   => 'Автор произведения',
            'source' => 'feature:author'
        ),
        'days'                  => array(
            'type'   => 'adjustable',
            'title'   => 'Количество дней тура',
            'source' => '',
        ),
        'place'                 => array(
            'type'        => 'adjustable',
            'title'        => 'Место проведения',
            'description' => '',
            'format'      => '',
            'source'      => 'feature:place',
        ),
        'date'                  => array(
            'type'        => 'adjustable',
            'title'        => 'Дата и время сеанса',
            'description' => '',
            'format'      => '',
            'source'      => 'feature:date',
        ),
        'description'           => array(
            'type'        => 'adjustable',
            'title'        => 'Описание',
            'description' => 'Выбираем подходящее на Ваш взгляд описание, тоесть "описание" или "краткое описание"',
            'source'      => 'field:summary',
        ),
        'publisher'             => array(
            'type'   => 'adjustable',
            'title'   => 'Издательство',
            'source' => 'feature:publisher',
        ),
        'typePrefix'            => array(
            'type'   => 'adjustable',
            'title'   => 'Группа товаров/категория',
            'source' => 'field:type_id',
        ),
        'seller_warranty'       => array(
            'type'        => 'adjustable',
            'title'        => 'Гарантия продавца',
            'description' => 'Возможные пользовательские значения: Выбираем "пропустить"
1) false — товар не имеет гарантию продавца;
2) true — товар имеет гарантию продавца;
3) указание срока гарантии в формате ISO 8601, например: P1Y2M10DT2H30M;
4) указание срока гарантии в чисое дней;
Поддерживаются числовые данные — простое число определяет срок гарантии в днях, либо с учетом размерности характеристики типа «Время».
Остальные типы данных приводятся к значениям true/false.',
            'values'      => array(
                false => 'false',
                true  => 'true',
            ),
        ),
        'expiry'                => array(
            'type'        => 'adjustable',
            'title'        => 'Срок годности/службы',
            'description' => '
Выбираем "пропустить". Возможные пользовательские значения:
1) указание срока гарантии в формате ISO 8601, например: P1Y2M10DT2H30M
2) указание числа дней
Поддерживаются числовые данные — простое число определяет срок гарантии в днях, либо с учетом размерности характеристики типа «Время».',
            'source'      => 'feature:expiry',
        ),
        
        'GTIN'               => array(
            'type'        => 'adjustable',
            'title'        => 'GTIN',
            'description' => 'Выбираем поле GTIN если оно у Вас есть. gtin: глобальный номер товара (UPC, EAN, JAN, ISBN)',
            'source'      => 'feature:GTIN'
        ), 
        'mpn'               => array(
            'type'        => 'adjustable',
            'title'        => 'mpn',
            'description' => 'mpn: уникальный номер продукта в системе нумерации, используемой его изготовителем.',
            'source'      => 'feature:mpn'
        ),


        'g:product_type'               => array(
            'type'        => 'adjustable',
            'title'        => 'g:product_type',
            'description' => 'Позволяет по-своему классифицировать товары в фиде',

        ),

        'g:google_product_category'               => array(
            'type'        => 'adjustable',
            'title'        => 'google_product_category',
            'description' => 'Указать категорию товара по классификации Google.',
            'source'      => 'feature:g:google_product_category'
        ),
        'g:material'               => array(
            'type'        => 'adjustable',
            'title'        => 'material',
            'description' => 'Материал, из которого изготовлен товар',
            'source'      => 'feature:g:material'
        ),

        'custom_label_0'                => array(
            'type'        => 'adjustable',
            'title'        => 'Метка продовца 0',
            'description' => 'Ярлык, по которому можно группировать товары в рамках кампании. Пользователям этот атрибут не виден. Не более 100 символов',
        ),
        'custom_label_1'                => array(
            'type'        => 'adjustable',
            'title'        => 'Метка продовца 1',
            'description' => 'Ярлык, по которому можно группировать товары в рамках кампании. Пользователям этот атрибут не виден. Не более 100 символов',
        ),
        'custom_label_2'                => array(
            'type'        => 'adjustable',
            'title'        => 'Метка продовца 2',
            'description' => 'Ярлык, по которому можно группировать товары в рамках кампании. Пользователям этот атрибут не виден. Не более 100 символов',
        ),
        'custom_img'                => array(
            'type'        => 'adjustable',
            'title'        => 'свой путь до картинки',
            'description' => 'Свой абсолютный путь до картинки "https://site.ru/product.jpg"',
        ),

        'series'                => array(
            'type' => 'adjustable',
            'title' => 'Серия',
        ),
        'year'                  => array(
            'type'   => 'adjustable',
            'title'   => 'Год издания',
            'format' => '%d',
            'source' => 'feature:imprint_date',
        ),
        'ISBN'                  => array(
            'type'   => 'adjustable',
            'title'   => 'Код книги',
            'source' => 'feature:isbn',
        ),
        'description.*book'     => array(
            'type' => 'adjustable',
            'title' => 'Аннотация к книге',
        ),
        'volume'                => array(
            'type' => 'adjustable',
            'title' => 'Номер тома',
        ),
        'part'                  => array(
            'type' => 'adjustable',
            'title' => 'Номер тома',
        ),
        'language'              => array(
            'type'   => 'adjustable',
            'title'   => 'Язык произведения',
            'source' => 'feature:language',
        ),
        'performed_by'          => array(
            'type' => 'adjustable',
            /**
             *  Если их несколько, перечисляются через запятую
             **/
            'title' => 'Исполнитель',
        ),
        'performance_type'      => array(
            'type' => 'adjustable',
            'title' => 'Тип аудиокниги',
        ),
        'format'                => array(
            'type' => 'adjustable',
            'title' => 'Формат аудиокниги',
        ),
        'storage'               => array(
            'type' => 'adjustable',
            'title' => 'Носитель',
        ),
        'recording_length'      => array(
            'type' => 'adjustable',
            /**
             *  задается в формате mm.ss (минуты.секунды).
             */
            'title' => 'Время звучания',
        ),
        'binding'               => array(
            'type' => 'adjustable',
            'title' => 'Переплет',
        ),
        'page_extent'           => array(
            'type' => 'adjustable',
            'title' => 'Количествово страниц в книге',
        ),
        'table_of_contents'     => array(
            'type'        => 'adjustable',
            'title'        => 'Оглавление',
            'description' => 'Выводится информация о наименованиях произведений, если это сборник рассказов или стихов. Выбираем "пропустить"',
        ),
        'weight'                => array(
            'type'        => 'adjustable',
            'title'        => 'Вес товара',
            'description' => 'Вес указывается в килограммах с учетом упаковки. Выбираем "пропустить"',
            'format'      => '%0.4f',
            'source'      => 'feature:weight',
        ),
        'dimensions'            => array(
            'type'        => 'adjustable',
            'title'        => 'Габариты товара ',
            'description' => 'габариты товара (длина, ширина, высота) в упаковке. Выбираем "пропустить"',
            'format'      => '%s',
            'source'      => '',
        ),
        'media'                 => array(
            'type'        => 'adjustable',
            'title'        => 'Носитель',
            'description' => '(CD, DVD, ...)',
        ),
        'starring'              => array(
            'type'   => 'adjustable',
            'title'   => 'Актеры',
            'source' => 'feature:starring',
        ),
        'director'              => array(
            'type'   => 'adjustable',
            'title'   => 'Режиссер',
            'source' => 'feature:director',
        ),
        'originaltitle'          => array(
            'type'   => 'adjustable',
            'title'   => 'Оригинальное название',
            'source' => '',
        ),
        'country'               => array(
            'type'   => 'adjustable',
            'title'   => 'Страна',
            'source' => '',
        ),
        'worldRegion'           => array(
            'type'   => 'adjustable',
            'title'   => 'Часть света',
            'source' => '',
        ),
        'region'                => array(
            'type'   => 'adjustable',
            'title'   => 'Курорт или город',
            'source' => '',
        ),
        'dataTour'              => array(
            'type'        => 'adjustable',
            'title'        => 'Даты заездов',
            'description' => '',
            'format'      => '',
            'source'      => '',
        ),
        'hotel_stars'           => array(
            'type'        => 'adjustable',
            'title'        => 'Звезды отеля',
            'description' => '',
            'format'      => '',
            'source'      => 'feature:hotel_stars',
        ),
        'room'                  => array(
            'type'        => 'adjustable',
            'title'        => 'Тип комнаты',
            'description' => '(SNG, DBL, ...)',
            'format'      => '',
            'source'      => 'feature:room',
        ),
        'meal'                  => array(
            'type'        => 'adjustable',
            'title'        => 'Тип питания',
            'description' => '(All, HB, ...)',
            'format'      => '',
            'source'      => 'feature:meal',
        ),
        'included'              => array(
            'type'        => 'adjustable',
            'title'        => 'Что включено в стоимость тура',
            'description' => '',
            'format'      => '',
            'source'      => 'feature:included',
        ),
        'transport'             => array(
            'type'        => 'adjustable',
            'title'        => 'Транспорт',
            'description' => '',
            'format'      => '',
            'source'      => 'feature:transport',
        ),
        'hall'                  => array(
            'type'        => 'adjustable',
            'title'        => 'Ссылка на изображение с планом зала',
            'description' => '',
            'format'      => '',
            'source'      => '',
        ),
        'is_premiere'           => array(
            'type'        => 'adjustable',
            'title'        => 'Премьера',
            'description' => 'Признак примьерности мероприятия',
            'format'      => '',
            'source'      => '',
        ),
        'is_kids'               => array(
            'type'        => 'adjustable',
            'title'        => 'Детское мероприятие',
            'description' => 'Признак детского мероприятия.',
            'format'      => '',
            'source'      => '',
        ),
        'param'                 => array(
            'type'        => 'adjustable',
            'title'        => '<param>',
            'description' => 'Дополнительные произвольные характеристики товара. Если тип характеристики магазина не имеет единицы измерения, но ее необходимо передать в Google, то можно задать название единицы измерения (параметр unit) в названии характеристики в скобках, например, «Вес (кг)»',
        ),

    )
);

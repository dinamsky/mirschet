<?php
/**
 * Created by PhpStorm.
 * User: onehalf
 * Date: 08.03.16
 * Time: 20:48
 */
return array(
    'full_metod_root' => array(
        'title' => _wp('To use more exact method of search of items for without category'),
        'description' => '<span style="color:red">' . _wp('Attention!!! The raised load of the server. What in case of a large amount of goods/categories, can negatively to affect the speed of operation of your server') . '</span>',
        'control_type' => waHtmlControl::CHECKBOX,
        'value' => 0,
    ),
    'full_metod_image' => array(
        'title' => _wp('To use more exact method of search of items for without pictures'),
        'description' => '<span style="color:red">' . _wp('Attention!!! The raised load of the server. What in case of a large amount of goods/categories, can negatively to affect the speed of operation of your server') . '</span>',
        'control_type' => waHtmlControl::CHECKBOX,
        'value' => 0,
    ),
    'count_char_min' => array(
        'title' => _wp('The minimum quantity of symbols for fields'),
        'description' => _wp('Allows to specify the minimum quantity of symbols at which to consider fields empty'),
        'control_type' => waHtmlControl::INPUT,
        'value' => 0,
    ),
    'include_subcategory' => array(
        //включать подкатегории в запросе
        'title' => _wp('Include subcategory'),
        'description' => _wp('Include subcategory in filters by features, if category selected'),
        'control_type' => waHtmlControl::CHECKBOX,
        'value' => 0,
    ),
    'view_feature_code'=> array(
        'title'=> _wp('view feature code'),
        'description' => _wp('view feature code in select box'),
        'control_type'=> waHtmlControl::CHECKBOX,
        'value'=>true,
    ),
    'include_subcategory_searchtext' => array(
        //включать подкатегории в запросе
        'title' => _wp('Include subcategory for search text'),
        'description' => _wp('Include subcategory for search text, if category selected'),
        'control_type' => waHtmlControl::CHECKBOX,
        'value' => 0,
    ),

);
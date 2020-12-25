<?php
/**
 * Created by PhpStorm.
 * User: snark | itfrogs.ru
 * Date: 21.08.14
 * Time: 23:07
 */

return array(
    /*
    'app_id' => array(
        'value' => "",
        'control_type' => 'text',
        'title' => _wp('Application ID'),
        'description' => _wp("Set the vkontakte Application ID"),
        'subject' => 'basic_settings',
    ),
    'app_secret' => array(
        'value' => "",
        'control_type' => 'text',
        'title' => _wp('App secret'),
        'description' => _wp("Set the vkontakte App Secret"),
        'subject' => 'basic_settings',
    ),
    */
    'image_size' => array(
        'value' => '970',
        'title'         => _wp('Image size'),
        'description'   => _wp('Select the image size. Remember that the size should be no less than 400x400 pixels.'),
        'control_type' => waHtmlControl::RADIOGROUP,
        'options_callback' => array('shopVkshopPlugin', 'getImageSizes'),
        'subject' => 'basic_settings',
    ),
    'category_id' => array(
        'title' => _wp('Default VK category'),
        'control_type' => waHtmlControl::CUSTOM . ' ' . 'shopVkshopPlugin::settingCustomControlVkCats',
        'description' => _wp("Select a category of goods VC, which will be set by default when exporting."),
        'subject' => 'basic_settings',
    ),
    /*
    'domain' => array(
        'value' => '',
        'title' => _wp('Settlement'),
        'description' => _wp('Select the settlement.'),
        'control_type' => waHtmlControl::CUSTOM . ' ' . 'shopVkshopPlugin::settingCustomControlSettlements',
        'subject' => 'basic_settings',
    ),*/
    'convert_currency' => array(
        'value' => 0,
        'control_type' => waHtmlControl::CHECKBOX,
        'title' => _wp('Convert currency'),
        'description' => _wp("If enabled, currency will be converted."),
        'subject' => 'basic_settings',
    ),
    'currency' => array(
        'value' => 'RUB',
        'title' => _wp('Currency'),
        'description' => _wp('The currency, which has a shop Vkontakte.'),
        'control_type' => waHtmlControl::SELECT,
        'options' => array(
            'RUB' => 'RUB',
            'UAH' => 'UAH',
            'BYR' => 'BYR',
            'KZT' => 'KZT',
            'USD' => 'USD',
            'EUR' => 'EUR',
        ),
        'subject' => 'basic_settings',
    ),
    'log' => array(
        'value' => 0,
        'control_type' => waHtmlControl::CHECKBOX,
        'title' => _wp('Logging activities'),
        'description' => _wp("If enabled, any activity will be logged."),
        'subject' => 'basic_settings',
    ),
    'error_log' => array(
        'value' => 0,
        'control_type' => waHtmlControl::CHECKBOX,
        'title' => _wp('Logging errors'),
        'description' => _wp("If enabled, any all errors will be logged."),
        'subject' => 'basic_settings',
    ),
    'maxtags' => array(
        'value' => 30,
        'title' => _wp('Max hashtags quantity'),
        'description' => _wp('Set the max hashtags quantity.'),
        'control_type' => waHtmlControl::INPUT,
        'subject' => 'basic_settings',
    ),
    'maxtagslenght' => array(
        'value' => 500,
        'title' => _wp('Max hashtags text lenght'),
        'description' => _wp('Set the max hashtags text lenght.'),
        'control_type' => waHtmlControl::INPUT,
        'subject' => 'basic_settings',
    ),
    'max_description_lenght' => array(
        'value' => 2000,
        'title' => _wp('Maximum description lenght'),
        'description' => _wp('Set the max description lenght.'),
        'control_type' => waHtmlControl::INPUT,
        'subject' => 'basic_settings',
    ),
    'stack_count' => array(
        'value' => 10,
        'title' => _wp('The number of products in one stack'),
        'description' => _wp('To work a little faster plugin when exporting goods takes not one, but several pieces in groups. Set the number of items in a single package. With an increase of the number of exports will be faster. But, at a certain value, depending on your server`s exports may hang. If this happens, reduce the number and try again.'),
        'control_type' => waHtmlControl::INPUT,
        'subject' => 'basic_settings',
    ),
    'access_token' => array(
        'value' => '',
        'control_type' => waHtmlControl::HIDDEN,
        'subject' => 'basic_settings',
    ),
    'token_datetime' => array(
        'value' => '',
        'control_type' => waHtmlControl::HIDDEN,
        'subject' => 'basic_settings',
    ),
    'user_id' => array(
        'value' => '',
        'control_type' => waHtmlControl::HIDDEN,
        'subject' => 'basic_settings',
    ),
    'secret' => array(
        'value' => '',
        'control_type' => waHtmlControl::HIDDEN,
        'subject' => 'basic_settings',
    ),
    'groups' => array(
        'value' => array(),
        'title' => _wp('Number of the VK Group'),
        'control_type' => waHtmlControl::CUSTOM . ' ' . 'shopVkshopPlugin::getGroupsControl',
        'description' => _wp("Set number of the VK Group. If group link is http://vk.com/club76633538, then number is 76633538"),
        'subject' => 'groups_settings',
    ),
    'features' => array (
        'title' => _wp('Features'),
        'description' => _wp('Select the available features.'),
        'control_type' => waHtmlControl::GROUPBOX,
        'options_callback' => array (
            'shopVkshopPlugin',
            'settingsFeatures'
        ),
        'subject' => 'templates_settings',
    ),
    'fdelimeter' => array (
        'value' => '<br>',
        'title' => _wp('Features delimiter'),
        'description' => _wp('Select the delimiter'),
        'control_type' => waHtmlControl::SELECT,
        'options' => array (
            array (
                'value' => ', ',
                'title' => ',',
                'description' => ''
            ),
            array (
                'value' => '; ',
                'title' => ',',
                'description' => ''
            ),
            array (
                'value' => '<br>',
                'title' => _wp('Break line'),
                'description' => ''
            )
        ),
        'subject' => 'templates_settings',
    ),
    'caption_tmpl' => array(
        'value' => '{$product.full_url}<br>{$product.hashtags}<br>{$product.summary|strip_tags}<br>{$product.features}',
        'title' => _wp('Description template'),
        'description' => _wp('Product description template.'),
        'control_type' => waHtmlControl::TEXTAREA,
        'subject' => 'templates_settings',
    ),
    'caption_hint' => array(
        'title' => _wp('Description hint'),
        'description' => _wp('Tip for objects used in the description template. Used Smarty.'),
        'control_type' => waHtmlControl::CUSTOM . ' ' . 'shopVkshopPlugin::getCaptionhintControl',
        'subject' => 'templates_settings',
    ),
    'cron_lamp' => array(
        'title' => _wp('Cron job status'),
        'description' => _wp('If the light is green, the script is working.'),
        'control_type' => waHtmlControl::CUSTOM . ' ' . 'shopVkshopPlugin::cronLampControl',
        'subject' => 'cron_settings',
    ),
    'cron_autoadd' => array(
        'value' => 0,
        'title' => _wp('Automatically add products'),
        'description' => _wp('If in the settings of the category of this product there is a match with a album of VK, then the product will be added to the appropriate album.'),
        'control_type' => waHtmlControl::CHECKBOX,
        'subject' => 'cron_settings',
    ),
    'cron_autoupdate' => array(
        'value' => 0,
        'title' => _wp('Automatically update products'),
        'description' => _wp('The cron script will automatically update the products in the VK group if they have been updated in the store.'),
        'control_type' => waHtmlControl::CHECKBOX,
        'subject' => 'cron_settings',
    ),
    'cron_autodelete' => array(
        'value' => 0,
        'title' => _wp('Automatically delete products'),
        'description' => _wp('The cron script will automatically delete the products in the VK group if they have been deleted in the store.'),
        'control_type' => waHtmlControl::CHECKBOX,
        'subject' => 'cron_settings',
    ),
    'cron_delete_hidden' => array(
        'value' => 0,
        'title' => _wp('Automatically delete hidden products'),
        'description' => _wp('The cron script will automatically delete the products in the VK group if they have been hidden in the store.'),
        'control_type' => waHtmlControl::CHECKBOX,
        'subject' => 'cron_settings',
    ),
    'cron_delete_nonstock' => array(
        'value' => 0,
        'title' => _wp('Automatically remove missing products'),
        'description' => _wp('The cron script will automatically delete the products in the VK group if they run out of stock.'),
        'control_type' => waHtmlControl::CHECKBOX,
        'subject' => 'cron_settings',
    ),
    'cron_no_datetime' => array(
        'value' => 0,
        'title' => _wp('Do not consider the date of editing the goods'),
        'description' => _wp('The cron script will automatically delete the products in the VK group if they run out of stock.'),
        'control_type' => waHtmlControl::CHECKBOX,
        'subject' => 'cron_settings',
    ),
    'cron_hint' => array(
        'title' => _wp('Cron hint'),
        'control_type' => waHtmlControl::CUSTOM . ' ' . 'shopVkshopPlugin::getCronHintControl',
        'subject' => 'cron_settings',
    ),
    'feedback' => array(
        'title' => _wp('Ask for technical support'),
        'description' => _wp('Click on the link to contact the developer.'),
        'control_type' => waHtmlControl::CUSTOM . ' ' . 'shopVkshopPlugin::getFeedbackControl',
        'subject' => 'info_settings',
    ),
    'hint' => array(
        'control_type' => waHtmlControl::CUSTOM . ' ' . 'shopVkshopPlugin::settingCustomControlHint',
        'subject' => 'info_settings',
    ),
    'cron_timestamp' => array(
        'control_type' => waHtmlControl::HIDDEN,
        'subject' => 'info_settings',
    ),
);

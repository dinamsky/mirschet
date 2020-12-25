<?php
/**
 * Created by PhpStorm.
 * User: snark | itfrogs.ru
 * Date: 21.08.14
 * Time: 23:07
 */


return array(
    'app_id' => array(
        'value' => "",
        'control_type' => 'text',
        'title' => _wp('Application ID'),
        'description' => _wp("Set the vkontakte Application ID"),
    ),
    'group_id' => array(
        'title' => _wp('Number of the VK Group'),
        'value' => "",
        'control_type' => 'text',
        'description' => _wp("Set number of the VK Group. If group link is http://vk.com/club76633538, then number is 76633538"),
    ),
    'signed_post' => array(
        'title' => _wp('Sign the wall post'),
        'value' => 0,
        'control_type' => 'checkbox',
        'description' => _wp("If checked, then wall post will be signed."),
    ),
    'ads_post' => array(
        'title' => _wp('Sign an entry as an advertisement'),
        'value' => 0,
        'control_type' => 'checkbox',
        'description' => _wp("If checked, the recording on the wall will be marked as an advertisement."),
    ),
    'desc' => array(
        'value'        => 'description',
        'title'        => _wp('Descryption type'),
        'description'  => _wp('Set description type.'),
        'control_type' => waHtmlControl::SELECT,
        'options'      => array(
            'description'   => _wp('Description'),
            'summary' => _wp('Summary'),
        )
    ),
    'desc_size' => array(
        'value' => "650",
        'title' => _wp('Description size'),
        'control_type' => 'text',
        'description' => _wp("Sometimes VK refuses to take too long descriptions. This option is introduced in order to be able to cut a description of the size, which receives the VK. Some is 700 characters, some 650 or 600."),
    ),
    'domain' => array(
        'value' => '',
        'title' => _wp('Settlement'),
        'description' => _wp('Select the settlement.'),
        'control_type' => waHtmlControl::CUSTOM . ' ' . 'shopVkontaktePlugin::settingCustomControlSettlements',
    ),
    'feedback' => array(
        'title' => _wp('Ask for technical support'),
        'description' => _wp('Click on the link to contact the developer.'),
        'control_type' => waHtmlControl::CUSTOM . ' ' . 'shopVkontaktePlugin::getFeedbackControl',
        'subject' => 'info_settings',
    ),
);

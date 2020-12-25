<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @version 2.5.0
 * @copyright Serge Rodovnichenko, 2015-2016
 */
return array(
    'php'                    => array(
        'version'     => '>=5.6.0',
        'description' => 'Поддержка совместимости со старыми версиями PHP 5.2-5.5 отключена. Совместимость с PHP 5.6 будет поддерживаться до декабря 2017',
        'strict'      => true
    ),
    'php.curl'               => array(),
    'phpini.allow_url_fopen' => array(),
    'php.mbstring'           => array(
        'description' => 'Сравнение Unicode строк', 'strict' => true,
    ),
    'php.simplexml'          => array(
        'description' => 'Обработка запросов и ответов удаленного сервера', 'strict' => true,
    ),
    'php.dom'       => array(
        'strict' => true,
        'value'  => 1,
    ),
    'php.ctype'       => array(
        'strict' => true,
        'value'  => 1,
    ),
    'app.shop'               => array(
        'version' => '>=7.5.0',
        'strict'  => true
    ),
    /**
     * - waNet class requires Webasyst Framework 1.5.7+
     * - Models have a new truncate() method since WAF 1.6.0
     */
    'app.installer'          => array(
        'version' => 'latest', 'strict' => true,
    ),
);
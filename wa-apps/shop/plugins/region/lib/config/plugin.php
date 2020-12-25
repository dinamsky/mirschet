<?php
return array(
    'name' => /*_w*/('Regions on subdomains'),
    'description' => /*_w*/('Divide areas into separate subdomains.'),
    'img'=>'img/logo.png',
    'version' => '1.2.4',
    'vendor' => 1008046,
    'shop_settings' => true,
    'handlers' => array(
        'frontend_footer' => 'frontendFooter',
        'frontend_head' => 'frontendHead',
		//'frontend_header' => 'frontendHeader'
    ),
);

<?php

return array(
    'name' => 'Управление Доп. параметрами',
    'description' => 'Формирует поля из дополнительных параметров',
    'vendor' => '990614',
    'author'=>'Genasyst',
    'version' => '1.0.6',
    'img' => 'img/advancedparams.png',
    'custom_settings' => true,
    'handlers' => array (
        'backend_page_edit' => 'backendPageEdit',
        'page_edit' => 'PageEdit',
        'page_save' => 'pageSave', 
        'page_delete' => 'pageDelete'
    ),
);
//EOF
<?php
/**
 * Created by PhpStorm.
 * User: snark | itfrogs.ru
 * Date: 8/13/15
 * Time: 10:38 PM
  */

return array(
    'php.curl'=>array(
        'name'=>'cURL',
        'description'=>_wp('The data exchange with third-party servers'),
        'strict'=>true,
    ),
    'phpini.max_execution_time'=>array(
        'name'=>_wp('Maximum execution time'),
        'description'=>_wp('Maximum execution time of PHP-scripts'),
        'strict'=>false,
        'value'=>'>60',
    ),
);
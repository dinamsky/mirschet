<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */
try {
    $file = dirname(__FILE__) . '/../../../locale/.htaccess_1';
    if (file_exists($file)) {
        waFiles::delete($file, true);
    }
} catch (Exception $ex) {
    
}
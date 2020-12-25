<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class shopFlexdiscountFactory
{
    private static $factories = array();

    public function getFactory($factory)
    {
        $class = 'shopFlexdiscount' . ucfirst($factory) . 'Storage';
        if (!isset(self::$factories[$class])) {
            if (class_exists($class)) {
                self::$factories[$class] = new $class();
            } else {
                throw new Exception(_wp('Class not found') . ' ' . $class);
            }
        }
        return self::$factories[$class];
    }
}
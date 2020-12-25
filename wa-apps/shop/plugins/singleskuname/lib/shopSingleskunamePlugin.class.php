<?php

class shopSingleskunamePlugin extends shopPlugin
{
    public static function backendProductEdit()
    {
        return array(
            'edit_basics' => '<style>.s-product-skus .s-name {display: block !important;}</style>'
        );
    }
}

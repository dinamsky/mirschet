<?php
/**
 * Created by PhpStorm.
 * User: snark | itfrogs.ru
 * Date: 25/9/15
 * Time: 6:40 PM
  */

try {
    $asm = new waAppSettingsModel();

    $routing = wa()->getRouting();
    $domain_routes = $routing->getByApp('shop');
    foreach ($domain_routes as $domain => $routes) {
        foreach ($routes as $route) {
            $routing->setRoute($route, $domain);
            $settlement = wa()->getRouteUrl('/frontend/product', array('product_url' => false), true);
            $routing->setRoute($route, $domain);
            waRequest::setParam($route);
            $settlement = rtrim($settlement, '/').'/';
            $settlements[] = $settlement;
        }
    }
    $asm->set(array('shop', 'vkontakte'), 'domain', reset($settlements));
}
catch (waException $e) {

}


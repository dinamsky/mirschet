<?php

class shopVkgoodsPluginBackendTmpltdlgAction extends waViewAction
{

    /*
     * @desc  Формирует диалоговые окна на основе шаблона
     */

    function execute()
    {

        $params = waRequest::post();

        if (isset($params['event']) && $params['event'] != 'delman') {
            $pid = $params['pid'];
            $pid_type = 'pid';
        } elseif (isset($params['hash'])) {
            $params['event'] = 'list';
            $pid = $params['hash'];
            $pid_type = 'hash';
        } elseif (isset($params['product_id'])) {
            $params['event'] = 'list';
            $pid_type = 'list';
            $pid = implode(',', $params['product_id']);
        }

        //Проверка валидности токена
        $plugin = wa()->getPlugin('vkgoods');
        $vkg = $plugin->getSettings();
        $vk_session = new shopVkgoodsPluginVkapi($vkg['vk_user_id'], $vkg['token']);

        $user_groups = $vk_session->getUserGroups($vkg['vk_user_id']);

        $model_set_prod = new shopSetModel();
        $sets_products = $model_set_prod->getAll();
        $uinfo = array();

        $storefronts = array();
        $current_domain = &$profile['config']['domain'];
        $routing = wa()->getRouting();
        $domain_routes = $routing->getByApp('shop');
        foreach ($domain_routes as $domain => $routes) {
            foreach ($routes as $route) {
                $storefront = $domain . '/' . $route['url'];
                if (($storefronts == $current_domain) || ($current_domain === '')) {
                    $current_domain = $storefront;
                    $routing->setRoute($route, $domain);
                    waRequest::setParam($route);
                }
                $storefronts[] = $storefront;
            }
        }

        $view = wa()->getView();

        $view->assign('uinfo', $uinfo);
        $view->assign('event', $params['event']);
        $view->assign('vkgoods_settings', $vkg);
        $view->assign('sets_products', $sets_products);
        $view->assign('storefronts', $storefronts);
        $view->assign('user_groups', ifset($user_groups));
        $view->assign('vk_cats', include($plugin->getPath() . '/lib/config/vk_cats.php'));
        $view->assign('params', $params);
        if ($params['event'] != 'delman') {
            $view->assign('pid', $pid);
            $view->assign('pid_type', $pid_type);
        }

    }

}

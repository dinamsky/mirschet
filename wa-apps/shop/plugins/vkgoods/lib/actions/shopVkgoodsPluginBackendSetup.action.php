<?php

class shopVkgoodsPluginBackendSetupAction extends waViewAction
{

    public function execute()
    {

        $vkgoods = wa()->getPlugin('vkgoods');
        $oauth_vk_uri = urlencode(wa()->getURL(true) . "?plugin=vkgoods&action=oauthvk");
        $vkgoods_settings = $vkgoods->getSettings();

        $wait_model = new shopVkgoodsPluginWaitproductModel();
        $wait_public = $wait_model->query('select count(distinct pid) from shop_vkgoods_wait_product')->fetchAll();
        $wait_group = $wait_model->query('select count(distinct gid) from shop_vkgoods_wait_product')->fetchAll();

        $vkprod_model = new shopVkgoodsPluginProductModel();
        $vkprod = $vkprod_model->query('select count(distinct pid) from shop_vkgoods_product')->fetchAll();

        $collection = new shopProductsCollection();
        $collection->addWhere("p.status = 1");

        $view = wa()->getView();

        if ($vkgoods_settings['token'] != "") {

            $vk_session = new shopVkgoodsPluginVkapi($vkgoods_settings['vk_user_id'], $vkgoods_settings['token'], "");
            $user_info = $vk_session->api('users.get', array('user_ids' => $vkgoods_settings['vk_user_id'], 'fields' => 'photo_50', 'https' => 1), true);
            $user_groups = $vk_session->getUserGroups($vkgoods_settings['vk_user_id']);
            $view->assign('user_info', $user_info['response'][0]);
            $view->assign('user_groups', $user_groups);

        }

        $view->assign('vkgoods_settings', $vkgoods_settings);
        $view->assign('oauth_vk_uri', $oauth_vk_uri);
        $view->assign('wait_count', $wait_model->countAll());
        $view->assign('wait_prd_count', $wait_model->countAll());
        $view->assign('wait_group_vk', $wait_group[0]['count(distinct gid)']);
        $view->assign('prd_public', $vkprod[0]['count(distinct pid)']);
        $view->assign('all_prd_count', $collection->count());
        $view->assign('path', wa()->getConfig()->getRootPath());
        $view->assign('yes_curl', function_exists('curl_init'));

    }

}

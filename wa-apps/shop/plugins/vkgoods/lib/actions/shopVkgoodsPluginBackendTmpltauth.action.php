<?php
class shopVkgoodsPluginBackendTmpltauthAction extends waViewAction {

    function execute() {

        $str_uids = "";
        foreach ($profiles as $profile) {
            $str_uids = $str_uids . $profile['uid'] . ',';
        }
        $view = wa() -> getView();
        $vk_session = new shopVkgoodsPluginVkapi($profiles[0]['uid'], $profiles[0]['token'], "");
        $response = $vk_session -> api('users.get', '&fields=photo_50&user_ids=' . $str_uids, TRUE);
        if (!isset($response['error'])) {
            $vkusers = $response['response'];
            $view -> assign('vkusers', $vkusers);
        }
    }

}

<?php

/**
 * Процесс авторизации ВКонтакте
 */
class shopVkgoodsPluginBackendOauthvkController extends waController {


    public function execute(){
        $oauth_uri = wa()->getURL(true) . "?plugin=vkgoods&action=oauthvk";
        $vkgoods = wa()->getPlugin('vkgoods');
        $vkgoods_settings = $vkgoods->getSettings();
        if ($code = waRequest::get('code')) {
            $token_url = "https://oauth.vk.com/token";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_URL, $token_url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            
            $data = array(
                'code' => $code,
                'client_id' => $vkgoods_settings['app_site'],
                'client_secret' => $vkgoods_settings['app_site_secret'],
                'redirect_uri' => $oauth_uri
            );
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            
            $response = curl_exec($ch);
            $response = json_decode($response, true);
            curl_close($ch);
            if (!isset($response['error'])) {
                $app_settings_model = new waAppSettingsModel();
                $app_settings_model->set(array(
                    'shop',
                    'vkgoods'
                ), 'token', $response['access_token']);
                $app_settings_model->set(array(
                    'shop',
                    'vkgoods'
                ), 'vk_user_id', $response['user_id']);
                echo "<script type='text/javascript'>opener.location.reload();window.close();</script>";
            } else {
                waLog::log($response['error'], 'shop/plugins/vkgoods/vkgoods.log');
                echo "Ошибка авторизации. Подробности см. в файле vkgoods.log";
            }
        }
    }
}
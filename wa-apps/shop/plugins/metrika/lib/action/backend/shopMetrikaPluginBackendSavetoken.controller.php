<?php

/**
 * @see https://tech.yandex.ru/metrika/doc/ref/concepts/About-docpage/
 */
class shopMetrikaPluginBackendSavetokenController extends waJsonController
{
    const CLIENT_ID = 'e9212e398adb4e1984db0bff3ec4669c';
    const CLIENT_SECRET = '5f945e55016c4401922bbe3a8164eb98';

    public function execute()
    {
        $code = waRequest::get('code');
        if (isset($code)) {

            $request_data = array(
                'grant_type' => 'authorization_code', // тип авторизации
                'code' => trim($code), // полученный код
                'client_id' => self::CLIENT_ID,
                'client_secret' => self::CLIENT_SECRET
            );

            try {
                $net = new waNet();
                $net->query('https://oauth.yandex.ru/token', $request_data, 'POST');
                $result = $net->getResponse();
                $settings_model = new waAppSettingsModel();
                $settings_model->set('shop.metrika', 'token', json_decode($result)->access_token);

            } catch (Exception $ex) {
                $result = $ex->getMessage();
                $this->setError(json_decode($result)->error_description);
            }

        }
    }


}
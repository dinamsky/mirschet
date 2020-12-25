<?php

class shopVkgoodsPluginVkapi
{

    const API_VERSION = '5.95';
    private $user_id;
    private $access_token;
    private $user_agent;

    /*
     * @var int - id пользователя ВКонтакте
     * @var str - access token
     * @var str - путь для сохранения временных файлов
     * @var boolean
     */
    public function __construct($user_id, $access_token, $debug = false)
    {
        $this->user_id = $user_id;
        $this->access_token = $access_token;
        $this->debug = $debug;
        $this->call = 0;
    }

    /*
     * @desc Универсальная функция для отправки запросов к API ВКонтакте
     * @var string - название метода API
     * @var array() - массив параметров
     * @var boolean
     * @return декодированная json-строка
     */
    public function api($method, $param, $stoa = false)
    {

        $res = $this->curlPost("https://api.vk.com/method/" . $method . "?v=" . self::API_VERSION . "&access_token=" . $this->access_token, $param);
        $tr = json_decode($res);

        /* Такая порстенькая попытка избежать случайных сбоев API ВК
         * 7 - в группе нет товаров
         * 1406 - Too many items in album
         */
        if (isset ($tr->error) && $tr->error->error_code != '7' && $tr->error->error_code != '1406') {
            sleep(2);
            $res = $this->curlPost("https://api.vk.com/method/" . $method . "?v=" . self::API_VERSION . "&access_token=" . $this->access_token, $param);
            $tr = json_decode($res);
        }

        if (isset ($tr->error) && $tr->error->error_code != '7') {
            waLog::log(json_encode($tr->error), 'shop/plugins/vkgoods/vkgoods.log');
        }

        if ($this->debug == true) {
            $this->debugLog($method . "?" . $param, $res);
        }
        return json_decode($res, $stoa);

    }

    /**
     * Загрузка файлов фотографий на сервер ВКонтакте
     *
     * @var int - id сообщества ВК
     * @var str - путь к исходному изображению
     * @var 1 - основное фото, 0 - нет
     *
     */

    public function uploadImg($gid, $path, $main_photo)
    {

        if (class_exists('CURLFile')) {
            $image = new waImage ($path);
            $file = new CURLFile ($path, $image->type);
            $data = array(
                "file1" => $file
            );
        } else {
            $data = array(
                "file1" => "@" . $path
            );
        }

        $server = $this->api("photos.getMarketUploadServer", array('group_id' => $gid, 'main_photo' => $main_photo));
        if (isset ($server->error)) {
            return $server;
        }
        $res = $this->curlPost($server->response->upload_url, $data);

        $we = json_decode($res);
        if (isset ($we->error)) {
            if (strpos($we->error, 'BAD_IMAGE_SIZE')) {
                waLog::log('Изображение не принято сервером ВК. Изображение должно иметь размер не меньше 400 точек и не больше 7000 точек по каждой из сторон. Кроме того, нельзя использовать изображения, у которых одна из сторон в разы превышает другую.' . " (" . $path . " )", 'shop/plugins/vkgoods/vkgoods.log');
                return $we;
            }
            waLog::log($we->error . " (" . $path . " )", 'shop/plugins/vkgoods/vkgoods.log');
            return $we;

        }

        if ($we->photo == '[]') {
            waLog::log('Изображение не принято сервером ВК. Изображение должно иметь размер не меньше 400 точек и не больше 7000 точек по каждой из сторон. Кроме того, нельзя использовать изображения, у которых одна из сторон в разы превышает другую.' . " (" . $path . " )", 'shop/plugins/vkgoods/vkgoods.log');
            $we->error = 'BAD_IMAGE_SIZE';
            return $we;
        }

        $upload = json_decode($res);
        $img_data = array(
            'group_id' => $gid,
            'server' => $upload->server,
            'photo' => $upload->photo,
            'hash' => $upload->hash,
        );

        if (isset ($upload->crop_data)) {
            $img_data ['crop_data'] = $upload->crop_data;
        }
        if (isset ($upload->crop_hash)) {
            $img_data ['crop_hash'] = $upload->crop_hash;
        }

        $save = $this->api("photos.saveMarketPhoto", $img_data);

        return $save;
    }

    public function getUserGroups($user_id = null)
    {
        if (!$user_id) {
            $user_id = $this->user_id;
        }
        $cache_groups = new waSerializeCache('shop_vkgoods_groups_' . $user_id, 1800, 'shop');
        if ($cache_groups->isCached()) {
            $groups = $cache_groups->get();
        } else {
            $data = $this->api('groups.get', "user_id=" . $user_id . "&extended=1&filter=moder", true);
            $groups = array();
            foreach (ifset($data['response']['items'], array()) as $group) {
                $groups[$group['id']] = $group;
            }
            $cache_groups->set($groups);
        }
        return $groups;
    }

    /**
     * Подготовка и отправка POST|GET запросов
     *
     * @var str - URL по которому необходимо отправить запрос
     * @var array() - массив параметров
     */
    public function curlPost($url, $data = array())
    {
        if (!isset ($url)) {
            return false;
        }

        if ($this->call >= 3) {
            sleep(1);
            $this->call = 1;
        } else {
            $this->call++;
        }

        if (function_exists('curl_init')) {

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            if (count($data) > 0) {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                if ((version_compare(PHP_VERSION, '5.5.0') >= 0) && version_compare(PHP_VERSION, '7.0.0', '<')) {
                    curl_setopt($ch, CURLOPT_SAFE_UPLOAD, FALSE);
                }
            }

            $response = curl_exec($ch);
            curl_close($ch);

            return $response;
        } else {
            return (json_encode(array('error' => 'cURL not installed')));
        }
    }

    public function debugLog($method, $response)
    {
        waLog::log($method, 'shop/plugins/vkgoods/vkgoods.debug.log');
        waLog::log($response, 'shop/plugins/vkgoods/vkgoods.debug.log');
    }

}

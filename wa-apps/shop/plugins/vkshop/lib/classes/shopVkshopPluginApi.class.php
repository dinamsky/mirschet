<?php

/**
 * Created by PhpStorm.
 * User: snark | itfrogs.ru
 * Date: 10/2/15
 * Time: 2:13 PM
 */

/**
 * The PHP class for vk.com API and to support OAuth.
 * @author Vlad Pronsky <vladkens@yandex.ru>
 * @license https://raw.github.com/vladkens/VK/master/LICENSE MIT
 */
class shopVkshopPluginApi
{
    /**
     * VK application id.
     * @var string
     */
    private $app_id;
    /**
     * VK application secret key.
     * @var string
     */
    private $api_secret;
    /**
     * VK application user secret key.
     * @var string
     */
    private $user_secret;
    /**
     * API version. If null uses latest version.
     * @var int
     */
    private $api_version;
    /**
     * VK access token.
     * @var string
     */
    private $access_token;
    /**
     * Authorization status.
     * @var bool
     */
    private $auth = false;
    /**
     * Instance curl.
     * @var Resource
     */
    private $ch;
    /**
     * @var shopVkshopPluginAlbumsModel
     */
    private $album_model;

    /**
     * @var shopVkshopPlugin $plugin
     */
    private $plugin;

    /**
     * @var array|mixed|null|string
     */
    private $settings;

    /**
     *
     */
    const AUTHORIZE_URL = 'https://oauth.vk.com/authorize';
    /**
     *
     */
    const ACCESS_TOKEN_URL = 'https://oauth.vk.com/access_token';

    /**
     * Constructor.
     * @param   string $app_id
     * @param   string $api_secret
     * @param   string $user_secret
     * @param   string $access_token
     * @throws  waException
     */
    public function __construct($app_id, $api_secret, $access_token = null, $user_secret = '')
    {
        $this->app_id = $app_id;
        $this->api_secret = $api_secret;
        $this->user_secret = $user_secret;
        $this->setAccessToken($access_token);

        $this->ch = curl_init();
        curl_setopt ($this->ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt ($this->ch, CURLOPT_TIMEOUT, 10);
        curl_setopt ($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt ($this->ch, CURLOPT_SSL_VERIFYHOST, false);

        $this->album_model = new shopVkshopPluginAlbumsModel();
        $this->plugin = wa()->getPlugin('vkshop');
        $this->settings = $this->plugin->getSettings();
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        curl_close($this->ch);
    }

    /**
     * Set special API version.
     * @param   int $version
     * @return  void
     */
    public function setApiVersion($version)
    {
        $this->api_version = $version;
    }

    /**
     * Set Access Token.
     * @param   string $access_token
     * @throws  waException
     * @return  void
     */
    public function setAccessToken($access_token)
    {
        $this->access_token = $access_token;
    }

    /**
     * Returns base API url.
     * @param   string $method
     * @param   string $response_format
     * @return  string
     */
    public function getApiUrl($method, $response_format = 'json')
    {
        return 'https://api.vk.com/method/' . $method . '.' . $response_format;
    }

    /**
     * Returns authorization link with passed parameters.
     * @param   string $api_settings
     * @param   string $callback_url
     * @param   bool   $test_mode
     * @return  string
     */
    public function getAuthorizeUrl(
        $api_settings = '',
        $callback_url = 'https://api.vk.com/blank.html',
        $test_mode = false
    ) {
        $parameters = array(
            'client_id' => $this->app_id,
            'scope' => $api_settings,
            'response_type' => 'code',
            'redirect_uri' => $callback_url,
        );
        if ($test_mode) {
            $parameters['test_mode'] = 1;
        }
        return $this->createUrl(self::AUTHORIZE_URL, $parameters);
    }

    /**
     * Returns access token by code received on authorization link.
     * @param   string $code
     * @param   string $callback_url
     * @throws  waException
     * @return  array
     */
    public function getAccessToken($code, $callback_url = 'https://api.vk.com/blank.html')
    {
        if (!is_null($this->access_token) && $this->auth) {

            //            throw new waException('Already authorized.');
        }
        $parameters = array(
            'client_id' => $this->app_id,
            'client_secret' => $this->api_secret,
            'code' => $code,
            'redirect_uri' => $callback_url
        );


        $rs = $this->request(
            $this->createUrl(self::ACCESS_TOKEN_URL, $parameters)
        );

        if (isset($rs['error'])) {
            return (array) $rs;
        } else {
            $this->auth = true;
            $this->access_token = $rs['access_token'];
            return (array) $rs;
        }
    }

    /**
     * Return user authorization status.
     * @return  bool
     */
    public function isAuth()
    {
        return !is_null($this->access_token);
    }

    /**
     * Check for validity access token.
     * @param   string $access_token
     * @return  bool
     */
    public function checkAccessToken($access_token = null)
    {
        $token = is_null($access_token) ? $this->access_token : $access_token;
        if (is_null($token)) {
            return false;
        }
        $rs = $this->api('getUserSettings', array('access_token' => $token), 'array', 'get', true);
        return isset($rs['response']);
    }


    /**
     * @param $method
     * @param array $parameters
     * @param string $format
     * @param string $requestMethod
     * @param bool $use_sig
     * @return array|SimpleXMLElement|string|waNet
     * @throws waException
     */
    public function api($method, $parameters = array(), $format = 'array', $requestMethod = 'post', $use_sig = true)
    {
        $parameters['timestamp'] = time();
        $parameters['api_id'] = $this->app_id;
        $parameters['random'] = rand(0, 10000);
        if (!array_key_exists('access_token', $parameters) && !is_null($this->access_token)) {
            $parameters['access_token'] = $this->access_token;
        }
        if (!array_key_exists('v', $parameters) && !is_null($this->api_version)) {
            $parameters['v'] = $this->api_version;
        }
        ksort($parameters);
        //$sig = '/method/'.$method.'.json?';

        if ($use_sig) {
            $sig = '';
            foreach ($parameters as $key => $value) {
                $sig .= '&' . $key . '=' . $value;
            }
            $sig = ltrim($sig, '&');
            $sig = '/method/' . $method . '.json?' . $sig;

            if (empty($this->user_secret)) {
                $sig .= $this->api_secret;
            } else {
                $sig .= $this->user_secret;
            }

            $parameters['sig'] = md5($sig);
        }

        if ($method == 'execute' || $requestMethod == 'post') {
            $rs = $this->request($this->getApiUrl($method, $format == 'array' ? 'json' : $format), "POST", $parameters);
        } else {
            $rs = $this->request(
                $this->createUrl(
                    $this->getApiUrl($method, $format == 'array' ? 'json' : $format),
                    $parameters
                )
            );
        }
        usleep(500000);

        return $rs;
    }

    /**
     * Concatenate keys and values to url format and return url.
     * @param   string $url
     * @param   array  $parameters
     * @return  string
     */
    private function createUrl($url, $parameters)
    {
        $url .= '?' . http_build_query($parameters);
        return $url;
    }


    /**
     * @param $url
     * @param string $method
     * @param array $postfields
     * @return array|SimpleXMLElement|string|waNet
     * @throws waException
     */
    private function request($url, $method = 'GET', $postfields = array())
    {
        curl_setopt ( $this->ch, CURLOPT_URL, $url);

        if ($method == 'POST') {
            curl_setopt ( $this->ch, CURLOPT_POST, ($method == 'POST'));
            curl_setopt ( $this->ch, CURLOPT_POSTFIELDS, $postfields);

            if ((version_compare ( PHP_VERSION, '5.5.0' ) >= 0) && version_compare(PHP_VERSION, '7.0.0', '<')) {
                curl_setopt ( $this->ch, CURLOPT_SAFE_UPLOAD, FALSE );
            }
        }

        $data = curl_exec($this->ch);
        if ($data) {
            $data = (array) json_decode($data, true);
        }

        if ($this->settings['log']) {
            waLog::dump($data, 'vkshop-activity.log');
        }
        return $data;
    }


    /**
     * @param $filename
     * @return CURLFile|string
     * @throws waException
     */
    private static function getCurlFile($filename)
    {
        if (class_exists('CURLFile')) {
            $image = new waImage($filename);
            $file = new CURLFile($filename, $image->type);
            return $file;
        }
        return '@' . $filename;
    }


    /**
     * @param $parameters
     * @param $type
     * @param null $name
     * @param null $shop_id
     * @param null $group_id
     * @return int
     * @throws waException
     */
    public function addAlbum($parameters, $type, $name = null, $shop_id = null, $group_id = null) {
        $r = $this->api('market.addAlbum', $parameters);

        $album_id = 0;
        if (!isset($r['error']) && isset($r['response']) && isset($r['response']['market_album_id'])) {
            $data = array(
                'type'      => $type,
                'album_id'  => $r['response']['market_album_id'],
                'group_id'  => $group_id,
            );
            if ($name) {
                $data['name'] = $name;
            }
            if ($shop_id) {
                $data['shop_id'] = $shop_id;
            }
            $this->album_model->insert($data);
            $album_id = $r['response']['market_album_id'];
        }

        /*
         * Иногда бывает так, что подборка добавлена, но все равно вылетает ошибка. Тогда создастся много альбомов с обним и тем же именем.
         * Для этого и сравниваем.
         */
        $tools = new shopVkshopPluginTools($this);
        $tools->compareAlbums();

        return $album_id;
    }

    /**
     * @param $params
     * @param array $uploaded_images
     * @param bool $edit
     * @return mixed
     */
    public function saveProduct($params, $uploaded_images = array(), $edit = false, $vk_product = array())
    {
        $main_image = reset($uploaded_images);
        if (isset($main_image['vk_photo_id'])) {
            if (!is_numeric($main_image['vk_photo_id'])) {
                $numbers = explode('_', $main_image['vk_photo_id']);
                $main_photo_id = $numbers[1];
            }
            else {
                $main_photo_id = intval($main_image['vk_photo_id']);
            }
        }
        else {
            $main_photo_id = intval($main_image['id']);
        }

        unset($uploaded_images[0]);
        $images = array();
        foreach ($uploaded_images as $image) {
            if (isset($image['vk_photo_id'])) {
                if (!is_numeric($image['vk_photo_id'])) {
                    $numbers = explode('_', $main_image['vk_photo_id']);
                    $images[] = $numbers[1];
                }
                else {
                    $images[] = intval($image['vk_photo_id']);
                }
            }
            else {
                $images[] = intval($image['id']);
            }
        }
        $photo_ids = '';
        if (!empty($images)) {
            $photo_ids = implode(',', $images);
        }
        $parameters = array(
            'owner_id' => '-' . $params['group_id'],
            'name' => $params['name'],
            'description' => $params['description'],
            'category_id' => $params['category_id'],
            'price' => $params['price'],
            'main_photo_id' => $main_photo_id,
            'deleted' => $params['status'] == 1 ? 0 : 1,
            //'photo_ids'     => $photo_ids,
        );

        if (isset($params['full_url']) && filter_var($params['full_url'], FILTER_VALIDATE_URL)) {
            $parameters['url'] = $params['full_url'];
        }

        if (!empty($photo_ids)) {
            $parameters['photo_ids'] = $photo_ids;
        }

        if ($edit) {
            $parameters['item_id'] = $vk_product['item_id'];
            //unset($parameters['photo_ids']);
            $method = 'market.edit';
        } else {
            $method = 'market.add';
        }

        $result = $this->api($method, $parameters);
        return $result;
    }


    /**
     * @param $data
     * @param $product
     * @param $result
     * @param string $action
     * @return bool
     * @throws waException
     */
    public function parseError($data, shopProduct $product, $result, $action = 'send')
    {
        $ptqm = new shopVkshopPluginProductsTempQueueModel();
        $pqm = new shopVkshopPluginProductsQueueModel();
        $pm = new shopVkshopPluginProductsModel();

        $group_model = new shopVkshopPluginGroupModel();
        $groups = $group_model->getLoginedGroups();

        if (isset($result['error']) || !isset($result['response'])) {
            $error = $result['error'];
            $data = $this->teak($data);
            $data['error'] = array(
                'error' => $error['error_code'],
                'error_description' => $error['error_msg'],
            );

            return $data;
        } else {
            $item_id = 0;
            if (isset($result['response']) && isset($result['response']['market_item_id'])) {
                /*
                 * status добавлен в массив товаров для того чтобы обновить старые базы данных у клиентов,
                 * у которых не заполнена таблица альбомов товаров
                 */
                $d = array(
                    'product_id' => $product->getId(),
                    'group_id' => $product->group_id,
                    'item_id' => $result['response']['market_item_id'],
                    'datetime' => date('Y-m-d H:i:s'),
                    'status' => 1,
                );
                $pm->insert($d);

                $item_id = $result['response']['market_item_id'];
            } else {
                $item = $pm->getByField('product_id', $product->getId());
                if (!empty($item)) {
                    $item_id = $item['item_id'];
                }
            }

            $album_ids = array();

            if ($this->settings['cron_autoadd'] && !empty($groups)) {
                $category_products_model = new shopCategoryProductsModel();
                $vkshop_category_model = new shopVkshopPluginCategoryModel();
                $categories = $category_products_model->getByField('product_id', $product->getId(), true);
                foreach ($categories as $category) {
                    foreach ($groups as $group) {
                        $album_link = $vkshop_category_model->getByField(
                            array(
                                'category_id'   => $category['category_id'],
                                'group_id'      => $group['id'],
                            )
                        );
                        if (!empty($album_link)) {
                            $album_ids[] = intval($album_link['album_id']);
                        }
                    }
                }
            }

            $data = $this->addToAlbums($product, $item_id, $data, $album_ids);

            $ptqm->deleteByField(
                array(
                    'product_id' => $product->getId(),
                    'action' => $action,
                )
            );
            $pqm->deleteById($product->getId());

            $data = $this->teak($data);
        }
        return $data;
    }


    /**
     * @param shopProduct $product
     * @param $item_id
     * @param $data
     * @param $album_ids
     * @param $action
     * @return array|mixed
     * @throws waException
     */
    public function addToAlbums(shopProduct $product, $item_id, $data, $album_ids) {
        if (isset($data['album_id']) && intval($data['album_id']) > 0) {
            array_push($album_ids, intval($data['album_id']));
            $album_ids = array_unique($album_ids);
        }

        if (!empty($album_ids)) {
            $parameters = array(
                'owner_id' => '-' . $product->group_id,
                'item_id' => $item_id,
                'album_ids' => implode(',', $album_ids),
            );
            $result = $this->api('market.addToAlbum', $parameters);

            if (isset($result['error']) || !isset($result['response'])) {
                if ($result['error']['error_code'] == 1406 || $result['error']['error_msg'] == 'Too many items in album') {
                    $new_album_name = _wp('Аlbum-') . time();
                    $parameters = array(
                        'owner_id' => '-' . $product->group_id,
                        'main_album' => 0,
                        'title' => $new_album_name,
                    );

                    $data['album_id'] = $this->addAlbum($parameters, 'custom', $new_album_name, null, $product->group_id);
                    if ($data['album_id'] != 0) {
                        $album_ids = array(0 => intval($data['album_id']));
                        $parameters = array(
                            'owner_id' => '-' . $product->group_id,
                            'item_id' => $item_id,
                            'album_ids' => implode(',', $album_ids),
                        );
                        $result = $this->api('market.addToAlbum', $parameters);
                    }
                }
            }

            $parameters = array(
                'item_ids'  => '-' . $product->group_id . '_' . $item_id,
                'extended'  => 1,
            );
            $result = $this->api('market.getById', $parameters);

            if (isset($result['response']) && isset($result['response']['items'])) {
                $item = reset($result['response']['items']);
                if (isset($item['albums_ids']) && !empty($item['albums_ids'])) {
                    $this->updateProductAlbums($product->getId(), $product->group_id, $item['albums_ids']);
                }
            }
        }

        return $data;
    }

    /**
     * @param $product_id
     * @param $group_id
     * @param $albums_ids
     * @throws waException
     */
    public function updateProductAlbums($product_id, $group_id, $albums_ids) {
        $products_albums_model = new shopVkshopPluginProductsAlbumsModel();
        $products_albums_model->deleteByField(
            array(
                'product_id'    => $product_id,
                'group_id'      => $group_id,
            )
        );

        $insert_data = array();
        foreach ($albums_ids as $album_id) {
            $insert_data[] = array(
                'product_id'    => $product_id,
                'group_id'      => $group_id,
                'album_id'      => $album_id,
            );
        }
        $products_albums_model->multipleInsert($insert_data);
    }

    /**
     * @param $vk_products
     * @throws waException
     */
    public function checkItemsAlbums($vk_products) {
        if (!empty($vk_products)) {
            $pm = new shopVkshopPluginProductsModel();
            $item_ids = array();
            $vk_items = array();
            foreach ($vk_products as $p) {
                $item_ids[] = '-' . $p['group_id'] . '_' . $p['item_id'];
                $vk_items[$p['item_id']] = $p;
            }

            $parameters = array(
                'item_ids'  => implode(',', $item_ids),
                'extended'  => 1,
            );
            $result = $this->api('market.getById', $parameters);

            if (isset($result['response']) && isset($result['response']['items'])) {
                foreach ($result['response']['items'] as $item) {
                    if (isset($item['albums_ids']) && !empty($item['albums_ids']) && isset($vk_items[$item['id']])) {
                        $p = $vk_items[$item['id']];
                        $this->updateProductAlbums($p['product_id'], $p['group_id'], $item['albums_ids']);

                        if (empty($p['status'])) {
                            $p['status'] = 1;
                            $pm->updateById($p['id'], $p);
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $data
     * @param string $action
     * @return mixed
     */
    public function teak($data, $action = 'send') {
        $ptqm = new shopVkshopPluginProductsTempQueueModel();
        $count = $ptqm->countAll($action);
        $data['memory'] = memory_get_peak_usage();
        $data['memory_avg'] = memory_get_usage();
        //$data['current'] = $data['current'] - 1;
        if (empty($count)) {
            $data['current'] = 0;
        }
        else {
            $data['current'] = $data['count'] - ($data['count'] - $count);
        }
        $data['processed_count'] = $data['count'] - $data['current'];
        return $data;
    }
    
}

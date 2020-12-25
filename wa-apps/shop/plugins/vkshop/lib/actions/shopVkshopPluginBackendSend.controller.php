<?php

/**
 * Created by PhpStorm.
 * User: snark | itfrogs.ru
 * Date: 7/23/15
 * Time: 10:44 PM
 */
class shopVkshopPluginBackendSendController extends waJsonController
{
    /**
     * @var shopVkshopPlugin $plugin
     */
    private $plugin;

    /**
     * shopVkshopPluginBackendSendController constructor.
     * @throws waException
     */
    function __construct()
    {
        $this->plugin = wa()->getPlugin('vkshop');
    }

    /**
     * @throws Exception
     * @throws waException
     */
    public function execute()
    {
        if (wa()->getUser()->getRights('shop', 'products')) {
            $group_model = new shopVkshopPluginGroupModel();
            $groups = $group_model->getLoginedGroups();

            $data = waRequest::post('data', null, waRequest::TYPE_ARRAY);

            if (!is_array($data['images']) || empty($data)) {
                $this->setError(_wp('No images to post'));
                return;
            }

            if (!$data['product_id']) {
                $this->setError(_wp('No product to post'));
                return;
            }

            if (empty($data['caption'])) {
                $this->setError(_wp('No message to post'));
                return;
            }

            if (empty($data['group_id'])) {
                $this->setError(_wp('No groups defined in the plugin settings'));
                return;
            }

            if (!isset($groups[$data['group_id']])) {
                $this->setError(_wp('No groups defined in the plugin settings'));
                return;
            }
            else {
                $group = $groups[$data['group_id']];
                /**
                 * @var shopVkshopPluginApi $vk
                 */
                $vk = $group['vk'];
            }

            if (!$group['auth']) {
                $this->setError(_wp('Group is not authorized'));
                return;
            }

            try {
                /**
                 * @var shopVkshopPlugin $plugin
                 */
                $plugin = wa()->getPlugin('vkshop');
                $settings = $plugin->getSettings();

                $tools = new shopVkshopPluginTools($vk);
                $product = new shopProduct($data['product_id']);
                $pm = new shopVkshopPluginProductsModel();

                $album_model = new shopVkshopPluginAlbumsModel();

                $album_id = 0;
                if (!empty($data['vk_new_album'])) {
                    $album = $album_model->getByField('name', trim($data['vk_new_album']));

                    if (empty($album)) {
                        $parameters = array(
                            'owner_id' => '-' . $data['group_id'],
                            'main_album' => 0,
                            'title' => trim($data['vk_new_album']),
                        );
                        $r = $vk->api('market.addAlbum', $parameters);

                        if (!isset($r['error']) && isset($r['response']) && isset($r['response']['market_album_id'])) {
                            $insert_data = array(
                                'name' => trim($data['vk_new_album']),
                                'type' => 'custom',
                                'group_id' => $data['group_id'],
                                'album_id' => $r['response']['market_album_id'],
                            );
                            $album_model->insert($insert_data);
                            $album_id = $r['response']['market_album_id'];
                        }
                    } else {
                        $album_id = $album['album_id'];
                    }
                } else {
                    $album_id = intval($data['vk_album_id']);
                }

                $product_params = array();
                $product_params['id'] = $product['id'];
                $product_params['name'] = mb_substr($data['name'], 0, 99, 'utf-8');
                $product_params['price'] = intval($data['price']) > 0 ? intval($data['price']) : 0;
                $product_params['description'] = $data['caption'];
                $product_params['vk_cat_id'] = $data['vk_cat_id'];
                //$product_params['main_album_id'] = $main_album_id;
                $product_params['album_id'] = $album_id;
                $product_params['group_id'] = $data['group_id'];

                if ($group['settlement'] && strlen($group['settlement']) > 4) {
                    $product_params['full_url'] = $group['settlement'] . $product->url;
                } else {
                    $routing = wa('shop')->getRouting();
                    $params = array('product_url' => $product->url);
                    $product_params['full_url'] = $routing->getUrl('/frontend/product', $params, true);
                }

                $data['count'] = 1;
                $data['current'] = 1;
                $data['processed_count'] = 0;
                $data['maxcount'] = 1;
                $data['category_id'] = $data['vk_cat_id'];
                $data['album_id'] = $album_id;

                $params = array(
                    'vk_product'        => $pm->getByField(array('product_id' => $product->getId(), 'group_id' => $data['group_id'])),
                    'product_params'    => $product_params,
                    'settings'          => $settings,
                );

                if (!empty($params['vk_product'])) {
                    $data['upload_images'] = true;
                }

                $p = array(
                    'product_id'    => $product->getId(),
                    'group_id'      => $data['group_id'],
                    'action'        => 'send',
                );

                $tools->productSend($p, $params, $data);
            } catch (Exception $e) {
                $this->setError($e->getMessage());
                return;
            }

            $this->response[] = _wp('All done');
        } else {
            $this->setError(_wp('Access denied'));
        }
    }


}

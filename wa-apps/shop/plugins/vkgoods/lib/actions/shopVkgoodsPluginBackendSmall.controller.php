<?php

/**
 * @desc Набор мелких действий
 */
class shopVkgoodsPluginBackendSmallController extends waController
{

    public function execute()
    {
        $vkgoods = wa()->getPlugin('vkgoods');
        $settings = $vkgoods->getSettings();
        switch (waRequest::get('event')) {
            // Смена аккаунта ВК
            case "exitvk":
                $settings['token'] = '';
                $settings['vk_user_id'] = '';
                $vkgoods->saveSettings($settings);
                return;
                break;
            // Очистка очереди отложенных публикаций
            case "clearwait":
                $model = new shopVkgoodsPluginWaitproductModel();
                $model->query('TRUNCATE TABLE  shop_vkgoods_wait_product');
                return;
                break;
            // Удаление товара по кнопке со страницы товара
            case "del":
                $mvkg = new shopVkgoodsPluginProductModel();
                $vk_prods = $mvkg->getByField('pid', waRequest::post('pid'), true);
                $vksession = new shopVkgoodsPluginVkapi($settings['vk_user_id'], $settings['token']);
                foreach ($vk_prods as $vk_prod) {
                    $del_data = array(
                        'owner_id' => "-" . $vk_prod['gid'],
                        'item_id' => $vk_prod['vk_pid']
                    );
                    $res = $vksession->api("market.delete", $del_data);
                    if (isset($res->response) && $res->response == 1) {
                        $mvkg->deleteById($vk_prod['id']);
                    } else {}
                }
                
                break;
            // Неопубликованные товары (кнопка на тулбаре)
            case 'unpublished':
                $post = waRequest::post();
                $product_ids = array();
                if ($post['hash'] != '') {
                    $collection = $this->getCollectionByHash(urldecode($post['hash']));
                } else {
                    $collection = new shopProductsCollection(explode(',', $post['product_id']));
                }
                $model = new shopVkgoodsPluginProductModel();
                if ($pids_published = array_keys($model->getAll('pid'))) {
                    $collection->addWhere('p.id NOT IN(' . implode(',', $pids_published) . ')');
                }
                $product_ids = $this->getCollectionProductsIds($collection);
                
                $storage_hash = time();
                wa()->getStorage()->set('shop/vkgoods/vkgoods-' . $storage_hash, $product_ids);
                echo json_encode(array(
                    'hash' => $storage_hash,
                    'count' => count($product_ids)
                ));
                break;
            // Обновление товара по кнопке со страницы товара
            case 'upd':
                
                $model = new shopVkgoodsPluginProductModel();
                $vk_prods = $model->getByField('pid', waRequest::get('pid'), TRUE);
                
                $vk_session = new shopVkgoodsPluginVkapi($settings['vk_user_id'], $settings['token']);
                $data['settings'] = $settings;
                
                $info = array(
                    'del_vkitem' => 0,
                    'del_db' => 0,
                    'actual' => 0,
                    'get_vkitem' => 0,
                    'del_db' => 0,
                    'edit' => 0,
                    'done' => 0,
                    'unexp' => 0
                );
                
                foreach ($vk_prods as $vk_prod) {
                    $update = new shopVkgoodsPluginPublish($data);
                    $result = $update->goUpdate($vk_prod, $vk_session);
                    switch ($result['code']) {
                        case 'del_vkitem':
                            $info['del_vkitem'] += 1;
                            break;
                        case 'del_db':
                            $info['del_db'] += 1;
                            break;
                        case 'actual':
                            $info['actual'] += 1;
                            break;
                        case 'get_vkitem':
                            $info['get_vkitem'] += 1;
                            break;
                        case 'edit':
                            $info['edit'] += 1;
                            break;
                        case 'done':
                            $info['done'] += 1;
                            break;
                        default:
                            $info['unexp'] += 1;
                            break;
                    }
                }
                
                echo json_encode($info);
                break;
        }
    }

    protected function getCollectionProductsIds($collection)
    {
        $pids = array();
        for ($i = 0; $i < ceil($collection->count() / 200); $i += 1) {
            $pids = array_merge($pids, array_keys($collection->getProducts('*', $i * 200, 200)));
        }
        return $pids;
    }

    protected function getCollectionByHash($hash)
    {
        $collection = new shopProductsCollection($hash);
        $ahash = explode('/', $hash);
        if ($ahash[0] == 'set') {
            $model_set = new shopSetModel();
            $set = $model_set->getById($ahash[1]);
            if ($set['type'] == shopSetModel::TYPE_DYNAMIC) {
                $pids = array();
                for ($i = 0; $i < ceil($collection->count() / 200); $i += 1) {
                    $pids = array_merge($pids, array_keys($collection->getProducts('*', $i * 200, 200)));
                }
                $collection = new shopProductsCollection($pids);
            }
        }
        return $collection;
    }
} 
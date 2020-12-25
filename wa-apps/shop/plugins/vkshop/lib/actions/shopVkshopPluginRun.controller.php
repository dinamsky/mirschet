<?php

/**
 * Created by PhpStorm.
 * User: snark | itfrogs.ru
 */
class shopVkshopPluginRunController extends waLongActionController
{
    /**
     * @var shopProduct $product
     */
    private $product;
    /**
     * @var array
     */
    protected $params = array(
        'vk_product'        => array(),
        'product_params'    => array(),
        'settings'          => array(),
    );

    /**
     *
     */
    public function execute()
    {
        try {
            parent::execute();
        } catch (waException $e) {
            if ($e->getCode() == '302') {
                echo json_encode(array('warning' => $e->getMessage()));
            } else {
                echo json_encode(array('error' => $e->getMessage()));
            }
        }
    }

    /**
     * Initializes new process.
     * Runs inside a transaction ($this->data and $this->fd are accessible).
     */
    protected function init()
    {
        try {
            $this->plugin = wa()->getPlugin('vkshop');
            $this->params['settings'] = $this->plugin->getSettings();

            $access_token = $this->params['settings']['access_token'];

            $user_id = $this->params['settings']['user_id'];
            $secret = $this->params['settings']['secret'];

            $group_model = new shopVkshopPluginGroupModel();
            $groups = $group_model->getLoginedGroups();

            $options = waRequest::post();
            $options['processId'] = $this->processId;

            if (isset($groups[$options['config']['group_id']])) {
                $group = $groups[$options['config']['group_id']];

                /**
                 * @var shopVkshopPluginApi $vk
                 */
                $vk = $group['vk'];

                $action = $options['config']['action'];

                $new = (isset($options['config']['new']) && $options['config']['new'] == 1) ? true : false;
                $upload_images = (isset($options['config']['images']) && $options['config']['images'] == 1) ? true : false;
                $in_stock = (isset($options['config']['in_stock']) && $options['config']['in_stock'] == 1) ? true : false;
                $delete_nostock = (isset($options['config']['delete_nostock']) && $options['config']['delete_nostock'] == 1) ? true : false;

                $where_new = '';
                if ($new) {
                    $where_new = ' AND v.product_id IS NULL ';
                }
                $main_album_id = 0;
                $album_id = 0;
                if ($group['auth']) {
                    $ptqm = new shopVkshopPluginProductsTempQueueModel();
                    $model = new waModel();
                    $album_model = new shopVkshopPluginAlbumsModel();

                    switch ($action) {
                        case 'all':
                            $count = $ptqm->queueAll($new, $options['config']['group_id'], 'send');

                            if (!empty($options['config']['vk_new_album'])) {
                                $album = $album_model->getByField('name', trim($options['config']['vk_new_album']));

                                if (empty($album)) {
                                    $parameters = array(
                                        'owner_id' => '-' . $options['config']['group_id'],
                                        'main_album' => 0,
                                        'title' => trim($options['config']['vk_new_album']),
                                    );
                                    $album_id = $vk->addAlbum($parameters, 'custom', trim($options['config']['vk_new_album']), null, $options['config']['group_id']);
                                } else {
                                    $album_id = $album['album_id'];
                                }
                            } else {
                                $album_id = intval($options['config']['vk_album_id']);
                            }
                            break;
                        case 'queue':
                            $products = $model->query(
                                'SELECT p.id FROM shop_product p '
                                . ' LEFT JOIN shop_vkshop_products v ON p.id = v.product_id AND v.group_id = i:group_id '
                                . ' JOIN shop_vkshop_products_queue q ON p.id = q.product_id '
                                . ' WHERE 1 ' . $where_new,
                                array(
                                    'group_id' => $options['config']['group_id'],
                                )
                            )->fetchAll();
                            $count = count($products);

                            $products_ids = array();
                            foreach ($products as $key => $product) {
                                $products[$key]['group_id'] = $options['config']['group_id'];
                                $products_ids[] = $product['id'];
                            }
                            $ptqm->unqueueProducts($products_ids, 'send');
                            $ptqm->queueProducts($products, 'send');

                            if (!empty($options['config']['vk_new_album'])) {
                                $album = $album_model->getByField('name', trim($options['config']['vk_new_album']));

                                if (empty($album)) {
                                    $parameters = array(
                                        'owner_id' => '-' . $options['config']['group_id'],
                                        'main_album' => 0,
                                        'title' => trim($options['config']['vk_new_album']),
                                    );
                                    $album_id = $vk->addAlbum($parameters, 'custom', trim($options['config']['vk_new_album']), null, $options['config']['group_id']);
                                } else {
                                    $album_id = $album['album_id'];
                                }
                            } else {
                                $album_id = intval($options['config']['vk_album_id']);
                            }

                            break;
                        case 'delete':
                            $products = $model->query(
                                'SELECT p.id FROM shop_product p '
                                . ' LEFT JOIN shop_vkshop_products v ON p.id = v.product_id AND v.group_id = i:group_id '
                                . ' JOIN shop_vkshop_products_queue q ON p.id = q.product_id ',
                                array(
                                    'group_id' => $options['config']['group_id'],
                                )
                            )->fetchAll();
                            $count = count($products);
                            $products_ids = array();
                            foreach ($products as $key => $product) {
                                $products[$key]['group_id'] = $options['config']['group_id'];
                                $products_ids[] = $product['id'];
                            }
                            $ptqm->unqueueProducts($products_ids, 'delete');
                            $ptqm->queueProducts($products, 'delete');
                            break;
                        case 'set':
                            $products = $model->query(
                                'SELECT s.product_id AS id FROM shop_set_products s '
                                . ' JOIN shop_product p ON s.product_id = p.id '
                                . ' LEFT JOIN shop_vkshop_products v ON s.product_id = v.product_id AND v.group_id = i:group_id '
                                . ' WHERE 1 AND s.set_id = s:set_id ' . $where_new,
                                array(
                                    'set_id' => $options['config']['set_id'],
                                    'group_id' => $options['config']['group_id'],
                                )
                            )->fetchAll();

                            $count = count($products);
                            $products_ids = array();
                            foreach ($products as $key => $product) {
                                $products[$key]['group_id'] = $options['config']['group_id'];
                                $products_ids[] = $product['id'];
                            }
                            $ptqm->unqueueProducts($products_ids, 'send');
                            $ptqm->queueProducts($products, 'send');

                            $set_model = new shopSetModel();
                            $set = $set_model->getById($options['config']['set_id']);

                            $album = $album_model->getAlbumBySetId($options['config']['set_id']);

                            if ($album['album_id'] == null) {
                                $parameters = array(
                                    'owner_id' => '-' . $options['config']['group_id'],
                                    'main_album' => 0,
                                    'title' => $album['name'],
                                );
                                $album_id = $vk->addAlbum($parameters, 'set', $set['name'], $options['config']['set_id'], $options['config']['group_id']);
                            } else {
                                $album_id = $album['album_id'];
                            }

                            break;
                        case 'type':
                            $products = $model->query(
                                'SELECT p.id FROM shop_product p '
                                . ' LEFT JOIN shop_vkshop_products v ON p.id = v.product_id  AND v.group_id = i:group_id '
                                . ' WHERE 1 AND type_id = ' . intval($options['config']['type_id']) . $where_new,
                                array(
                                    'group_id' => $options['config']['group_id'],
                                )
                            )->fetchAll();
                            $count = count($products);
                            $products_ids = array();
                            foreach ($products as $key => $product) {
                                $products[$key]['group_id'] = $options['config']['group_id'];
                                $products_ids[] = $product['id'];
                            }
                            $ptqm->unqueueProducts($products_ids, 'send');
                            $ptqm->queueProducts($products, 'send');

                            $type_model = new shopTypeModel();
                            $type = $type_model->getById($options['config']['type_id']);

                            $album = $album_model->getAlbumByTypeId($options['config']['type_id']);

                            if ($album['album_id'] == null) {
                                $parameters = array(
                                    'owner_id' => '-' . $options['config']['group_id'],
                                    'main_album' => 0,
                                    'title' => $album['name'],
                                );
                                $album_id = $vk->addAlbum($parameters, 'type', $type['name'], $options['config']['type_id'], $options['config']['group_id']);
                            } else {
                                $album_id = $album['album_id'];
                            }

                            break;
                        default:
                            $count = 0;
                            break;
                    }
                } else {
                    $count = 0;
                }

                $this->data += array(
                    'timestamp' => time(),
                    'count' => $count,
                    'current' => $count,
                    'set_id' => $options['config']['set_id'],
                    'type_id' => isset($options['config']['type_id']) ? $options['config']['type_id'] : 0,
                    'category_id' => $options['config']['category_id'],
                    'maxcount' => $options['config']['maxcount'],
                    'temppath' => wa()->getTempPath(),
                    'main_album_id' => $main_album_id,
                    'album_id' => $album_id,
                    'group_id' => $options['config']['group_id'],
                    'upload_images' => $upload_images,
                    'in_stock' => $in_stock,
                    'delete_nostock' => $delete_nostock,
                    'action' => $action,
                    'access_token' => $access_token,
                    'user_id' => $user_id,
                    'secret' => $secret,
                    'currency' => $this->params['settings']['currency'],
                    'busy' => false,
                    'processed_count' => 0,
                    'error' => null,
                    'memory' => memory_get_peak_usage(),
                    'memory_avg' => memory_get_usage(),
                );
            }
            else {
                $this->isDone();
            }
        } catch (waException $e) {
            echo json_encode(array('error' => $e->getMessage()));
        }
    }


    /**
     * Performs a small piece of work.
     * Runs inside a transaction ($this->data and $this->fd are accessible).
     *
     * The longer it takes to complete one step, the more time it is possible to lose if script fails.
     * The shorter, the more overhead there are because of copying $this->data and $this->fd after
     * each step. So, it should be reasonably long and reasonably short at the same time.
     * 5-10% of max execution time is recommended.
     *
     * $this->getStorage() session is already closed.
     *
     * @var shopVkshopPluginApi $vk
     * @return boolean false to end this Runner and call info(); true to continue.
     * @throws waException
     */
    protected function step()
    {
        if ($this->data['busy']) {
            return !$this->isDone();
        }

        $this->plugin = wa()->getPlugin('vkshop');
        $this->params['settings'] = $this->plugin->getSettings();

        $group_model = new shopVkshopPluginGroupModel();
        $groups = $group_model->getLoginedGroups();


        //$tools = new shopVkshopPluginTools($vk);

        $pqm = new shopVkshopPluginProductsQueueModel();
        $ptqm = new shopVkshopPluginProductsTempQueueModel();
        $pm = new shopVkshopPluginProductsModel();

        if (!$this->data['busy'] && $this->data['action'] == 'delete') {
            $this->data['busy'] = true;
            $products = $ptqm->getProducts(intval($this->params['settings']['stack_count']), 'delete');
            foreach ($products as $key => $p) {
                if (isset($groups[$p['group_id']]) && $groups[$p['group_id']]['auth']) {
                    $vk = $groups[$p['group_id']]['vk'];
                    $this->params['vk_product'] = $pm->getByField(array('product_id' => $p['product_id'], 'group_id' => $p['group_id']));
                    $tools = new shopVkshopPluginTools($vk);
                    $this->data = $tools->productDelete($p, $this->params, $this->data);
                }
            }
            $this->data['busy'] = false;
        } elseif (!$this->data['busy']) {
            $this->data['busy'] = true;
            $products = $ptqm->getProducts(intval($this->params['settings']['stack_count']));

            foreach ($products as $key => $p) {
                if (isset($groups[$p['group_id']]) && $groups[$p['group_id']]['auth']) {
                    $vk = $groups[$p['group_id']]['vk'];
                    $tools = new shopVkshopPluginTools($vk);
                    $this->params['vk_product'] = $pm->getByField(array('product_id' => $p['product_id'], 'group_id' => $p['group_id']));
                    $this->data = $tools->productSend($p, $this->params, $this->data);
                }
            }
            $this->data['busy'] = false;
        }

        return !$this->isDone();
    }

    /**
     * Checks if there is any more work for $this->step() to do.
     * Runs inside a transaction ($this->data and $this->fd are accessible).
     *
     * $this->getStorage() session is already closed.
     *
     * @return boolean whether all the work is done
     */
    protected function isDone()
    {
        if ($this->data['current'] == 0) {
            $done = true;
        } else {
            $done = false;
        }
        return $done;
    }

    /**
     *
     */
    protected function info()
    {
        $interval = 0;
        if (!empty($this->data['timestamp'])) {
            $interval = time() - $this->data['timestamp'];
        }
        $response = array(
            'time' => sprintf('%d:%02d:%02d', floor($interval / 3600), floor($interval / 60) % 60, $interval % 60),
            'processId' => $this->processId,
            'stage' => false,
            'progress' => 0.0,
            'ready' => $this->isDone(),
            'count' => empty($this->data['count']) ? false : $this->data['count'],
            'memory' => sprintf('%0.2fMByte', $this->data['memory'] / 1048576),
            'memory_avg' => sprintf('%0.2fMByte', $this->data['memory_avg'] / 1048576),
        );

        if ($this->data['count'] > 0) {
            $percent = 100.0 * $this->data['processed_count'] / $this->data['count'];
            if ($percent > 100) {
                $percent = 100;
            }
            $response['progress'] = sprintf('%0.3f%%', $percent);
        } else {
            $response['progress'] = '100%';
        }


        $response['current_count'] = $this->data['current'];
        $response['processed_count'] = $this->data['processed_count'];
        if ($response['ready']) {
            $response['report'] = $this->report();
        }
        echo json_encode($response);
    }

    /**
     * @return string
     */
    protected function report()
    {
        $report = '<div class="successmsg">';
        $report .= sprintf('<i class="icon16 yes"></i>%s ', _wp('Successfully'));
        $chunks = array();

        $report .= implode(', ', $chunks);
        if (!empty($this->data['timestamp'])) {
            $interval = time() - $this->data['timestamp'];
            $interval = sprintf(
                _wp('%02d hr %02d min %02d sec'), floor($interval / 3600), floor($interval / 60) % 60,
                $interval % 60
            );
            $report .= ' ' . sprintf(_wp('(total time: %s)'), $interval);
        }
        $report .= '</div>';
        return $report;
    }

    /**
     * Called when $this->isDone() is true
     * $this->data is read-only, $this->fd is not available.
     *
     * $this->getStorage() session is already closed.
     *
     * @param  $filename string full path to resulting file
     * @return boolean true to delete all process files; false to be able to access process again.
     */
    protected function finish($filename)
    {
        $this->info();
        $result = false;
        if ($this->getRequest()->post('cleanup')) {
            $result = true;

        }
        return $result;
    }

    /**
     * @param $filename
     * @return CURLFile|string
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

}
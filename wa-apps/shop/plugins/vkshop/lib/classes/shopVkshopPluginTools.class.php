<?php

/**
 * Created by PhpStorm.
 * User: snark | itfrogs.ru
 * Date: 5/29/16
 * Time: 12:47 AM
 */
class shopVkshopPluginTools
{
    /**
     * @var shopVkshopPluginProductsQueueModel
     */
    private $pqm;
    
    /**
     * @var shopVkshopPluginProductsTempQueueModel
     */
    private $ptqm;
    
    /**
     * @var shopVkshopPluginProductsModel
     */
    private $pm;

    /**
     * @var shopVkshopPluginProductsCronModel
     */
    private $cm;
    
    /**
     * @var shopVkshopPluginImagesModel
     */
    private $vk_images_model;

    /**
     * @var shopVkshopPluginApi $vk
     */
    protected $vk;

    /**
     * @var waView $view
     */
    private $view;

    /**
     * @var shopVkshopPlugin $plugin
     */
    private $plugin;

    /**
     * @var string
     */
    private $admin_url;

    /**
     * shopVkshopPluginTools constructor.
     * @param $vk
     * @throws waException
     */
    function __construct($vk)
    {
        $this->vk = $vk;
        $this->pqm = new shopVkshopPluginProductsQueueModel();
        $this->ptqm = new shopVkshopPluginProductsTempQueueModel();
        $this->pm = new shopVkshopPluginProductsModel();
        $this->cm = new shopVkshopPluginProductsCronModel();
        $this->vk_images_model = new shopVkshopPluginImagesModel();
        $this->products_albums_model = new shopVkshopPluginProductsAlbumsModel();
        $this->view = waSystem::getInstance()->getView();
        $this->plugin = wa()->getPlugin('vkshop');
        $this->admin_url = wa()->getUrl(true);
    }


    /**
     * @return bool
     * @throws waException
     */
    public function compareAlbums()
    {
        $album_model = new shopVkshopPluginAlbumsModel();
        $group_model = new shopVkshopPluginGroupModel();
        $groups = $group_model->getLoginedGroups();

        foreach ($groups as $group) {
            if (!$group['auth']) continue;

            $vk_albums = array();
            $parameters = array(
                'owner_id' => '-' . $group['id'],
            );
            $r = $group['vk']->api('market.getAlbums', $parameters);

            if (!isset($r['error']) && isset($r['response']) && isset($r['response']['items'])) {
                foreach ($r['response']['items'] as $vk_album) {
                    if (is_array($vk_album) && isset($vk_album['id'])) {
                        $vk_albums[] = $vk_album['id'];
                    }
                }
                $vkshop_albums = $album_model->select('album_id')->where('group_id = ' . intval($group['id']))->fetchAll('album_id');
                if (!empty($vkshop_albums)) {
                    $vkshop_albums = array_keys($vkshop_albums);
                }
                $diff = array_diff($vk_albums, $vkshop_albums);
                if (empty($diff)) {
                    $diff = array_diff($vkshop_albums, $vk_albums);
                }

                if (!empty($diff)) {
                    foreach ($diff as $value) {
                        if (in_array($value, $vkshop_albums)) {
                            $album_model->deleteByField('album_id', $value);
                        } else {
                            foreach ($r['response']['items'] as $vk_album) {
                                if (is_array($vk_album)) {
                                    if (isset($vk_album['id']) && isset($vk_album['title']) && $vk_album['id'] == $value) {
                                        $album_name = $vk_album['title'];

                                        if (!empty($album_name)) {
                                            $insert_data = array(
                                                'name' => $album_name,
                                                'type' => 'custom',
                                                'album_id' => $value,
                                                'group_id' => $group['id'],
                                            );
                                            $album_model->insert($insert_data);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                /*
                 * Если подборку переименовали, то нам тоже нужно ее переименовать.
                 */
                $vkshop_albums = $album_model->select('*')->fetchAll('album_id');
                foreach ($r['response'] as $vk_album) {
                    if (is_array($vk_album)) {
                        if (isset($vk_album['id']) && !empty($vkshop_albums[$vk_album['id']])) {
                            $vkshop_album = $vkshop_albums[$vk_album['id']];
                            if ($vk_album['title'] != $vkshop_album['name']) {
                                $vkshop_album['name'] = $vk_album['title'];
                                $vkshop_album['group_id'] = $group['id'];
                                $album_model->updateById($vkshop_album['id'], $vkshop_album);
                            }
                        }
                    }
                }
                return true;
            }
            else return $r['error'];
        }
    }

    /**
     * @var shopProduct $product
     * @param $p
     * @param $params
     * @param $data
     * @return mixed
     * @throws Exception
     * @throws waException
     */
    public function productSend($p, $params, $data) {
        $product = new shopProduct($p['product_id']);
        $product->group_id = $p['group_id'];

        $group_model = new shopVkshopPluginGroupModel();
        $groups = $group_model->getLoginedGroups();

        if (isset($groups[$p['group_id']])) {
            $group = $groups[$p['group_id']];
        }

        $images = $product->getImages();

        if (!isset($data['temppath']) || empty($data['temppath'])) {
            $data['temppath'] = wa()->getTempPath();
        }

        if (isset($data['images'])) {
            foreach ($images as $key => $image) {
                if (!in_array($image['id'], $data['images'])) {
                    unset($images[$key]);
                }
            }
        }

        if (!isset($p['action']) || empty($p['action'])) {
            $p['action'] = 'send';
        }

        /*
         * Пропускаем товар, если в нем нет картинок
         */
        if (empty($images) && empty($params['vk_product'])) {
            $this->ptqm->deleteByField(
                array(
                    'product_id' => $product->getId(),
                    'action' => $p['action'],
                )
            );
            $this->pqm->deleteById($product->getId());
            return $this->vk->teak($data);
        }
        /*
         * Удаляем товар из вк, если у товара нет картинок.
         */
        elseif (empty($images) && !empty($params['vk_product'])) {
            $parameters = array(
                'owner_id' => '-' . $p['group_id'],
                'item_id' => $params['vk_product']['item_id'],
            );
            $result = $this->vk->api('market.delete', $parameters);

            if (isset($result['error']) || !isset($result['response'])) {
                $this->ptqm->increaseCount($product->getId(), $p['group_id'], $data['maxcount'], $p['action']);
                //Логгирование ошибок
                if ($params['settings']['error_log']) {
                    waLog::log('No pictures. Product delete error:', 'vkshop-delete.log');
                    waLog::log($product['name'], 'vkshop-delete.log');
                    waLog::log($this->admin_url . '?action=products#/product/'.$product->getId().'/', 'vkshop-delete.log');
                    waLog::dump($result['error'], 'vkshop-delete.log');
                }
            } else {
                $this->ptqm->deleteByField(array('product_id' => $product->getId(), 'group_id' => $p['group_id'], 'action' => $p['action']));
                $this->pqm->deleteByField(array('product_id' => $p['product_id'], 'group_id' => $p['group_id']));
                $this->pm->deleteByField(array('product_id' => $p['product_id'], 'group_id' => $p['group_id']));
                $this->products_albums_model->deleteByField(array('product_id' => $p['product_id'], 'group_id' => $p['group_id']));

                $vk_images = $this->vk_images_model->getByField(array('product_id' => $p['product_id'], 'group_id' => $p['group_id']), true);
                $vk_images_ids = array();
                foreach ($vk_images as $vk_image) {
                    $vk_images_ids[] = $vk_image['id'];
                }
                $this->vk_images_model->deleteByField('id', $vk_images_ids);

                return $this->vk->teak($data);
            }
        }

        if ($product['count'] === '0' || $product['count'] === 0) {
            if ($data['in_stock']) {
                $this->ptqm->deleteByField(array('product_id' => $product->getId(), 'group_id' => $p['group_id'], 'action' => $p['action']));
                $this->pqm->deleteById($product->getId());
                $this->pm->deleteByField(array('product_id' => $product->getId(), 'group_id' => $p['group_id']));
                $this->products_albums_model->deleteByField(array('product_id' => $product->getId(), 'group_id' => $p['group_id']));
                return $this->vk->teak($data);
            }

            if ($data['delete_nostock'] && !empty($params['vk_product'])) {
                $parameters = array(
                    'owner_id' => '-' . $p['group_id'],
                    'item_id' => $params['vk_product']['item_id'],
                );
                $result = $this->vk->api('market.delete', $parameters);

                if (isset($result['error']) || !isset($result['response'])) {
                    $this->ptqm->increaseCount($product->getId(), $p['group_id'], $data['maxcount'], $p['action']);
                    //Логгирование ошибок
                    if ($params['settings']['error_log']) {
                        waLog::log('Not in stock. Product delete error:', 'vkshop-delete.log');
                        waLog::log($product['name'], 'vkshop-delete.log');
                        waLog::log($this->admin_url . '?action=products#/product/'.$product->getId().'/', 'vkshop-delete.log');
                        waLog::dump($result['error'], 'vkshop-delete.log');
                    }
                } else {
                    $this->ptqm->deleteByField(array('product_id' => $product->getId(), 'group_id' => $p['group_id'], 'action' => $p['action']));
                    $this->pqm->deleteById($product->getId());
                    $this->pm->deleteByField(array('product_id' => $product->getId(), 'group_id' => $p['group_id']));
                    $this->products_albums_model->deleteByField(array('product_id' => $product->getId(), 'group_id' => $p['group_id']));

                    $vk_images = $this->vk_images_model->getByField('product_id', $product->getId(), true);
                    $vk_images_ids = array();
                    foreach ($vk_images as $vk_image) {
                        $vk_images_ids[] = $vk_image['id'];
                    }
                    $this->vk_images_model->deleteByField('id', $vk_images_ids);
                }
                return $this->vk->teak($data);
            }
        }

        if (isset($group) && strlen($group['settlement']) > 4) {
            $params['product_params']['full_url'] = $group['settlement'] . $product->url;
        } else {
            $routing = wa('shop')->getRouting();
            $params = array('product_url' => $product->url);
            $params['product_params']['full_url'] = $routing->getUrl('/frontend/product', $params, true);
        }

        $prepare = new shopVkshopPluginPrepare($product, $params['settings']);
        $product = $prepare->prepareHashtags()->prepareLink()->prepareFeatures()->getProduct();

        $this->view->assign('product', $product);

        if (isset($data['caption'])) {
            $caption = $data['caption'];
        }
        else {
            $caption = $this->view->fetch('string:' . $this->plugin->getSettings('caption_tmpl'));
        }
        $caption = html_entity_decode($caption);
        $caption = str_replace("<br>", "\r\n", $caption);
        $caption = str_replace("<br />", "\r\n", $caption);
        $caption = str_replace("<br/>", "\r\n", $caption);
        $caption = mb_substr(
            strip_tags($caption), 0, intval($params['settings']['max_description_lenght']),
            'utf-8'
        );

        $params['product_params']['id'] = $product->getId();
        $params['product_params']['name'] = mb_substr(strip_tags($product['name']), 0, 99, 'utf-8');

        if (isset($data['name'])) {
            $params['product_params']['name'] = mb_substr(strip_tags($data['name']), 0, 99, 'utf-8');
        }

        if ($params['settings']['convert_currency']) {
            $params['product_params']['price'] = round(
                shop_currency(
                    $product['price'],
                    $product['currency'], $data['currency'], false
                )
            );
        } else {
            $params['product_params']['price'] = round($product['price']);
        }

        if (isset($data['price'])) {
            $params['product_params']['price'] = $data['price'];
        }

        $params['product_params']['status'] = $product['status'];
        $params['product_params']['description'] = $caption;
        $params['product_params']['category_id'] = $data['category_id'];
        $params['product_params']['group_id'] = $p['group_id'];



        $uploaded_images = array();

        if (empty($params['vk_product']) || $data['upload_images']) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: multipart/form-data"));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);

            $q = 0;
            $photos = array();
            foreach ($images AS $image) {
                $imagePath = shopImage::getThumbsPath($image, $params['settings']['image_size']);

                if (filesize($imagePath) < 500) {
                    @waFiles::delete($imagePath);
                }

                if (! file_exists($imagePath)) {
                    shopImage::generateThumbs($image, array($params['settings']['image_size']));
                    $imagePath = shopImage::getThumbsPath($image, $params['settings']['image_size']);
                }

                try {
                    $temp_image = waImage::factory($imagePath);
                } catch (waException $e) {
                    waLog::log('Image parse error:', 'vkshop-images.log');
                    waLog::log($product['name'], 'vkshop-images.log');
                    waLog::log($this->admin_url . '?action=products#/product/'.$product->getId().'/', 'vkshop-images.log');
                    waLog::log($e['error_code'], 'vkshop-images.log');
                    waLog::log($e['error_msg'], 'vkshop-images.log');
                    continue;
                }

                if ($temp_image->width > $temp_image->height) {
                    $crop_width = $temp_image->height;
                    $crop_side = 'h';
                    $crop_y = 0;
                    $crop_x = round(($temp_image->width - $temp_image->height) / 2);
                } else {
                    $crop_width = $temp_image->width;
                    $crop_side = 'w';
                    $crop_x = 0;
                    $crop_y = round(($temp_image->height - $temp_image->width) / 2);
                }

                if ($temp_image->width < 400 || $temp_image->height < 400) {
                    if ($crop_side == 'w') {
                        $temp_image->resize(400, null, 'WIDTH', false);
                        $crop_width = $temp_image->width;
                        $crop_x = 0;
                        //$crop_y = round(($temp_image->height - $temp_image->width) / 2);
                        $crop_y = 200;
                    } else {
                        $temp_image->resize(null, 400, 'WIDTH', false);
                        $crop_width = $temp_image->height;
                        $crop_y = 0;
                        //$crop_x = round(($temp_image->width - $temp_image->height) / 2);
                        $crop_x = 200;
                    }

                    $imagePath = $data['temppath'] . '/vkshop_tempimage.jpg';
                    $temp_image->save($imagePath);
                }

                $parameters = array(
                    'group_id' => $p['group_id'],
                    'main_photo' => $q == 0 ? 1 : 0,
                    'imagepath' => $imagePath,
                    'crop_x' => $crop_x,
                    'crop_y' => $crop_y,
                    'crop_width' => $crop_width,
                );
                /*
                 * Кроп нужен только для основного фото :)
                 */
                if ($q != 0) {
                    unset($parameters['crop_x']);
                    unset($parameters['crop_y']);
                    unset($parameters['crop_width']);
                }

                $r = $this->vk->api('photos.getMarketUploadServer', $parameters);
                if (!isset($r['error']) && isset($r['response']) && isset($r['response']['upload_url'])) {
                    $upload_url = $r['response']['upload_url'];
                    curl_setopt($ch, CURLOPT_URL, $upload_url);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, array('file1' => self::getCurlFile($imagePath)));
                    $result = json_decode(curl_exec($ch));
                    $photos[] = $result;
                }

                @waFiles::delete($data['temppath'] . '/vkshop_tempimage.jpg');

                $q++;

                if ($q > 4) {
                    break;
                }
            }
            curl_close($ch);

            if (!empty($photos)) {
                foreach ($photos as $photo) {
                    if (!isset($photo->error)) {
                        $parameters = array(
                            'group_id' => $p['group_id'],
                            'server' => $photo->server,
                            'photo' => $photo->photo,
                            'hash' => $photo->hash,
                        );
                        if (isset($photo->crop_data)) {
                            $parameters['crop_data'] = $photo->crop_data;
                        }
                        if (isset($photo->crop_hash)) {
                            $parameters['crop_hash'] = $photo->crop_hash;
                        }

                        $result = $this->vk->api('photos.saveMarketPhoto', $parameters);

                        if (isset($result['error'])) {
                            $error = $result['error'];
                            $errors[] = array(
                                'error' => 'photos.save. ' . $error['error_code'],
                                'error_description' => $error['error_msg'],
                            );
                        } else {
                            $uploaded_images[] = $result['response'];
                        }
                    }
                }

                $this->vk_images_model->deleteByField('product_id', $product->getId());
                $images_data = array();
                foreach ($uploaded_images as $i => $image) {
                    array_push(
                        $images_data, array(
                            'product_id'    => $product->getId(),
                            'vk_photo_id'   => $image[0]['id'],
                            'group_id'      => $p['group_id'],
                            'type'          => $i == 0 ? 'main' : null,
                        )
                    );
                }
                if (!empty($images_data)) {
                    $this->vk_images_model->multipleInsert($images_data);
                }
            }
        }

        $temp_images = array();
        foreach ($uploaded_images as $i => $image) {
            $temp_images[] = $image[0];
        }
        $uploaded_images = $temp_images;

        if (!empty($params['vk_product'])) {

            if (!empty($uploaded_images) && $data['upload_images']) {
                $vk_images = $this->vk_images_model->getByField('product_id', $product->getId(), true);
                $vk_images_ids = array();
                foreach ($vk_images as $vk_image) {
                    $vk_images_ids[] = $vk_image['id'];
                }
                $result = $this->vk->saveProduct($params['product_params'], $uploaded_images, true, $params['vk_product']);
                $data = $this->vk->parseError($data, $product, $result, $p['action']);
                if (isset($data['error'])) {
                    //Логгирование ошибок
                    if ($params['settings']['error_log']  && is_array($data['error']) && isset($data['error']['error_code']) && isset($data['error']['error_msg'])) {
                        /*
                         * Если вдруг оказывается, что товара нет в группе, загружаем его заново.
                         */
                        if ($data['error']['error_msg'] === 'Item not found') {
                            $this->pm->deleteByField(array('product_id' => $p['product_id'], 'group_id' => $p['group_id']));
                            if (!empty($uploaded_images)) {
                                $result = $this->vk->saveProduct($params['product_params'], $uploaded_images, false);
                                $data = $this->vk->parseError($data, $product, $result, $p['action']);
                                if (isset($data['error'])) {
                                    //Логгирование ошибок
                                    if ($params['settings']['error_log']  && is_array($data['error']) && isset($data['error']['error_code']) && isset($data['error']['error_msg'])) {
                                        waLog::log('Item not found repair error:', 'vkshop-new.log');
                                        waLog::log($product['name'], 'vkshop-new.log');
                                        waLog::log($this->admin_url . '?action=products#/product/'.$product->getId().'/', 'vkshop-new.log');
                                        waLog::log($data['error']['error_code'], 'vkshop-new.log');
                                        waLog::log($data['error']['error_msg'], 'vkshop-new.log');
                                    }
                                }
                                else {
                                    $params['vk_product']['datetime'] = date('Y-m-d H:i:s');
                                    $this->pm->updateById($params['vk_product']['id'], $params['vk_product']);
                                }
                            }
                        } else {
                            waLog::log('Product edit error:', 'vkshop-edit.log');
                            waLog::log($product['name'], 'vkshop-edit.log');
                            waLog::log($this->admin_url . '?action=products#/product/'.$product->getId().'/', 'vkshop-edit.log');
                            waLog::log($data['error']['error_code'], 'vkshop-edit.log');
                            waLog::log($data['error']['error_msg'], 'vkshop-edit.log');
                        }
                        unset($data['error']);
                    }
                    else {
                        $params['vk_product']['datetime'] = date('Y-m-d H:i:s');
                        $this->pm->updateById($params['vk_product']['id'], $params['vk_product']);
                    }
                } else {
                    $this->vk_images_model->deleteByField('id', $vk_images_ids);
                }
            } else {
                /*
                 * товары старые, картинки старые. только редактирование
                 *
                 * Export images (не отмечено)
                 * Export only new products (не отмечено)
                 *
                 */

                $vk_images = $this->vk_images_model->getByField('product_id', $product->getId(), true);
                $images = array();

                if (isset($main_image)) {
                    unset($main_image);
                }

                foreach ($vk_images as $i => $image) {
                    if ($image['type'] == 'main') {
                        $main_image = $image;
                    } else {
                        $images[] = $image;
                    }
                }
                if (isset($main_image)) {
                    array_unshift($images, $main_image);
                }

                $result = $this->vk->saveProduct($params['product_params'], $images, true, $params['vk_product']);

                $data = $this->vk->parseError($data, $product, $result, $p['action']);

                if (isset($data['error'])) {
                    //Логгирование ошибок
                    if ($params['settings']['error_log'] && is_array($data['error']) && isset($data['error']['error_code']) && isset($data['error']['error_msg'])) {
                        waLog::log('Edit with no images error:', 'vkshop-edit.log');
                        waLog::log($product['name'], 'vkshop-edit.log');
                        waLog::log($this->admin_url . '?action=products#/product/'.$product->getId().'/', 'vkshop-edit.log');
                        waLog::log($data['error']['error_code'], 'vkshop-edit.log');
                        waLog::log($data['error']['error_msg'], 'vkshop-edit.log');
                    }

                    unset($data['error']);
                }
                else {
                    $params['vk_product']['datetime'] = date('Y-m-d H:i:s');
                    $this->pm->updateById($params['vk_product']['id'], $params['vk_product']);
                }
            }
        } else {
            if (!empty($uploaded_images)) {
                $result = $this->vk->saveProduct($params['product_params'], $uploaded_images, false);

                $data = $this->vk->parseError($data, $product, $result, $p['action']);

                if (isset($data['error'])) {
                    //Логгирование ошибок
                    if ($params['settings']['error_log'] && is_array($data['error']) && isset($data['error']['error_code']) && isset($data['error']['error_msg'])) {
                        waLog::log('New product upload error:', 'vkshop-new.log');
                        waLog::log($product['name'], 'vkshop-new.log');
                        waLog::log($this->admin_url . '?action=products#/product/'.$product->getId().'/', 'vkshop-new.log');
                        waLog::log($e['error_code'], 'vkshop-new.log');
                        waLog::log($e['error_msg'], 'vkshop-new.log');
                    }

                    unset($data['error']);
                }
            }
            if ($this->ptqm->increaseCount($product->getId(), $p['group_id'], $data['maxcount'], $p['action'])) {
                $data = $this->vk->teak($data);
            }
        }
        $data = $this->vk->teak($data);
        return $data;
    }


    /**
     * @param $p
     * @param $params
     * @param $data
     * @return mixed
     * @throws waException
     */
    public function productDelete($p, $params, $data) {
        $product = new shopProduct($p['product_id']);
        $product->group_id = $p['group_id'];
        if (!empty($params['vk_product'])) {
            $parameters = array(
                'owner_id' => '-' . $p['group_id'],
                'item_id' => $params['vk_product']['item_id'],
            );
            $result = $this->vk->api('market.delete', $parameters);

            if (isset($result['error']) || !isset($result['response'])) {
                $this->ptqm->increaseCount($product->getId(), $p['group_id'], $data['maxcount'], 'delete');
                //Логгирование ошибок
                if ($params['settings']['error_log']) {
                    waLog::log($product['name'], 'vkshop-delete.log');
                    waLog::log($this->admin_url . '?action=products#/product/'.$product->getId().'/', 'vkshop-delete.log');
                    waLog::dump($result['error'], 'vkshop-delete.log');
                }
            } else {
                $this->ptqm->deleteByField(array('product_id' => $p['product_id'], 'group_id' => $p['group_id'], 'action' =>  'delete'));
                $this->pqm->deleteById($product->getId());
                $this->pm->deleteByField(array('product_id' => $p['product_id'], 'group_id' => $p['group_id']));
                $this->products_albums_model->deleteByField(array('product_id' => $p['product_id'], 'group_id' => $p['group_id']));
                $this->cm->deleteByField(array('product_id' => $p['product_id'], 'group_id' => $p['group_id']));

                $vk_images = $this->vk_images_model->getByField('product_id', $product->getId(), true);
                $vk_images_ids = array();
                foreach ($vk_images as $vk_image) {
                    $vk_images_ids[] = $vk_image['id'];
                }
                $this->vk_images_model->deleteByField('id', $vk_images_ids);
            }
        } else {
            $this->ptqm->deleteByField(array('product_id' => $product->getId(), 'group_id' => $p['group_id'], 'action' => 'delete'));
            $this->pqm->deleteById($product->getId());
        }

        $data = $this->vk->teak($data, 'delete');
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
}
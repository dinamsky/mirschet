<?php

class shopVkgoodsPluginPublish
{

    protected $model_product_skus;
    protected $data;

    /**
     * Конструктор класса
     *
     * @param
     *            array данные для экспорта
     */
    public function __construct($data)
    {
        $this->data = $data;
        $this->model_product_skus = new shopProductSkusModel();
    }

    /*
     * @desc Публикация одного товара в соответствии с параметрами.
     * @param object shopVkgoodsPluginVkapi
     */
    public function goAdd($vk_session = false)
    {
        $vkm = new shopVkgoodsPluginProductModel();
        if (!$vk_session) {
            $vk_session = new shopVkgoodsPluginVkapi($this->data['settings']['vk_user_id'], $this->data['settings']['token']);
        }
        $data_upd = array();

        // Проверяем публиковался ли ранее этот товар в данном сообществе. Если да - проверяем не удален ли он и ставим флаг.
        $public_prod = $vkm->getByField(array(
            'pid' => $this->data['pid'],
            'gid' => $this->data['gid']
        ), false);
        $prd = new shopProduct($this->data['pid']);

        if (!$prd['edit_datetime']) {
            $prd['edit_datetime'] = $prd->create_datetime;
        }

        // Если товар скрыт с сайта или удален с сайта - пропускаем при публикации
        if ($prd->getId() == '' || $prd['status'] == 0) {
            if ($public_prod) {
                $vk_publics = $vkm->getByField(array(
                    'pid' => $this->data['pid']
                ), true);
                foreach ($vk_publics as $c_public) {
                    $res = $vk_session->api("market.delete", array(
                        'owner_id' => '-' . $this->data['gid'],
                        'item_id' => $c_public['vk_pid']
                    ), 1);
                    if (isset($res['error'])) {
                        unset($vkm);
                        unset($vk_session);
                        if ($prd->getId() != '' && isset($this->data['settings']['prd_set']) && $this->data['settings']['prd_set'] == 1 && isset($this->data['settings']['set_id']) && $this->data['settings']['set_id'] != '') {
                            $this->addToSet($prd, $this->data['settings']['set_id']);
                        }
                        return array(
                            'status' => 'error',
                            'code' => 'delete'
                        );
                    }
                    $vkm->deleteById($c_public['id']);
                }
            }
            unset($vkm);
            unset($vk_session);
            if ($prd->getId() != '' && isset($this->data['settings']['prd_set']) && $this->data['settings']['prd_set'] == 1 && isset($this->data['settings']['set_id']) && $this->data['settings']['set_id'] != '') {
                $this->addToSet($prd, $this->data['settings']['set_id']);
            }
            return array(
                'status' => 'error',
                'code' => 'hidden'
            );
        }

        // Если товара нет на складе - пропускаем при публикации
        if (!$this->data['settings']['upd_null'] && $prd['count'] == 0 && $prd['count'] != '') {
            if ($public_prod) {
                $vk_publics = $vkm->getByField(array(
                    'pid' => $this->data['pid']
                ), true);
                foreach ($vk_publics as $c_public) {
                    $res = $vk_session->api("market.delete", array(
                        'owner_id' => '-' . $this->data['gid'],
                        'item_id' => $c_public['vk_pid']
                    ), 1);
                    if (isset($res['error'])) {
                        unset($vkm);
                        unset($vk_session);
                        if ($prd->getId() != '' && isset($this->data['settings']['prd_set']) && $this->data['settings']['prd_set'] == 1 && isset($this->data['settings']['set_id']) && $this->data['settings']['set_id'] != '') {
                            $this->addToSet($prd, $this->data['settings']['set_id']);
                        }
                        return array(
                            'status' => 'error',
                            'code' => 'delete'
                        );
                    }
                    $vkm->deleteById($c_public['id']);
                }
            }
            unset($vkm);
            unset($vk_session);
            if ($prd->getId() != '' && isset($this->data['settings']['prd_set']) && $this->data['settings']['prd_set'] == 1 && isset($this->data['settings']['set_id']) && $this->data['settings']['set_id'] != '') {
                $this->addToSet($prd, $this->data['settings']['set_id']);
            }
            return array(
                'status' => 'error',
                'code' => 'null_count'
            );
        }

        if ($public_prod) {
            $old_publication = $vk_session->api('market.getById', array(
                'item_ids' => '-' . $this->data['gid'] . '_' . $public_prod['vk_pid']
            ), 1);
            if (isset($old_publication['error'])) {
                unset($vkm);
                unset($vk_session);
                if ($prd->getId() != '' && isset($this->data['settings']['prd_set']) && $this->data['settings']['prd_set'] == 1 && isset($this->data['settings']['set_id']) && $this->data['settings']['set_id'] != '') {
                    $this->addToSet($prd, $this->data['settings']['set_id']);
                }
                return array(
                    'status' => 'error',
                    'code' => 'check_vkpid'
                );
            }
            // Ответы ВК то в одном формате, то в другом... Поэтому небольшой комплект проверок
            if ($old_publication['response']['count'] > 0) {

                if ($old_publication['response']['items'][0]['id'] != '0' && isset($old_publication['response']['items'][0]['availability']) && $old_publication['response']['items'][0]['availability'] != 1) {
                    $this->data['vk_prod'] = $public_prod;
                } else {
                    $vkm->deleteById($public_prod['id']);
                    unset($public_prod);
                }
            } else {
                $vkm->deleteById($public_prod['id']);
                unset($public_prod);
            }
        }

        /*
         * Перебираем изображения товара и загружаем в ВК
         * Первое - основное. Дополнительных не более 4
         * Если у товара нет изображений - возвращаем сообщение об ошибке0
         */
        $images = $prd->getImages();
        if (count($images) == 0) {
            unset($vkm);
            unset($vk_session);
            if ($prd->getId() != '' && isset($this->data['settings']['prd_set']) && $this->data['settings']['prd_set'] == 1 && isset($this->data['settings']['set_id']) && $this->data['settings']['set_id'] != '') {
                $this->addToSet($prd, $this->data['settings']['set_id']);
            }
            return array(
                'status' => 'error',
                'code' => 'noimg'
            );
        }
        $ci = 5;
        $vkimages = array();

        foreach ($images as $image) {

            switch ($ci) {
                case '0':
                    break 2;
                case '5':
                    $main_photo = 1;
                    break;
                default:
                    $main_photo = 0;
                    break;
            }

            $img_path = shopImage::getThumbsPath($image, $this->data['settings']['sketch_size']);
            if (!file_exists($img_path)) {
                try {
                    shopImage::generateThumbs($image, array(
                        $this->data['settings']['sketch_size']
                    ));
                } catch (waException $e) {
                    waLog::log('Ошибка при обработке изображения товара с id ' . $prd->getId(), 'shop/plugins/vkgoods/vkgoods.error.log');
                    return array(
                        'status' => 'error',
                        'code' => 'img_upload'
                    );
                }
            }
            try {
                $tmp_img = new waImage($img_path);
            } catch (waException $e) {
                waLog::log('Ошибка при обработке изображения товара с id ' . $prd->getId(), 'shop/plugins/vkgoods/vkgoods.error.log');
                return array(
                    'status' => 'error',
                    'code' => 'img_upload'
                );
            }
            if ($tmp_img->width < 400 || $tmp_img->height < 400) {
                if ($tmp_img->width > $tmp_img->height) {
                    $tmp_img = waImage::factory(shopImage::getPath($image));
                    $tmp_img->resize(null, 400, 'HEIGHT', false);
                } else {
                    $tmp_img = waImage::factory(shopImage::getPath($image));
                    $tmp_img->resize(400, null, 'WIDTH', false);
                }
                $img_path = wa()->getTempPath() . '/vkgoods_tmp_img.png';
                $tmp_img->save($img_path);
            }

            $upload = $vk_session->uploadImg($this->data['gid'], $img_path, $main_photo);
            if (strpos($img_path, 'vkgoods_tmp_img')) {
                waFiles::delete($img_path);
            }
            if (isset($upload->error)) {
                if ($main_photo == 1) {
                    unset($vkm);
                    unset($vk_session);
                    if ($prd->getId() != '' && isset($this->data['settings']['prd_set']) && $this->data['settings']['prd_set'] == 1 && isset($this->data['settings']['set_id']) && $this->data['settings']['set_id'] != '') {
                        $this->addToSet($prd, $this->data['settings']['set_id']);
                    }
                    return array(
                        'status' => 'error',
                        'code' => 'img_upload'
                    );
                }
            } else {
                $vkimages[] = $upload;
            }

            if ($this->data['all_photo'] == 0) {
                break;
            }
            $ci--;
        }
        // Устанавливает основным фото первое изображение товара и исключаем его из массива доп.изображений
        $main_photo = $vkimages[0]->response[0]->id;
        array_shift($vkimages);

        $str_vk_images = "";
        foreach ($vkimages as $vkimage) {
            $str_vk_images .= $vkimage->response[0]->id . ",";
        }
        $item_data = $this->preparationData($prd, $main_photo, $str_vk_images, 1);

        /*
         * Проверяем наличие флага о публикации товара в этом сообществе.
         * Если флага нет - стандартная публикация.
         * Если флаг есть - проверяем актуальность и если неактуален - обновляем товар
         */

        if (!isset($this->data['vk_prod'])) {
            $vkitem = $vk_session->api("market.add", $item_data, 1);
        } else {
            if ($this->data['vk_prod']['date'] < $prd['edit_datetime']) {
                $item_data['item_id'] = $this->data['vk_prod']['vk_pid'];
                $item_data['main_photo_id'] = $this->data['vk_prod']['main_photo_id'];
                unset($item_data['photo_ids']);
                $vkitem = $vk_session->api("market.edit", $item_data, 1);
            } elseif (in_array($this->data['aid'], explode(',', $public_prod['aid']))) {
                unset($vkm);
                unset($vk_session);
                return array(
                    'status' => 'ok',
                    'code' => 'actual'
                );
            } elseif ($this->data['aid'] != 0) {
                // Смахивает на костыль, но ничего оптимальнее для ситуации когда продукт опубликован и актуален, но надо поместить в другую подборку, не придумал
                $ita = $vk_session->api("market.addToAlbum", "owner_id=-" . $this->data['gid'] . "&item_id=" . $this->data['vk_prod']['vk_pid'] . "&album_ids=" . $this->data['aid']);
            }
        }
        if (isset($vkitem['error'])) {
            unset($vkm);
            unset($vk_session);
            if ($prd->getId() != '' && isset($this->data['settings']['prd_set']) && $this->data['settings']['prd_set'] == 1 && isset($this->data['settings']['set_id']) && $this->data['settings']['set_id'] != '') {
                $this->addToSet($prd, $this->data['settings']['set_id']);
            }
            return array(
                'status' => 'error',
                'code' => 'public_prd'
            );
        }

        /*
         * Добавление товара в подборку. Если товар публиковался ранее - проверяем опубликован ли он в подборке $this->data['aid']. Если нет - публикуем
         */

        if ($this->data['aid'] != 0 && isset($vkitem['response'])) {
            if (!isset($this->data['vk_prod'])) {
                $ita = $vk_session->api("market.addToAlbum", "owner_id=-" . $this->data['gid'] . "&item_id=" . $vkitem['response']['market_item_id'] . "&album_ids=" . $this->data['aid']);
            } else {
                if (!in_array($this->data['aid'], explode(',', $public_prod['aid']))) {
                    $ita = $vk_session->api("market.addToAlbum", "owner_id=-" . $this->data['gid'] . "&item_id=" . $this->data['vk_prod']['vk_pid'] . "&album_ids=" . $this->data['aid']);
                }
            }
        }

        if (isset($ita->error)) {
            unset($vkm);
            unset($vk_session);
            if ($prd->getId() != '' && isset($this->data['settings']['prd_set']) && $this->data['settings']['prd_set'] == 1 && isset($this->data['settings']['set_id']) && $this->data['settings']['set_id'] != '') {
                $this->addToSet($prd, $this->data['settings']['set_id']);
            }
            return array(
                'status' => 'error',
                'code' => 'add_to_album'
            );
        }

        /*
         * Формирование данных для добавления информации о публикации в БД
         */

        $data_upd = array(
            'pid' => $this->data['pid'],
            'gid' => $this->data['gid'],
            'vkcat_id' => $this->data['category_id'],
            'date' => $prd['edit_datetime'],
            'storefront' => $this->data['storefront']
        );
        if (isset($vkitem)) {
            $data_upd['vk_pid'] = $vkitem['response']['market_item_id'];
        } else {
            $data_upd['vk_pid'] = $this->data['vk_prod']['vk_pid'];
        }

        if (isset($ita)) {
            if (isset($this->data['vk_prod'])) {
                $data_upd['aid'] = $this->data['vk_prod']['aid'] . ',' . $this->data['aid'];
            } else {
                $data_upd['aid'] = $this->data['aid'];
            }
        }

        if (isset($this->data['vk_prod'])) {
            $vkm->updateById($this->data['vk_prod']['id'], $data_upd);
            unset($vkm);
            unset($vk_session);
            return array(
                'status' => 'ok',
                'code' => 'aid_only'
            );
        } else {
            $data_upd['main_photo_id'] = $main_photo;
            $data_upd['photo_ids'] = $str_vk_images;
            $vkm->insert($data_upd);
            unset($vkm);
            unset($vk_session);
            return array(
                'status' => 'ok',
                'code' => 'full'
            );
        }
    }

    /**
     *
     * @var array данные о публикации в ВК
     * @var object shopVkgoodsPluginVkapi
     */
    public function goUpdate($vk_prod, $vk_session)
    {
        $this->data['desc'] = $this->data['settings']['desc_template'];
        // для обновления
        if ($vk_prod['storefront'] != '') {
            $this->data['storefront'] = $vk_prod['storefront'];
        } else {
            $this->data['storefront'] = $this->data['settings']['upd_storefront'];
        }
        $this->data['f_price'] = $this->data['settings']['format_price'];
        $model = new shopVkgoodsPluginProductModel();

        $prd = new shopProduct($vk_prod['pid']);

        if (!$prd['edit_datetime']) {
            $prd['edit_datetime'] = $prd->create_datetime;
        }


        // Проверяем существует ли еще такой товар и опубликован ли он на витрине. Если нет - удаляем из ВК
        if ($prd->getId() == '' || $prd['status'] == 0) {
            $del_data = array(
                'owner_id' => '-' . $vk_prod['gid'],
                'item_id' => $vk_prod['vk_pid']
            );
            $res = $vk_session->api('market.delete', $del_data);
            if (!isset($res->response)) {
                waLog::log('Ошибка удаления публикации http://vk.com/market-' . $vk_prod['gid'] . '?w=product-' . $vk_prod['gid'] . '_' . $vk_prod['vk_pid'] . '(id товара ' . $vk_prod['pid'] . ')', 'shop/plugins/vkgoods/vkgoods.update.log');
                return array(
                    'code' => 'del_vkitem'
                );
            } else {
                $model->deleteById($vk_prod['id']);
                return array(
                    'code' => 'del_db'
                );
            }
        }

        // Проверка в соотв.с настройками публиковать ли товар с остатками 0
        if (!$this->data['settings']['upd_null'] && $prd['count'] == 0 && $prd['count'] != '') {
            $del_data = array(
                'owner_id' => '-' . $vk_prod['gid'],
                'item_id' => $vk_prod['vk_pid']
            );
            $res = $vk_session->api('market.delete', $del_data);
            if (!isset($res->response)) {
                waLog::log('Ошибка удаления публикации http://vk.com/market-' . $vk_prod['gid'] . '?w=product-' . $vk_prod['gid'] . '_' . $vk_prod['vk_pid'] . '(id товара ' . $vk_prod['pid'] . ')', 'shop/plugins/vkgoods/vkgoods.update.log');
                return array(
                    'code' => 'del_vkitem'
                );
            } else {
                $model->deleteById($vk_prod['id']);
                return array(
                    'code' => 'null_count'
                );
            }
        }

        // Проверяем, обновлялась ли информация с момента публикации о товаре
        if (!$this->data['settings']['no_check_update'] && $vk_prod['date'] >= $prd['edit_datetime']) {
            return array(
                'code' => 'actual'
            );
        }

        // Проверяем существует ли еще такая публикация. Если нет - удаляем из базы публикаций
        $res = $vk_session->api('market.getById', array(
            'item_ids' => '-' . $vk_prod['gid'] . '_' . $vk_prod['vk_pid']
        ), 1);

        if (isset($res['error'])) {
            waLog::log('Ошибка получения информации о публикации http://vk.com/market-' . $vk_prod['gid'] . '?w=product-' . $vk_prod['gid'] . '_' . $vk_prod['vk_pid'] . '(id товара ' . $vk_prod['pid'] . ')', 'shop/plugins/vkgoods/vkgoods.update.log');
            return array(
                'code' => 'get_vkitem'
            );
        }

        // Обрабатываем варианты ответов API. Кривизна: если товар удален недавно, то API возвращает инфу о товаре с пометкой об удалении (availability)
        if ($res['response']['count'] == '0' || $res['response']['items'][0]['id'] == '0') {
            $model->deleteById($vk_prod['id']);
            return array(
                'code' => 'del_db'
            );
        }

        // Обновление товара
        $item_data = $this->preparationData($prd, $vk_prod['main_photo_id'], $vk_prod['photo_ids']);
        // unset($item_data['photo_ids']);
        $item_data['owner_id'] = '-' . $vk_prod['gid'];
        $item_data['category_id'] = $vk_prod['vkcat_id'];
        $item_data['item_id'] = $vk_prod['vk_pid'];

        $res = $vk_session->api('market.edit', $item_data);
        if (isset($res->error) || !isset($res->response)) {
            waLog::log('Ошибка редактирования публикации http://vk.com/market-' . $vk_prod['gid'] . '?w=product-' . $vk_prod['gid'] . '_' . $vk_prod['vk_pid'] . '(id товара ' . $vk_prod['pid'] . ')', 'shop/plugins/vkgoods/vkgoods.update.log');
            return array(
                'code' => 'edit'
            );
        }

        $new_data = array(
            'date' => $prd['edit_datetime'],
            'storefront' => $this->data['storefront']
        );
        $model->updateById($vk_prod['id'], $new_data);
        return array(
            'code' => 'done'
        );
    }

    /**
     *
     * @param object $prd
     *            объект класса shopProduct
     * @param string $main_photo
     *            id основного изображения при публикациив ВК
     * @param string $str_vk_images
     *            id дополнительных изображений для публикации через запятую
     * @param int $mode
     *            1 - новая публикация, 0 - обновление существующей
     * @return array массив с данными для публикации в ВК
     */
    public function preparationData($prd, $main_photo, $str_vk_images, $mode = 0)
    {
        // Подготовка описания для ВК в соответствии с шаблоном
        $vk_desc = $this->formDesc($prd);

        // Формирование цены
        $fp = $this->data['f_price'];
        if (!isset($prd['min_price']) || $prd['min_price'] == $prd['max_price']) {
            $fp = 0;
        }

        switch ($fp) {
            case '0':
                $price = sprintf(str_replace(',', '.', round($this->getStorefrontPrice($prd, $this->data['storefront'], 'price'), 2)));
                break;
            case '1':
                $price = sprintf(str_replace(',', '.', round($this->getStorefrontPrice($prd, $this->data['storefront'], 'min_price'), 2)));
                break;

            case '2':
                $price = sprintf(str_replace(',', '.', round($this->getStorefrontPrice($prd, $this->data['storefront'], 'max_price'), 2)));
                break;
        }

        if ($price == 0 && $this->data['settings']['price_null']) {
            $price += 1;
        }

        // Формируем массив с данными товара для публикации

        $item_data = array(
            'name' => $this->prepareStringField($prd['name'], 4, 100),
            'description' => $vk_desc,
            'price' => $price,
            'main_photo_id' => $main_photo
        );

        if ($mode == 1) {
            $item_data['owner_id'] = '-' . $this->data['gid'];
            $item_data['category_id'] = $this->data['category_id'];
        }

        if ($str_vk_images != "") {
            $item_data['photo_ids'] = $str_vk_images;
        }
        $item_data['url'] = $this->getProductUrl($prd, $this->data['storefront']);
        return $item_data;
    }

    /**
     * Полготавливает описание для экспорта в соответствии с настройками пользователя
     *
     * @param $product -
     *            array()
     * @param
     *            boolean
     * @return str
     */
    public function formDesc($product)
    {
        if (strpos(strtolower($this->data['desc']), '%url%') !== false && $this->data['storefront'] != 'no_storefront') {
            $frontend_url = $this->getProductUrl(new shopProduct($product['id']), $this->data['storefront']);
        } else {
            $frontend_url = '';
        }

        $product_sku = $this->model_product_skus->getById($product['sku_id']);
        $desc_template = array(
            "%debug%",
            "%name%",
            "%desc%",
            "%summary%",
            "%sku%",
            "%sku_code%",
            "%url%",
            "%id%",
            "&nbsp;",
            "%features%",
            "%tags%",
            "&quot;"
        );
        $rpl_value = array(
            "",
            $product['name'],
            strip_tags($this->brReplace($product['description'])),
            strip_tags($this->brReplace($product['summary'])),
            ifempty($product_sku['name'], ''),
            ifempty($product_sku['sku'], ''),
            $frontend_url,
            $product['id'],
            " "
        );

        $rpl_value[] = strip_tags($this->brReplace($this->getProductFeatures($product['id'])));

        if (count($product['tags']) > 0) {
            $str_tags = "";
            foreach ($product['tags'] as $tag) {
                if (strlen($tag) > 0) {
                    $str_tags .= '#' . str_replace(" ", "_", trim($tag)) . " ";
                }
            }
            if ($str_tags) {
                $rpl_value[] = trim($str_tags);
            }
        }

        $vk_desc = str_replace($desc_template, $rpl_value, $this->data['desc']);

        return $this->prepareStringField($vk_desc, 10);
    }

    /**
     * Формирует URL продукта для заданной в настройках витрины магазина
     *
     * @param
     *            array() - $product
     * @return str - URL продукта во фронтенде
     */
    private function getProductUrl($product, $storefront)
    {
        $category_model = new shopCategoryModel();
        $categories = $category_model->getFullTree('id, name, depth, url, full_url', true);
        $routing = wa()->getRouting();
        $domain_routes = $routing->getByApp('shop');
        foreach ($domain_routes as $domain => $routes) {
            foreach ($routes as $r) {
                if ($domain . '/' . $r['url'] == $storefront) {
                    if (empty($r['type_id']) || (in_array($product->type_id, (array)$r['type_id']))) {
                        $routing->setRoute($r, $domain);
                        $params = array(
                            'product_url' => $product->url
                        );
                        if ($product->category_id && isset($categories[$product->category_id])) {
                            if (!empty($r['url_type']) && $r['url_type'] == 1) {
                                $params['category_url'] = $categories[$product->category_id]['url'];
                            } else {
                                $params['category_url'] = $categories[$product->category_id]['full_url'];
                            }
                        }
                        $frontend_url = $routing->getUrl('/frontend/product', $params, true);
                    }
                }
            }
        }
        return $frontend_url;
    }

    /**
     * Формирует текстовое представление выбранных в настройках плагина характеристик
     *
     * @param int $pid
     *            ID товара
     * @return void|string
     */
    private function getProductFeatures($pid)
    {
        $prd = new shopProduct($pid);
        /*
         * Т.к. в 6 версии в shopProduct нет getFeatures(),
         * пришлось обойти напрямую через модель
         */
        $mpp = new shopProductFeaturesModel();
        $app_config = wa()->getConfig()->getAppConfig('shop');
        if (version_compare($app_config->getInfo('version'), '7.1.0') >= 0) {
            $prd_all_features = $mpp->getData($prd, true);
        } else {
            $prd_all_features = $mpp->getData($prd);
        }
        $model_features = new shopFeatureModel();
        if (!$this->data['settings']['features']) {
            return;
        }
        $features_by_code = $model_features->getAll('code');
        $mtf = new shopTypeFeaturesModel();
        $prd['type_id'] === null ? $all_type_feature = $features_by_code : $all_type_feature = $mtf->getByType($prd['type_id']);
        $features_txt = "";
        foreach ($all_type_feature as $data) {
            $code = $data['code'];
            if (!isset($prd_all_features[$code])) {
                continue;
            } else {
                $value = $prd_all_features[$code];
            }
            if ($features_by_code[$code]['type'] == shopFeatureModel::TYPE_DIVIDER || $features_by_code[$code]['parent_id'] != null) {
                continue;
            }
            if (isset($this->data['settings']['features'][$features_by_code[$code]['id']])) {
                $features_txt .= $features_by_code[$code]['name'] . ': ';
                if (is_object($value)) {
                    $features_txt .= $value->__toString() . ', ';
                } elseif (is_array($value)) {
                    foreach ($value as $cvalue) {
                        if (is_object($cvalue)) {
                            $features_txt .= $cvalue->__toString() . ', ';
                        } else {
                            $features_txt .= $cvalue . ', ';
                        }
                    }
                } else {
                    $features_txt .= $value . ', ';
                }
                $features_txt = mb_substr($features_txt, 0, mb_strlen($features_txt) - 2);
                $features_txt .= $this->data['settings']['fdelimeter'];
            }
        }
        $features_txt = mb_substr($features_txt, 0, mb_strlen($features_txt) - mb_strlen($this->data['settings']['fdelimeter']));
        return $features_txt;
    }

    private function getStorefrontPrice($product, $storefront, $type_price)
    {
        $routing = wa()->getRouting();
        $domain_routes = $routing->getByApp('shop');
        foreach ($domain_routes as $domain => $routes) {
            foreach ($routes as $r) {
                if ($domain . '/' . $r['url'] == $storefront) {
                    $event_params = array(
                        'products' => array(
                            $product->id => &$product,
                        ),
                    );
                    wa('shop')->event('frontend_products', $event_params);
                    $data = array(
                        'price' => $product[$type_price],
                        'currency' => wa('shop')->getConfig()->getCurrency(true)
                    );
                    // Если в настройках витрины не была нажата кнопка Сохранить, то $r['currency'] отсутствует
                    if (!isset($r['currency'])) {
                        $r['currency'] = wa('shop')->getConfig()->getCurrency(true);
                    }
                    // Второе условие в этом if необходимо для округления по правилам валюты витрины, т.к. в $product хранится хоть и в той же валюте, но без учета округления
                    if ($data['currency'] != $r['currency'] || $product['currency'] != wa('shop')->getConfig()->getCurrency(true)) {
                        $currency_price = shopRounding::roundCurrency(shop_currency($data['price'], $data['currency'], $r['currency'], false), $r['currency']);
                    } else {
                        $currency_price = (float)$data['price'];
                    }
                    break 2;
                }
            }
        }
        return $currency_price;
    }

    public function addToSet($prd, $set_id)
    {
        $model = new shopSetProductsModel();
        $model->add($prd['id'], $set_id);
    }

    private function brReplace($value)
    {
        if (isset($this->data['settings']['br_replace_new']) && trim($this->data['settings']['br_replace_new']) != '') {
            $tags_to_br = explode(',', $this->data['settings']['br_replace_new']);
            $value = str_replace($tags_to_br, "\r\n", $value);
        } else {
            $value = str_replace("<br>", "\r\n", $value);
        }
        return $value;
    }

    private function prepareStringField($string, $min_length = 0, $max_length = 0)
    {
        if ($min_length && mb_strlen($string) < $min_length) {
            $string .= str_repeat('_', mb_strlen($string) - $min_length);
        }
        if ($max_length && mb_strlen($string) > $max_length) {
            if ($max_length > 5) {
                $string = mb_substr($string, 0, $max_length - 1);
            } else {
                $string = mb_substr($string, 0, $max_length - 4) . '...';
            }
        }
        return $string;
    }
}

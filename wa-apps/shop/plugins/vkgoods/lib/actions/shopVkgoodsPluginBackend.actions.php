<?php

class shopVkgoodsPluginBackendActions extends waJsonActions
{
    /**
     *  Удаляет информацию о настройках публикации для категории (вызывается ид диалога настройки категории)
     */
    public function delWaitCategoryAction()
    {
        if (!$public_id = waRequest::post('public_id')) {
            $this->setError('Отсутствует необходимый параметр public_id');
            return;
        }

        $model = new shopVkgoodsPluginWaitcategoryModel();
        $model->deleteById($public_id);
    }

    /**
     *  Сохраняет информацию о настройках экспорта для категории
     */
    public function saveCategoryExportSettingsAction()
    {
        $post = waRequest::post();
        $old_public_id = ifset($post['public_id']);
        $data = array(
            'cid' => $post['pid'],
            'gid' => $post['group'],
            'storefront' => $post['storefront'],
            'aid' => $post['vkalbums'],
            'category_id' => $post['vkcat'],
            'desc' => $post['desc_prod'],
            'all_photo' => $post['all_photo'],
            'f_price' => $post['f_price']
        );
        $model = new shopVkgoodsPluginWaitcategoryModel();

        $model->deleteByField(array('cid' => $data['cid'], 'gid' => $data['gid']));
        if ($old_public_id) {
            $model->deleteById($old_public_id);
        }

        $this->response = $model->insert($data, 1);
    }

    /**
     *  Формирует html для замены в диалоге настройки категории посел выполнения настроек экспорта
     */
    public function getCategoryFieldAction()
    {
        if (!$category_id = waRequest::post('category_id')) {
            $this->setError('Отсутствует обязательный параметр category_id');
            return;
        }
        $model = new shopVkgoodsPluginWaitcategoryModel();
        $params = array(
            'publics' => $model->getByCategoryID($category_id),
            'need_script' => false,
            'category' => array(
                'id' => $category_id
            )
        );
        $plugin = wa()->getPlugin('vkgoods');

        $view = wa()->getView();
        $view->assign('params', $params);
        $view->assign('name', 'vkgoods_subcategories');
        $this->response['html'] = $view->fetch(wa()->getAppPath('plugins/vkgoods/templates/actions/backend/vkgoods_category_dialog.html'));
    }

    /**
     *  Сохраняет списки товаров для отложенной публикации
     */
    public function waitproductAction()
    {
        $model = new shopVkgoodsPluginWaitproductModel();

        switch (waRequest::post('pid_type')) {
            case 'hash':
                $collection = new shopProductsCollection(waRequest::post('pid'));
                break;

            case 'list':
                $collection = new shopProductsCollection('id/' . waRequest::post('pid'));
                break;
        }

        $prds = $collection->getProducts('id', 0, $collection->count());
        foreach ($prds as $prd) {
            $data = array(
                'pid' => $prd['id'],
                'gid' => waRequest::post('group'),
                'storefront' => waRequest::post('storefront'),
                'aid' => waRequest::post('vkalbums'),
                'category_id' => waRequest::post('vkcat'),
                'desc' => waRequest::post('desc_prod'),
                'all_photo' => waRequest::post('all_photo'),
                'f_price' => waRequest::post('f_price')
            );

            $model->insert($data, 1);
        }

        $this->response = array('done' => 'ok', 'count' => $model->countAll());
    }

    /**
     *  Мелкие запросы к API VK
     */
    public function getvkdataAction()
    {
        $plugin = wa()->getPlugin('vkgoods');
        $vkg = $plugin->getSettings();
        $vk_session = new shopVkgoodsPluginVkapi($vkg['vk_user_id'], $vkg['token']);
        $response = false;

        switch (waRequest::get('event')) {
            case 'sg' :
                //Получает перечень подборок в сообществе ВК
                $response = $vk_session->api('market.getAlbums', array('owner_id' => '-' . waRequest::get('gid')));

                break;
            case 'noauth' :
                $response = $vk_session->api('users.get', "");
                break;

            default :
                break;
        }

        $this->response = $response;
    }

    /*
     *  Получение настроек экспорта новых товаров в категории (html)
     */
    public function getCategoryExportTemplateAction()
    {
        if (!$public_id = waRequest::post('public_id')) {
            $this->setError('Отсутствует параметр public_id');
            return;
        }
        $model = new shopVkgoodsPluginWaitcategoryModel();
        if (!$wait_cat = $model->getById($public_id)) {
            $this->setError('Ошибка получения информации о настройках публикации');
            return;
        }

        $plugin = wa()->getPlugin('vkgoods');
        $settings = $plugin->getSettings();
        $vk_session = new shopVkgoodsPluginVkapi($settings['vk_user_id'], $settings['token']);
        $vk_group = $vk_session->api('groups.getById', array('group_id' => $wait_cat['gid']), true);

        $data['group_name'] = ifset($vk_group['response'][0]['name'], 'Не удалось получить название группы');
        $data['group_id'] = $wait_cat['gid'];

        if ($wait_cat['aid']) {
            $vk_album = $vk_session->api('market.getAlbumById', array('owner_id' => '-' . $wait_cat['gid'], 'album_ids' => $wait_cat['aid']), true);
            $data['album_name'] = ifset($vk_album['response'][1]['title'], 'Не удалось получить название подбрки');
        } else {
            $data['album_name'] = 'Не помещать в подборку';
        }
        $wait_cat['all_photo'] == 1 ? $data['all_photo'] = 'Да' : $data['all_photo'] = 'Нет';
        $type_prices = array('Базовая цена', 'Минимальная цена', 'Максимальная цена');
        $data['f_price'] = $type_prices[$wait_cat['f_price']];
        $data['storefront'] = $wait_cat['storefront'];
        $wait_cat['subcategories'] == 1 ? $data['subcategories'] = 'Да' : $data['subcategories'] = 'Нет';
        $data['desc'] = $wait_cat['desc'];


        $view = wa()->getView();
        $view->assign('data', $data);
        $template = $plugin->getPath() . '/templates/actions/backend/vkgoods_category_dialog_info.html';
        $this->response = $view->fetch($template);
    }

    protected function setError($message, $data = array())
    {
        $this->errors[] = array($message, $data);
    }
}
<?php

class shopVkgoodsPlugin extends shopPlugin
{

    public function handlerBackendProduct($product)
    {
        if (!$product['edit_datetime']) {
            $product['edit_datetime'] = $product['create_datetime'];
        }

        if (!function_exists('curl_init')) {
            return;
        }

        if (!$this->checkSettings()) {
            $content = ' <hr style="border-style: groove;border-width: 5px 0 0;"/>    <h4>ВК: Товары</h4> 
           <p class="hint">Укажите <a href="?action=plugins#/vkgoods/">необходимые данные</a> для работы плагина</p>
           <hr style="border-style: groove;border-width: 5px 0 0;"/>';
            return array(
                'toolbar_section' => $content
            );
        }

        if (!$this->checkVkToken()) {
            $content = ' <hr style="border-style: groove;border-width: 5px 0 0;"/>    <h4>ВК: Товары</h4> 
           <p class="hint">Не пройдена <a href="?action=importexport#/vkgoods/">авторизация</a> для работы плагина</p>
           <hr style="border-style: groove;border-width: 5px 0 0;"/>';
            return array(
                'toolbar_section' => $content
            );
        }

        $model = new shopVkgoodsPluginProductModel();
        $cps = $model->getByField('pid', $product['id'], true);
        $vkprod['count'] = count($cps);
        $vkprod['actual'] = true;
        $content_info_section = false;
        $view = wa()->getView();
        if (count($cps) > 0) {
            foreach ($cps as $cp) {
                if ($cp['date'] < $product['edit_datetime']) {
                    $vkprod['actual'] = false;
                }
            }
            $view->assign('items', $cps);
            $content_info_section = $view->fetch($this->path . '/templates/actions/backend/vkgoods_back_product_infosection.html');
        }
        $view->assign('pid', $product['id']);
        $view->assign('vkprod', $vkprod);
        $content = $view->fetch($this->path . '/templates/actions/backend/vkgoods_product.html');

        return array(
            'toolbar_section' => $content,
            'info_section' => $content_info_section
        );
    }

    public function handlerBackendProducts()
    {
        if (!function_exists('curl_init')) {
            return;
        }
        return array(
            'toolbar_export_li' => '<li><a id=vkgoods_prds href="#"><i class="icon16 vkontakte"></i>Товары</a></li>
    			<script src="' . wa()->getAppStaticUrl('shop', true) . 'plugins/vkgoods/js/vkgoods_prds.js" type="text/javascript"></script>',
            'viewmode_li' => '<li class="list-view-mode list-view-mode-skus"><a id=vkgoods-unpublished href="#" title="Неопубликованные в ВК товары"><i class="icon16 vk-unpublished"></i></a></li>',
            'sidebar_top_li' => '<style>i.vk-unpublished { background-repeat:no-repeat; background-image: url(' . wa()->getAppStaticUrl('shop') . 'plugins/vkgoods/img/vk-unpublished.png); height:16px; width:19px; display:inline-block; text-indent:-9999px; text-decoration:none!important; }</style>' . '<li id="vkgoods-" class="vkgoods-li" style="display:none;"><span class="count"></span><a id="s-vkgoods-unpublished-a" href="#/products/hash=vkgoods-"><i class="icon16 vk-unpublished"></i>Неопубликованные</a></li>'
        );
    }

    public function handlerBackendCategoryDialog($category)
    {
        $model = new shopVkgoodsPluginWaitcategoryModel();

        waHtmlControl::registerControl('VkgoodsText', array(
            'shopVkgoodsPlugin',
            'getTextHtmlControl'
        ));
        $params = array(
            'title' => 'ВКонтакте: Товары',
            'publics' => $model->getByCategoryID($category['id']),
            'category' => $category,
            'need_script' => true,
            'title_wrapper' => '%s&nbsp;',
            'control_wrapper' => '<div class="field"><div class="name">%s</div><div class="value no-shift">%s%s</div></div>'
        );
        $html = waHtmlControl::getControl('VkgoodsText', 'vkgoods_subcategories', $params);
        return $html;
    }

    public function handlerCategorySave($category)
    {
        $subcategories = waRequest::post('vkgoods_subcategories');
        $model = new shopVkgoodsPluginWaitcategoryModel();
        if (!$subcategories || !$wait_cats = $model->getById(array_keys($subcategories))) {
            return;
        }
        foreach ($subcategories as $wait_id => $value) {
            if ($wait_cats[$wait_id]['subcategories'] != $value) {
                $model->updateById($wait_id, array('subcategories' => $value));
            }
        }
    }

    public function handlerCategoryDelete($category)
    {
        $model = new shopVkgoodsPluginWaitcategoryModel();
        $model->deleteByField('cid', $category['id']);
    }

    public static function getSettingsTextSystem()
    {
        return "<h2>Системные настройки</h2>";
    }

    public static function getSettingsTextUpdate()
    {
        return '<br><br><h2>Настройки публикаций и автоматического обновления</h2>
        		<script type="text/javascript" src="' . wa()->getRootUrl() . 'wa-apps/shop/plugins/vkgoods/js/vkgoods_settings.js"></script>';
    }

    public static function settingsSetList()
    {
        $model = new shopSetModel();
        $sets = $model->getAll();
        $settings_sets = array();
        foreach ($sets as $set) {
            $settings_sets[] = array(
                'title' => $set['name'],
                'value' => $set['id']
            );
        }
        return $settings_sets;
    }

    protected function checkSettings()
    {
        $settings = $this->getSettings();

        if ($settings['app_site'] == '' || $settings['app_site_secret'] == '') {
            return false;
        } else {
            return true;
        }
    }

    protected function checkVkToken()
    {
        $settings = $this->getSettings();
        if ($settings['token'] == '') {
            return false;
        } else {
            return true;
        }
    }

    public function handlerProductsCollection($params)
    {
        $this->product_collection = $params['collection'];
        $hash = $this->product_collection->getHash();
        if (strpos($hash[0], 'vkgoods-') !== 0) {
            return null;
        }
        $this->product_collection->addTitle('Неопубликованные в ВК товары');
        if (!$data = wa()->getStorage()->get('shop/vkgoods/' . $hash[0])) {
            $this->product_collection->addWhere("p.id = 0");
            return true;
        }

        $this->product_collection->addWhere("p.id IN (" . implode(',', $data) . ")");
        return true;
    }

    public static function getTextHtmlControl($name, $params = array())
    {
        $plugin = wa()->getPlugin('vkgoods');
        $view = wa()->getView();
        $view->assign('params', $params);
        $view->assign('name', $name);
        $html = $view->fetch($plugin->path . '/templates/actions/backend/vkgoods_category_dialog.html');
        return $html;
    }

    public static function settingsFeatures()
    {
        $model = new shopFeatureModel();
        $features = $model->where('type NOT IN ("divider") AND parent_id IS NULL')->fetchAll();
        $flist = array();
        foreach ($features as $feature) {
            $flist[] = array(
                'title' => $feature['name'],
                'value' => $feature['id']
            );
        }
        return $flist;
    }

    public function getPath()
    {
        return $this->path;
    }
}

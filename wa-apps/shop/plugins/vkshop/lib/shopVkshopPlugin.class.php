<?php

/**
 * Class shopVkshopPlugin
 */
class shopVkshopPlugin extends shopPlugin
{
    /**
     * @var waView $view
     */
    private static $view;

    /**
     * @return waSmarty3View|waView
     * @throws waException
     */
    private static function getView()
    {
        if (!isset(self::$view)) {
            self::$view = waSystem::getInstance()->getView();
        }
        return self::$view;
    }

    /**
     * @var shopVkshopPlugin $plugin
     */
    private static $plugin;

    /**
     * @return shopVkshopPlugin|waPlugin
     * @throws waException
     */
    private static function getPlugin()
    {
        if (!isset(self::$plugin)) {
            self::$plugin = wa()->getPlugin('vkshop');
        }
        return self::$plugin;
    }

    /**
     * @return array
     * @throws waException
     */
    public function backendProducts()
    {
        $view = self::getView();
        $pdm = new shopVkshopPluginProductsDisabledModel();
        $pqm = new shopVkshopPluginProductsQueueModel();
        $view->assign('disabled', $pdm->countAll());
        $view->assign('queued', $pqm->countAll());
        $view->assign('groups', $this->getSettings('groups'));

        return array(
            'toolbar_export_li' => $view->fetch($this->path . '/templates/hooks/queue.html'),
        );
    }

    /**
     * @param shopProduct $product
     * @return array|bool
     * @throws waException
     */
    public function backendProduct($product)
    {
        if ($product['status'] == 0) {
            return false;
        }

        $settings = $this->getSettings();
        $view = self::getView();
        $pdm = new shopVkshopPluginProductsDisabledModel();
        $disabled = $pdm->isProductDisabled($product['id']);
        $view->assign('product_id', $product->getId());
        $view->assign('disabled', $disabled);
        $view->assign('vkshop_product', json_encode($product->getData()));

        $images = $product->getImages();

        $view->assign('settings', $settings);
        $view->assign('product', $product);
        $view->assign('images', $images);

        return array(
            'action_button' => $view->fetch($this->path . '/templates/hooks/disable_button.html'),
            'info_section' => '<div id="s-plugin-vkshop-content" style="display: none"></div>',
            'toolbar_section' => $view->fetch($this->path . '/templates/hooks/backend_product.toolbar_section.html'),
        );
    }


    /**
     * @return array
     * @throws waException
     */
    public static function getSettlements()
    {
        $plugin = self::getPlugin();
        $settings = $plugin->getSettings();
        $settlements = array();
        $current_domain = $settings['domain'];
        $routing = wa()->getRouting();
        $domain_routes = $routing->getByApp('shop');
        foreach ($domain_routes as $domain => $routes) {
            foreach ($routes as $route) {
                $routing->setRoute($route, $domain);
                $settlement = wa()->getRouteUrl('/frontend/product', array('product_url' => false), true);
                if (($settlement == $current_domain) || ($current_domain === '')) {
                    $current_domain = $settlement;
                    $routing->setRoute($route, $domain);
                    waRequest::setParam($route);
                }
                $settlement = rtrim($settlement, '/') . '/';
                $settlements[] = $settlement;
            }
        }
        return $settlements;
    }

    /**
     * @return string
     * @throws waException
     */
    public static function settingCustomControlSettlements()
    {
        $view = self::getView();
        $plugin = self::getPlugin();
        $settlements = self::getSettlements();
        $settings = $plugin->getSettings();
        $current_domain = $settings['domain'];

        $view->assign('current_domain', $current_domain);

        $view->assign('settlements', $settlements);
        return $view->fetch($plugin->path . '/templates/controls/settlements.html');
    }

    /**
     * @return string
     * @throws waException
     */
    public static function settingCustomControlHint()
    {
        $view = self::getView();
        $plugin = self::getPlugin();
        return $view->fetch($plugin->getPluginPath() . '/templates/controls/hint.html');
    }

    /**
     * @return string
     * @throws waException
     */
    public static function settingCustomControlVkCats()
    {
        $view = self::getView();
        $plugin = self::getPlugin();
        $settings = $plugin->getSettings();
        $view->assign('settings', $settings);
        return $view->fetch($plugin->getPluginPath() . '/templates/controls/vk_cats.html');
    }

    /**
     * @return string
     */
    public function getPluginPath()
    {
        return $this->path;
    }

    /**
     * @return string
     * @throws waException
     */
    public static function getFeedbackControl()
    {
        $view = self::getView();
        $plugin = self::getPlugin();
        return $view->fetch($plugin->getPluginPath() . '/templates/controls/feedbackControl.html');
    }


    /**
     * @return array
     */
    public static function getImageSizes() {
        $shop_settings = wa()->getConfig()->getOption(null);
        $sizes = array(
            'system' => wa()->getConfig()->getImageSizes('system'),
            'custom' => (array)$shop_settings['image_sizes'],
        );
        $image_sizes = array_values($sizes['system']);
        $image_sizes = array_merge($image_sizes, array_values($sizes['custom']));

        $options = array();
        foreach ($image_sizes as $size) {
            $option = array(
                array(
                    'title' => $size,
                    'value' => $size,
                )
            );
            $options = array_merge($options, $option);
        }
        return $options;
    }

    /**
     * @param $sizes
     * @return array
     */
    protected static function formatSizes($sizes)
    {
        $result = array();
        foreach ($sizes as $size) {
            $size_info = shopImage::parseSize((string)$size);
            $type   = $size_info['type'];
            $width  = $size_info['width'];
            $height = $size_info['height'];
            if ($type == 'max' || $type == 'crop' || $type == 'width') {
                $result[] = array($type => $width);
            } else if ($type == 'height') {
                $result[] = array($type => $height);
            } elseif ($type == 'rectangle') {
                $result[] = array('rectangle' => array($width, $height));
            }
        }
        return $result;
    }

    /**
     * @return array
     */
    public static function getTabs()
    {
        $tabs = array(
            'groups' => array(
                'name' => _wp('Groups'),
                'template' => 'Groups.html',
            ),
            'templates' => array(
                'name' => _wp('Templates'),
                'template' => 'Templates.html',
            ),
            'cron' => array(
                'name' => _wp('Cron'),
                'template' => 'Cron.html',
            ),
            'info' => array(
                'name' => _wp('Information'),
                'template' => 'Info.html',
            ),
        );
        return $tabs;
    }


    /**
     * @return string
     * @throws waException
     */
    public static function getCaptionhintControl()
    {
        $plugin = self::getPlugin();
        $view = self::getView();
        return $view->fetch($plugin->getPluginPath() . '/templates/controls/captionhintControl.html');
    }

    /**
     * @return string
     * @throws waException
     */
    public static function getCronHintControl()
    {
        $plugin = self::getPlugin();
        $view = self::getView();
        $view->assign('root_path', wa()->getConfig()->getPath('root'));
        return $view->fetch($plugin->getPluginPath() . '/templates/controls/cronHint.html');
    }

    /**
     * @return string
     * @throws waException
     */
    public static function getGroupsControl() {
        $view = self::getView();
        $plugin = self::getPlugin();
        $settings = $plugin->getSettings();
        $view->assign('settings', $settings);

        $settlements = self::getSettlements();
        $view->assign('settlements', $settlements);
        return $view->fetch($plugin->getPluginPath() . '/templates/controls/groupsControl.html');
    }

    /**
     * @return array
     */
    public static function settingsFeatures(){
        $model = new shopFeatureModel();
        $features = $model->where('type NOT IN ("divider") AND parent_id IS NULL')->fetchAll();
        $flist = array();
        foreach($features as $feature){
            $flist[] = array('title' => $feature['name'], 'value' => $feature['id']);
        }
        return $flist;
    }


    /**
     * @param $params
     * @throws waException
     */
    public function productDelete($params)
    {
        $pcm = new shopVkshopPluginProductsCronModel();
        $pcm->deleteByField('product_id', $params['ids']);

        $pdm = new shopVkshopPluginProductsDisabledModel();
        $pdm->deleteByField('product_id', $params['ids']);

        $pqm = new shopVkshopPluginProductsQueueModel();
        $pqm->deleteByField('product_id', $params['ids']);

        $settings = $this->getSettings();
        if ($settings['cron_autodelete']) {

            $vk_product_model = new shopVkshopPluginProductsModel();
            $cron_model = new shopVkshopPluginProductsCronModel();
            $data = array();
            foreach ($params['ids'] as $id) {
                $vk_products = $vk_product_model->getByField('product_id', $id, true);

                if (!empty($vk_products)) {
                    foreach ($vk_products as $vk_product) {
                        $data[] = array(
                            'product_id'    => $id,
                            'group_id'      => intval($vk_product['group_id']),
                            'action'        => 'delete',
                        );
                    }
                }

            }
            $cron_model->multipleInsert($data);
        }
        else {
            $pm = new shopVkshopPluginProductsModel();
            $pm->deleteByField('product_id', $params['ids']);

            $products_albums_model = new shopVkshopPluginProductsAlbumsModel();
            $products_albums_model->deleteByField('product_id', $params['ids']);
        }
    }

    /**
     * @param $params
     * @throws waException
     */
    public function productSave($params)
    {
        if (isset($params['data']) && !empty($params['data'])) {
            $product_array = $params['data'];
            $vk_product_model = new shopVkshopPluginProductsModel();
            $cron_model = new shopVkshopPluginProductsCronModel();

            $group_model = new shopVkshopPluginGroupModel();
            $groups = $group_model->getAll();

            $settings = $this->getSettings();

            if ($settings['cron_delete_hidden'] && $product_array['status'] == 0) {
                $vk_products = $vk_product_model->getByField('product_id', $product_array['id'], true);

                if (!empty($vk_products)) {
                    foreach ($vk_products as $vk_product) {
                        $data = array(
                            'product_id'    => $product_array['id'],
                            'group_id'      => intval($vk_product['group_id']),
                            'action'        => 'delete',
                        );

                        $row = $cron_model->getByField($data);
                        if (empty($row)) {
                            $cron_model->insert($data);
                        }
                    }
                }
            }

            if ($settings['cron_autoupdate']) {
                $vk_products = $vk_product_model->getByField('product_id', $product_array['id'], true);

                if (!empty($vk_products)) {
                    foreach ($vk_products as $vk_product) {
                        $data = array(
                            'product_id'    => $product_array['id'],
                            'group_id'      => intval($vk_product['group_id']),
                            'action'        => 'update',
                        );

                        $row = $cron_model->getByField($data);
                        if (empty($row)) {
                            $cron_model->insert($data);
                        }
                    }
                }
                else {
                    if ($settings['cron_autoadd']) {
                        $category_products_model = new shopCategoryProductsModel();
                        $vkshop_category_model = new shopVkshopPluginCategoryModel();
                        $categories = $category_products_model->getByField('product_id', $product_array['id'], true);

                        if (!empty($groups)) {
                            foreach ($categories as $category) {
                                foreach ($groups as $group) {
                                    $album_link = $vkshop_category_model->getByField(
                                        array(
                                            'category_id'   => $category['category_id'],
                                            'group_id'      => $group['id'],
                                        )
                                    );

                                    if (!empty($album_link)) {
                                        $data = array(
                                            'product_id'    => $product_array['id'],
                                            'group_id'      => intval($album_link['group_id']),
                                            'action'        => 'add',
                                        );

                                        $row = $cron_model->getByField($data);
                                        if (empty($row)) {
                                            $cron_model->insert($data);
                                        }
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if ($settings['cron_autodelete']) {
                $data = array();
                $vk_products = $vk_product_model->getByField('product_id', $product_array['id'], true);
                if (!empty($vk_products) && $product_array['count'] === '0') {
                    foreach ($vk_products as $vk_product) {
                        $cron_model->deleteByField(
                            array(
                                'product_id'    => $product_array['id'],
                                'group_id'      => intval($vk_product['group_id']),
                            )
                        );

                        $data[] = array(
                            'product_id'    => $product_array['id'],
                            'group_id'      => intval($vk_product['group_id']),
                            'action'        => 'delete',
                        );
                    }
                }
                $cron_model->multipleInsert($data);
            }
        }
    }

    /**
     * @param array $settings
     * @return array|void
     * @throws waException
     */
    public function saveSettings($settings = array())
    {
        $group_model = new shopVkshopPluginGroupModel();
        if (!empty($settings['groups'])) {
            $ids = array();
            foreach ($settings['groups'] as $key => $group) {
                if (!empty($group['id']) && is_numeric($group['id'])) {
                    $ids[] = $group['id'];
                    $group_row = $group_model->getById($group['id']);
                    if (empty($group_row)) {

                        $group_row = array(
                            'id'            => $group['id'],
                            'group_name'    => $group['name'],
                            'app_id'        => $group['app_id'],
                            'app_secret'    => $group['app_secret'],
                            'settlement'    => $group['settlement'],
                        );

                        $group_model->insert($group_row);
                    }

                    else {
                        $group_row['group_name'] = $group['name'];
                        $group_row['app_id']     = $group['app_id'];
                        $group_row['app_secret'] = $group['app_secret'];
                        $group_row['settlement'] = $group['settlement'];
                        $group_model->updateById($group_row['id'], $group_row);
                    }
                }
                else {
                    unset($settings['groups'][$key]);
                }
            }

            if (!empty($ids)) {
                $group_model->exec('DELETE FROM shop_vkshop_group WHERE id NOT IN ('.$group_model->escape(implode(',', $ids)).')');
            }
        }

        parent::saveSettings($settings);
    }

    /**
     * @param $category
     * @return string
     * @throws waException
     */
    public function backendCategoryDialog($category)
    {
        $view = self::getView();
        $plugin = self::getPlugin();

        $errors = array();
        $group_model = new shopVkshopPluginGroupModel();
        $groups = $group_model->getLoginedGroups();

        if (empty($groups)) {
            $errors[] = _wp('Undefined groups in plugin settings.');
        }

        if (empty($errors)) {
            foreach ($groups as $key => $group) {
                if ($group['auth']) {
                    $tools = new shopVkshopPluginTools($group['vk']);
                    $r = $tools->compareAlbums();

                    if ($r === true) {
                        $albums_model = new shopVkshopPluginAlbumsModel();
                        $groups[$key]['albums'] = $albums_model->query(
                            'SELECT a.*, c.category_id FROM shop_vkshop_albums a '
                            .'LEFT JOIN shop_vkshop_category c ON (c.album_id = a.album_id AND c.group_id = a.group_id AND category_id = i:category_id) '
                            .'WHERE a.group_id = i:group_id ',
                            array(
                                'category_id'   => $category['id'],
                                'group_id'      => $group['id'],
                            )
                        )->fetchAll();
                    }
                    else $errors[] = $r;
                }
            }

        }
        else {
            $errors[] = _wp('VK is not authentificated.');
        }

        $view->assign('errors', $errors);
        $view->assign('groups', $groups);

        return $view->fetch($plugin->getPluginPath() . '/templates/actions/backend/category.html');
    }

    /**
     * @param $params
     * @throws waException
     */
    public function categorySave($params) {
        $albums = waRequest::post('vkshop');

        $group_model = new shopVkshopPluginGroupModel();
        $groups = $group_model->getAll();

        if (!empty($groups)) {
            $vcategory_model = new shopVkshopPluginCategoryModel();

            foreach ($groups as $group) {
                $data = array(
                    'category_id'       => $params['id'],
                    'group_id'          => $group['id'],
                );

                if (isset($albums[$group['id']]) && !empty($albums[$group['id']])) {
                    $data['album_id'] = $albums[$group['id']];
                    $vcategory_model->insert($data, 1);
                }
                else {
                    $vcategory_model->deleteByField($data);
                }
            }
        }
    }

    /**
     * @param $category
     */
    public function categoryDelete($category)
    {
        $vcategory_model = new shopVkshopPluginCategoryModel();
        $vcategory_model->deleteByField('category_id', $category['id']);
    }


    /**
     * @return string
     * @throws waException
     */
    public static function cronLampControl()
    {
        $plugin = self::getPlugin();
        $settings = $plugin->getSettings();
        $view = self::getView();
        $lamp = false;

        if (($settings['cron_timestamp'] + 7200) > time()) {
            $lamp = true;
            $message = _wp('Cron script is started');
        }
        else {
            $message = _wp('Cron script not started');
        }
        $view->assign('lamp', $lamp);
        $view->assign('message', $message);
        return $view->fetch($plugin->getPluginPath() . '/templates/controls/lamp_control.html');
    }
}

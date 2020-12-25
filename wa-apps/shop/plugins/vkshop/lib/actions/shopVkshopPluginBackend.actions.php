<?php


class shopVkshopPluginBackendActions extends waViewActions
{
    /**
 * @var string 
*/
    protected $template_folder = 'templates/actions/backend/';

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
     * Страница настроек
     * @var shopVkshopPluginApi $vk
     * @throws waException
     */
    public function setupAction()
    {
        $plugin = self::getPlugin();
        $settings = $plugin->getSettings();
        $group_model = new shopVkshopPluginGroupModel();
        $groups = $group_model->getAll();

        foreach ($groups as $key => $group) {
            $errors = array();
            $errors_template = '';
            if (strlen($group['app_id']) < 5 or strlen($group['app_secret']) < 5) {
                $url = wa()->getUrl(false) . '?action=plugins#/vkshop/';
                $errors = array(
                    array(
                        'error' => _wp('Settings error'),
                        'error_description' => sprintf(
                            _wp('Do not put App id or App secret in the <a href="%s">plugin settings</a>.'),
                            $url
                        ),
                    ),
                );
                $this->view->assign('errors', $errors);
                $errors_template = $this->view->fetch($plugin->getPluginPath() . '/templates/actions/backend/errors.html');
            }

            $groups[$key]['errors'] = $errors;
            $groups[$key]['errors_template'] = $errors_template;


            //здесь $vk создается правильно. переписывать не нужно т.к. нужен массив со всеми группами, а не только с зарегаными.
            $vk = new shopVkshopPluginApi($group['app_id'], $group['app_secret']);
            $vk->setApiVersion('5.87');
            $callback_url = wa()->getUrl(true) . '?action=importexport#/vkshop/getcode/';
            $login_url = $vk->getAuthorizeURL('photos,groups,market,offline,nohttps', $callback_url);
            $groups[$key]['login_url'] = $login_url . '?group=' . $group['id'];
        }

        $this->view->assign('settings', $settings);
        $this->view->assign('vk_cats', $plugin->getPluginPath() . '/templates/controls/vk_cats.html');

        $type_model = new shopTypeModel();
        $product_types = $type_model->getTypes(true);
        $this->view->assign('types', $product_types);

        $set_model = new shopSetModel();
        $this->view->assign('sets', $set_model->getAll());

        $this->view->assign('categories', new shopCategories());

        $qm = new shopVkshopPluginProductsQueueModel();
        $this->view->assign('queued', $qm->countAll());
        $this->view->assign('locale', $locale = wa()->getLocale());

        $this->view->assign('groups', $groups);

        $select_groups = array();
        foreach ($groups as $i => $group) {
            if (!empty($group['user_id'])) {
               $select_groups[] = $group;
            }
        }

        if (!empty($select_groups)) {
            $this->view->assign('select_groups', $select_groups);

            $group = reset($select_groups);
            $album_model = new shopVkshopPluginAlbumsModel();
            $this->view->assign('albums', $album_model->getAlbumsByGroupId($group['id']));
        }
        else {
            $this->view->assign('select_groups', array());
            $this->view->assign('albums', array());
        }
    }

    /**
     * @param string $type
     * @return string
     */
    protected function respondAs($type = null)
    {
        if ($type !== null) {
            if ($type == 'json') {
                $type = 'application/json';
            }
            $this->getResponse()->addHeader('Content-type', $type);
        }

        return $this->getResponse()->getHeader('Content-type');
    }

    /**
     * @return string
     */
    protected function getTemplate()
    {
        $plugin_root = $this->getPluginRoot();

        if ($this->template === null) {
            if ($this->respondAs() === 'application/json') {
                return $plugin_root . 'templates/json.tpl';
            }
            $template = ucfirst($this->action);
        } else {
            if (strpbrk($this->template, '/:') !== false) {
                return $this->template;
            }
            $template = $this->template;
        }

        return $plugin_root . $this->template_folder . $template . $this->view->getPostfix();
    }
}

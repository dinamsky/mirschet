<?php

/**
 * Created by PhpStorm.
 * User: snark | itfrogs.ru
 * Date: 6/18/15
 * Time: 6:29 PM
 */
class shopVkshopPluginBackendDialogAction extends waViewAction
{
    /**
     * @var shopVkshopPlugin $plugin
     */
    private $plugin;

    /**
     * shopVkshopPluginBackendDialogAction constructor.
     * @param null $params
     * @throws waException
     */
    public function __construct($params = null)
    {
        $this->plugin = wa()->getPlugin('vkshop');
        parent::__construct($params);
    }

    /**
     * @throws waException
     */
    public function execute()
    {
        if (wa()->getUser()->getRights('shop', 'products')) {
            $image_ids = $this->getRequest()->get('images');
            $product_id = $this->getRequest()->get('product_id', 0, 'int');

            if (!empty($image_ids) && $product_id > 0) {

                $settings = $this->plugin->getSettings();
                $product = new shopProduct($product_id);

                $group_model = new shopVkshopPluginGroupModel();
                $groups = $group_model->getLoginedGroups();

                $album_model = new shopVkshopPluginAlbumsModel();

                $images = $product->getImages();

                foreach ($images as $key => $image) {
                    if (!in_array($image['id'], $image_ids)) {
                        unset($images[$key]);
                    }
                }

                if (empty($groups)) {
                    $errors[] = _wp('Undefined groups in plugin settings.');
                }
                else {
                    $group = reset($groups);
                    $product->setData('group_id', $group['id']);
                    //$product->group_id = $group['id'];
                }

                if (empty($errors)) {
                    foreach ($groups as $key => $group) {
                        if ($group['auth']) {

                            $tools = new shopVkshopPluginTools($group['vk']);
                            $r = $tools->compareAlbums();

                            if ($r === true) {

                                $groups[$key]['albums'] = $album_model->getAlbumsByGroupId($group['id']);
                            }
                            else $errors[] = $r;
                        }
                    }

                }
                else {
                    $errors[] = _wp('VK is not authentificated.');
                }

                $prepare = new shopVkshopPluginPrepare($product, $settings);
                $product = $prepare->prepareHashtags()->prepareLink()->prepareFeatures()->getProduct();

                $this->view->assign(compact('images', 'product'));
                $caption = $this->view->fetch('string:' . $settings['caption_tmpl']);

                $caption = html_entity_decode($caption);
                $caption = str_replace("<br>", "\r\n", $caption);
                $caption = str_replace("<br />", "\r\n", $caption);
                $caption = str_replace("<br/>", "\r\n", $caption);

                $this->view->assign(
                    'caption', mb_substr(strip_tags($caption), 0, intval($settings['max_description_lenght']), 'utf-8')
                );
                $this->view->assign('locale', $locale = wa()->getLocale());

                if (!empty($groups)) {
                    $this->view->assign('groups', $groups);

                    $group = reset($groups);
                    $album_model = new shopVkshopPluginAlbumsModel();
                    $this->view->assign('albums', $album_model->getAlbumsByGroupId($group['id']));
                }
                else {
                    $this->view->assign('groups', array());
                    $this->view->assign('albums', array());
                }

                $this->view->assign('settings', $settings);
            }
        }
    }
}

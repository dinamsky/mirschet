<?php
/**
 * Created by PhpStorm.
 * User: snark | itfrogs.ru
 * Date: 5/18/17
 * Time: 9:39 PM
 */

class shopVkshopPluginBackendGetalbumsController extends waJsonController
{
    /**
     * @var shopVkshopPlugin $plugin
     */
    private $plugin;

    /**
     * shopVkshopPluginBackendGetalbumsController constructor.
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
        $group_id = waRequest::post('group_id', 0, 'int');

        if ($group_id > 0) {
            $album_model = new shopVkshopPluginAlbumsModel();
            $albums = $album_model->getAlbumsByGroupId($group_id);
            $view = wa()->getView();
            $view->assign('albums', $albums);
            $template = $view->fetch($this->plugin->getPluginPath() . '/templates/actions/backend/albums.html');
            $this->response = array(
                'albums' => $template,
            );
        }
        else {
            $this->setError(_wp('Unknown group ID'));
        }

    }


}
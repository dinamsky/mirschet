<?php

/**
 * Created by PhpStorm.
 * User: snark | itfrogs.ru
 * Date: 1/9/16
 * Time: 8:03 PM
 * готово к 3.3.0
 */
class shopVkshopPluginBackendVkloginController extends waJsonController
{
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
     * @throws waException
     */
    public function execute()
    {
        if (wa()->getUser()->getRights('shop', 'products')) {
            $plugin = self::getPlugin();
            $asm = new waAppSettingsModel();

            $view = self::getView();

            $code = waRequest::post('code', '', 'string');

            $group_id = waRequest::post('group_id', 0, 'int');

            $logout = waRequest::post('logout', 0, 'int');
            $errors = array();
            $data = array();

            $group_model = new shopVkshopPluginGroupModel();
            $group = $group_model->getById($group_id);

            if (!empty($group)) {
                $vk = new shopVkshopPluginApi($group['app_id'], $group['app_secret']);
                $vk->setApiVersion('5.87');
                $callback_url = wa()->getUrl(true) . '?action=importexport#/vkshop/getcode/' . '?group=' . $group['id'];
                $login_url = $vk->getAuthorizeURL('photos,groups,market,offline,nohttps', $callback_url);
                $view->assign('login_url', $login_url);

                if ($logout == 1) {
                    $asm->del(array('shop', 'vkshop'), 'access_token');
                    $asm->del(array('shop', 'vkshop'), 'token_datetime');
                    $asm->del(array('shop', 'vkshop'), 'user_id');
                    $asm->del(array('shop', 'vkshop'), 'secret');

                    $group['user_id'] = null;
                    $group['first_name'] = null;
                    $group['last_name'] = null;
                    $group['photo_50'] = null;
                    $group['access_token'] = null;
                    $group['secret'] = null;
                    $group['token_datetime'] = null;

                    $group_model->updateById($group['id'], $group);
                } else {
                    $data = $vk->getAccessToken($code, $callback_url);
                    if (!isset($data['error'])) {
                        $access_token = $data['access_token'];
                        $user_id = $data['user_id'];
                        $secret = $data['secret'];

                        $token_datetime = date('Y-m-d H:i:s');

                        $asm->set(array('shop', 'vkshop'), 'access_token', $access_token);
                        $asm->set(array('shop', 'vkshop'), 'token_datetime', $token_datetime);
                        $asm->set(array('shop', 'vkshop'), 'user_id', $user_id);
                        $asm->set(array('shop', 'vkshop'), 'secret', $secret);

                        $vk = new shopVkshopPluginApi($group['app_id'], $group['app_secret'], $access_token, $secret);
                        $vk->setApiVersion('5.87');
                        if (empty($errors) && $vk->isAuth()) {
                            $data = $vk->api(
                                'users.get', array(
                                'users_ids' => $user_id,
                                'fields' => 'photo_50,screen_name',
                            ), 'array', 'get', true
                            );
                            $user = reset($data['response']);
                            $view->assign('vkshop_user', $user);

                            $group['user_id'] = $user['id'];
                            $group['first_name'] = $user['first_name'];
                            $group['last_name'] = $user['last_name'];
                            $group['photo_50'] = $user['photo_50'];
                            $group['access_token'] = $access_token;
                            $group['secret'] = $secret;
                            $group['token_datetime'] = $token_datetime;

                            $group_model->updateById($group['id'], $group);

                            $view->assign('group', $group);

                        }
                    } else {
                        if (waSystemConfig::isDebug()) {
                            waLog::dump($errors, 'vkshop-auth-errors.log');
                        }
                        $asm->del(array('shop', 'vkshop'), 'access_token');
                        $asm->del(array('shop', 'vkshop'), 'token_datetime');
                        $asm->del(array('shop', 'vkshop'), 'user_id');
                        $asm->del(array('shop', 'vkshop'), 'secret');
                        $errors = array($data);
                    }
                }

                $groups = $group_model->select('*')->where('user_id IS NOT NULL')->fetchAll();
                if (!empty($groups)) {
                    $view->assign('groups', $groups);

                    $group = reset($groups);
                    $album_model = new shopVkshopPluginAlbumsModel();
                    $view->assign('albums', $album_model->getAlbumsByGroupId($group['id']));
                }
                else {
                    $view->assign('groups', array());
                    $view->assign('albums', array());
                }

                $view->assign('errors', $errors);
                $login_template = $view->fetch($plugin->getPluginPath() . '/templates/actions/backend/login.html');
                $groups_template = $view->fetch($plugin->getPluginPath() . '/templates/actions/backend/groups.html');
                $albums_template = $view->fetch($plugin->getPluginPath() . '/templates/actions/backend/albums.html');
                $errors_template = $view->fetch($plugin->getPluginPath() . '/templates/actions/backend/errors.html');

                $this->response = array(
                    'login_template' => $login_template,
                    'groups_template' => $groups_template,
                    'albums_template' => $albums_template,
                    'errors_template' => $errors_template,
                    'errors' => $errors,
                    'data' => $data,
                );
            }
            else {
                $this->setError('Group not defined');
            }

        } else {
            $this->setError('Access denied');
        }
    }
}
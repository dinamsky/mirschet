<?php
/**
 * Created by PhpStorm.
 * User: snark | itfrogs.ru
 * Date: 12/18/17
 * Time: 9:18 PM
 */

class shopVkshopCli extends waCliController
{

    /**
     * @throws waException
     */
    public function execute()
    {
        $plugin = wa()->getPlugin('vkshop');
        $settings = $plugin->getSettings();

        $group_model = new shopVkshopPluginGroupModel();
        $groups = $group_model->getLoginedGroups();

        if (($settings['cron_timestamp'] + 60) > time()) {
            waLog::log(_wp('Attempting to re-run the script. Stopped.'), 'vkshop-cron.log');
            print _wp('The script is still running.');
            die(_wp('The script is still running.'));
        }

        $asm = new waAppSettingsModel();
        $asm->set(array('shop', 'vkshop'), 'cron_timestamp', time());

        $vpm = new shopVkshopPluginProductsModel();
        $ptqm = new shopVkshopPluginProductsTempQueueModel();
        $cron_model = new shopVkshopPluginProductsCronModel();

        $offset = 0;
        $query_string  = 'SELECT vp.*, p.status, p.count FROM shop_vkshop_products vp ';
        $query_string .= 'JOIN shop_product p ON p.id = vp.product_id ';
        //$query_string .= 'WHERE vp.datetime IS NULL OR (p.edit_datetime IS NOT NULL AND vp.datetime < p.edit_datetime) ';
        $query_string .= 'LIMIT i:offset, 100 ';

        if ($settings['cron_autoupdate']) {
            while ($products = $vpm->query($query_string, array('offset' => $offset))->fetchAll()) {
                $offset += 100;
                foreach ($products as $p) {
                    $data = array(
                        'product_id'    => $p['product_id'],
                        'group_id'      => $p['group_id'],
                        'action'        => 'update',
                    );

                    //$product = new shopProduct($p['product_id']);
                    if (!empty($product)) {
                        if ($settings['cron_delete_hidden'] && $p['status'] == 0) {
                            $data['action'] = 'delete';
                        }

                        if ($settings['cron_delete_nonstock'] && $p['count'] === 0) {
                            $data['action'] = 'delete';
                        }
                    }

                    $row = $cron_model->getByField($data);
                    if (empty($row)) {
                        $cron_model->insert($data);
                    }
                }
                $asm->set(array('shop', 'vkshop'), 'cron_timestamp', time());
            }
        }

        $params = array(
            'settings'      => $settings,
        );

        foreach ($groups as $group) {
            $offset = 0;
            $query_string  = 'SELECT vp.* FROM shop_vkshop_products vp ';
            $query_string .= 'WHERE vp.status IS NULL AND vp.group_id = ' . intval($group['id']) . ' ';
            $query_string .= 'LIMIT i:offset, 100 ';

            if ($group['auth']) {
                /**
                 * @var shopVkshopPluginApi $vk
                 */
                $vk = $group['vk'];

                while ($products = $vpm->query($query_string, array('offset' => $offset))->fetchAll()) {
                    $offset += 100;

                    $vk->checkItemsAlbums($products);
                }

                $offset = 0;
                $query_string  = 'SELECT * FROM shop_vkshop_products_cron ';
                $query_string .= 'LIMIT i:offset, 100 ';
                while ($products = $vpm->query($query_string, array('offset' => $offset))->fetchAll()) {
                    $offset += 100;

                    $tools = new shopVkshopPluginTools($vk);

                    $data = array(
                        'timestamp'         => time(),
                        'temppath'          => wa()->getTempPath(),
                        'access_token'      => $settings['access_token'],
                        'currency'          => $settings['currency'],
                        'category_id'       => $settings['category_id'],
                        //'album_id'          => $settings['cron_album'],
                        //'group_id'          => $settings['cron_group'],
                        'upload_images'     => false,
                        'count'             => 0,
                    );

                    foreach ($products as $p) {
                        $vkproduct = $vpm->getByField('product_id', $p['product_id']);
                        $params['vk_product'] = $vkproduct;
                        $ptqm->queueProduct($p['product_id'], $p['group_id'], $p['action']);

                        switch ($p['action']) {
                            case 'delete':
                                if (empty($vkproduct) || !$p['group_id']) {
                                    $cron_model->deleteById($p['id']);
                                }
                                $data['action'] = 'delete';
                                $data = $tools->productDelete($p, $params, $data);
                                break;
                            case 'update':
                                if (empty($vkproduct) || !$p['group_id']) {
                                    $cron_model->deleteById($p['id']);
                                }
                                $data['upload_images'] = false;
                                $data = $tools->productSend($p, $params, $data);
                                break;
                            case 'add':
                                $data['upload_images'] = true;
                                $data = $tools->productSend($p, $params, $data);
                                break;
                        }

                        $temp_product = $ptqm->getByField('product_id', $p['product_id']);
                        if (empty($temp_product)) {
                            $cron_model->deleteById($p['id']);
                        }
                    }

                    $asm->set(array('shop', 'vkshop'), 'cron_timestamp', time());
                }
            }
            else {
                waLog::log(_wp('VK is not authentificated.'), 'vkshop-cron.log');
                //die(_wp('VK is not authentificated.'));
            }
        }
    }
}
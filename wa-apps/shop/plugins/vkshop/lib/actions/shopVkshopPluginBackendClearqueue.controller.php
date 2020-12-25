<?php

/**
 * Created by PhpStorm.
 * User: snark | itfrogs.ru
 * Date: 1/14/16
 * Time: 1:33 AM
 */
class shopVkshopPluginBackendClearqueueController extends waJsonController
{
    public function execute()
    {
        if (wa()->getUser()->getRights('shop', 'products')) {
            $pqm = new shopVkshopPluginProductsQueueModel();
            $pdm = new shopVkshopPluginProductsDisabledModel();
            $model = new waModel();
            $model->exec('DELETE FROM shop_vkshop_products_queue');
            $model->exec('DELETE FROM shop_vkshop_products_temp_queue');

            $this->response = array(
                'disabled' => $pdm->countAll(),
                'queued' => $pqm->countAll(),
            );
        } else {
            $this->setError('Access denied');
        }
    }
}
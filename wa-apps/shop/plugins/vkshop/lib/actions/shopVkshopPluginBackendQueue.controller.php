<?php

/**
 * Created by PhpStorm.
 * User: snark | itfrogs.ru
 * Date: 1/9/16
 * Time: 12:05 AM
 */
class shopVkshopPluginBackendQueueController extends waJsonController
{
    public function execute()
    {
        if (wa()->getUser()->getRights('shop', 'products')) {
            $products = waRequest::post('products');

            $pqm = new shopVkshopPluginProductsQueueModel();
            $pdm = new shopVkshopPluginProductsDisabledModel();

            if (!empty($products)) {
                if (!isset($products['hash'])) {
                    $pqm->queueProducts($products['product_id']);
                } elseif ($products['hash'] == 'all') {
                    $pqm->queueAll();
                } else {
                    $result = explode('/', $products['hash']);
                    $ids = array();

                    if ($result[0] == 'category') {
                        $category_id = intval($result[1]);
                        if ($category_id > 0) {
                            $category_model = new shopCategoryModel();
                            $category = $category_model->getById($category_id);
                            $cpm = new shopCategoryProductsModel();
                            if ($category['include_sub_categories']) {
                                $descendants = $category_model->descendants($category_id, true)->fetchAll('id');
                                $descendants_ids = array_keys($descendants);
                            } else {
                                $descendants_ids = array(0 => $category_id);
                            }
                            $products = $cpm->select('product_id')->where(
                                'category_id IN (' . implode(
                                    ',',
                                    $descendants_ids
                                ) . ')'
                            )->fetchAll('product_id');
                            $ids = array_keys($products);
                        }
                    } elseif ($result[0] == 'set') {
                        $spm = new shopSetProductsModel();
                        $products = $spm->getByField('set_id', $result[1], true);
                        foreach ($products as $product) {
                            $ids[] = $product['product_id'];
                        }
                    } elseif ($result[0] == 'type') {
                        $type_id = intval($result[1]);
                        if ($type_id > 0) {
                            $pm = new shopProductModel();
                            $products = $pm->getByField('type_id', $type_id, true);
                            foreach ($products as $product) {
                                $ids[] = $product['id'];
                            }
                        }
                    }

                    $pqm->queueProducts($ids);
                }
            }
            $this->response = array(
                'disabled' => $pdm->countAll(),
                'queued' => $pqm->countAll(),
            );
        } else {
            $this->setError('Access denied');
        }
    }
}
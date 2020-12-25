<?php

class shopVkshopPluginBackendDisableController extends waJsonController
{
    public function execute()
    {
        if (wa()->getUser()->getRights('shop', 'products')) {
            $product_id = waRequest::request('id', 0, 'int');
            $disable = waRequest::request('disable', 0, 'int');
            $action = waRequest::post('action');
            $products = waRequest::post('products');

            $pdm = new shopVkshopPluginProductsDisabledModel();
            $pqm = new shopVkshopPluginProductsQueueModel();

            if (!$action) {
                if ($product_id) {
                    if ($disable === 1) {
                        $pdm->disableProduct($product_id);
                    } else {
                        $pdm->enableProduct($product_id);
                    }
                }
            } else {
                if (!empty($products)) {

                    if (!isset($products['hash'])) {
                        if ($action == 'disable') {
                            //$collection->addWhere()
                            $pdm->disableProducts($products['product_id']);
                        } elseif ($action == 'undisable') {
                            $pdm->enableProducts($products['product_id']);
                        }
                    } elseif ($products['hash'] == 'all') {
                        if ($action == 'disable') {
                            $pdm->disableAll();
                        } elseif ($action == 'undisable') {
                            $pdm->enableAll();
                        }

                    } else {
                        $result = explode('/', $products['hash']);
                        $ids = array();

                        if ($result[0] == 'category') {
                            $category_id = intval($result[1]);
                            if ($category_id > 0) {
                                $cpm = new shopCategoryProductsModel();
                                $products = $cpm->getByField('category_id', $category_id, true);
                                foreach ($products as $product) {
                                    $ids[] = $product['product_id'];
                                }
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
                        if ($action == 'disable') {
                            $pdm->disableProducts($ids);
                        } elseif ($action == 'undisable') {
                            $pdm->enableProducts($ids);
                        }
                    }
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


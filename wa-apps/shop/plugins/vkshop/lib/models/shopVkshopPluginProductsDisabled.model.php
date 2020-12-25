<?php

/**
 * Created by PhpStorm.
 * User: snark | itfrogs.ru
 * Date: 1/8/16
 * Time: 11:38 PM
 */
class shopVkshopPluginProductsDisabledModel extends waModel
{
    /**
     * @var string
     */
    protected $table = 'shop_vkshop_products_disabled';

    /**
     * Primary key of the table
     * @var string
     */
    protected $id = 'product_id';

    /**
     * @return bool|int|mixed
     */
    public function disableAll()
    {
        $productModel = new shopProductModel();
        $products = $productModel->select('id')->fetchAll();
        $this->enableAll();
        $data = array();
        foreach ($products as $product) {
            array_push($data, array('product_id' => $product['id']));
        }
        $this->multipleInsert($data);

        $pqm = new shopVkshopPluginProductsQueueModel();
        $pqm->unqueueAll();

        return $this->countAll();
    }

    /**
     * @return bool|int|mixed
     */
    public function enableAll()
    {
        $this->exec("DELETE FROM " . $this->table);
        return $this->countAll();
    }

    /**
     * @return bool|int|mixed
     */
    public function countAll()
    {
        return $this->select('COUNT(product_id)')->fetchField();
    }

    /**
     * @param $product_id
     * @return bool
     */
    public function isProductDisabled($product_id)
    {
        $row = $this->getById($product_id);
        if (!empty($row)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $product_ids
     * @return bool|int|mixed
     */
    public function disableProducts($product_ids)
    {
        $this->deleteByField('product_id', $product_ids);
        $data = array();
        $disabled_products = array();
        foreach ($product_ids as $id) {
            array_push($data, array('product_id' => $id));
            $disabled_products[] = $id;
        }
        $this->multipleInsert($data);
        $pqm = new shopVkshopPluginProductsQueueModel();
        $pqm->unqueueProducts($disabled_products);
        return $this->countAll();
    }

    /**
     * @param $product_ids
     * @return bool|int|mixed
     */
    public function enableProducts($product_ids)
    {
        $this->deleteByField('product_id', $product_ids);
        return $this->countAll();
    }

    /**
     * @param $product_id
     */
    public function disableProduct($product_id)
    {
        if (!$this->isProductDisabled($product_id)) {
            $data = array(
                'product_id' => $product_id,
            );
            $this->insert($data);

            $pqm = new shopVkshopPluginProductsQueueModel();
            $pqm->unqueueProduct($product_id);
        }
    }

    /**
     * @param $product_id
     */
    public function enableProduct($product_id)
    {
        $this->deleteById($product_id);
    }
}


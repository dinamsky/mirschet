<?php

/**
 * Created by PhpStorm.
 * User: snark | itfrogs.ru
 * Date: 1/8/16
 * Time: 11:38 PM
 */
class shopVkshopPluginProductsQueueModel extends waModel
{
    /**
     * @var string
     */
    protected $table = 'shop_vkshop_products_queue';

    /**
     * Primary key of the table
     * @var string
     */
    protected $id = 'product_id';

    /**
     * @return bool|int|mixed
     */
    public function queueAll()
    {
        $productModel = new shopProductModel();
        $products = $productModel->select('id')->where('status = 1')->fetchAll();
        $this->unqueueAll();
        $data = array();
        foreach ($products as $product) {
            array_push($data, array('product_id' => $product['id']));
        }
        $this->multipleInsert($data);

        $pdm = new shopVkshopPluginProductsDisabledModel();
        $disabled_products = $pdm->getAll();
        $products = array();
        foreach ($disabled_products as $product) {
            $products[] = $product['product_id'];
        }

        $this->unqueueProducts($products);

        return $this->countAll();
    }

    /**
     * @return bool|int|mixed
     */
    public function unqueueAll()
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
    public function isProductQueued($product_id)
    {
        $row = $this->getByField(array('product_id' => $product_id));
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
    public function queueProducts($product_ids)
    {
        $this->deleteByField('product_id', $product_ids);
        $data = array();
        foreach ($product_ids as $id) {
            array_push($data, array('product_id' => $id));
        }
        $this->multipleInsert($data);

        $pdm = new shopVkshopPluginProductsDisabledModel();
        $disabled_products = $pdm->getAll();
        $products = array();
        foreach ($disabled_products as $product) {
            $products[] = $product['product_id'];
        }

        $this->unqueueProducts($products);
        return $this->countAll();
    }

    /**
     * @param $product_ids
     * @return bool|int|mixed
     */
    public function unqueueProducts($product_ids)
    {
        $this->deleteByField('product_id', $product_ids);
        return $this->countAll();
    }

    /**
     * @param $product_id
     */
    public function queueProduct($product_id)
    {
        if (!$this->isProductQueued($product_id)) {
            $data = array(
                'product_id' => $product_id,
            );
            $this->insert($data);
        }
    }

    /**
     * @param $product_id
     */
    public function unqueueProduct($product_id)
    {
        $this->deleteById($product_id);
    }
}


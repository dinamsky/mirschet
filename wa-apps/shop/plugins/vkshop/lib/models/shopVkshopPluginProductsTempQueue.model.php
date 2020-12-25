<?php

/**
 * Created by PhpStorm.
 * User: snark | itfrogs.ru
 * Date: 1/10/16
 * Time: 2:13 AM
 */
class shopVkshopPluginProductsTempQueueModel extends waModel
{
    /**
     * @var string
     */
    protected $table = 'shop_vkshop_products_temp_queue';

    /**
     * Primary key of the table
     * @var string
     */
    protected $id = 'id';

    /**
     * @param bool $new
     * @param null $group_id
     * @param string $action
     * @return bool|int|mixed
     */
    public function queueAll($new = false, $group_id = null, $action = 'send')
    {
        $productModel = new shopProductModel();
        $where_new = '';
        if ($new) {
            $where_new = ' AND v.product_id IS NULL ';
        }
        $products = $productModel->query('SELECT p.id FROM shop_product p LEFT JOIN shop_vkshop_products v ON p.id = v.product_id WHERE p.status = 1 ' . $where_new)->fetchAll();
        $this->unqueueAll();
        $data = array();
        foreach ($products as $product) {
            array_push($data, array('product_id' => $product['id'], 'group_id' => $group_id, 'action' => $action));
        }
        $this->multipleInsert($data);

        $pdm = new shopVkshopPluginProductsDisabledModel();
        $disabled_products = $pdm->getAll();
        $products = array();
        foreach ($disabled_products as $product) {
            $products[] = $product['product_id'];
        }

        $this->unqueueProducts($products);

        return $this->countAll($action);
    }

    /**
     * @param string $action
     * @return bool|int|mixed
     */
    public function unqueueAll($action = 'send')
    {
        $this->exec("DELETE FROM " . $this->table . ' WHERE action = s:action',
            array(
                'action' => $action,
            )
        );
        return $this->countAll($action);
    }

    /**
     * @param string $action
     * @return bool|int|mixed
     */
    public function countAll($action = 'send')
    {
        return $this->select('COUNT(product_id)')->where('action = "' . $this->escape($action) . '"')->fetchField();
    }

    /**
     * @param $product_id
     * @param null $group_id
     * @param string $action
     * @return bool
     */
    public function isProductQueued($product_id, $group_id = null, $action = 'send')
    {
        $row = $this->getByField(array('product_id' => $product_id, 'group_id' => $group_id, 'action' => $action));
        if (!empty($row)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $products
     * @param string $action
     * @return bool|int|mixed
     */
    public function queueProducts($products, $action = 'send')
    {
        foreach ($products as $product) {
            $this->deleteByField(array('product_id' => $product['id'], 'group_id' => $product['group_id'], 'action' => $action));
        }

        $data = array();
        foreach ($products as $product) {
            array_push($data, array('product_id' => $product['id'], 'group_id' => $product['group_id'], 'action' => $action));
        }
        $this->multipleInsert($data);

        $pdm = new shopVkshopPluginProductsDisabledModel();
        $disabled_products = $pdm->getAll();
        $products = array();
        foreach ($disabled_products as $product) {
            $products[] = $product['product_id'];
        }

        $this->unqueueProducts($products, $action);
        return $this->countAll($action);
    }

    /**
     * @param $product_ids
     * @param string $action
     * @return bool|int|mixed
     */
    public function unqueueProducts($product_ids, $action = 'send')
    {
        foreach ($product_ids as $id) {
            $this->deleteByField(
                array(
                    'product_id' => $id,
                    'action' => $action,
                )
            );
        }

        return $this->countAll($action);
    }

    /**
     * @param $product_id
     * @param null $group_id
     * @param string $action
     */
    public function queueProduct($product_id, $group_id = null, $action = 'send')
    {
        if (!$this->isProductQueued($product_id, $group_id, $action)) {
            $data = array(
                'product_id' => $product_id,
                'group_id' => $group_id,
                'action' => $action,
            );
            $this->insert($data);
        }
    }

    /**
     * @param $product_id
     */
    public function unqueueProduct($product_id)
    {
        $this->deleteByField('product_id', $product_id);
    }

    /**
     * @param int $limit
     * @param string $action
     * @return array
     */
    public function getProducts($limit = 10, $action = 'send')
    {
        $products = $this->select('*')->where('action = "' . $this->escape($action) . '"')->limit($limit)->fetchAll();
        /*
        $ids = array();
        foreach ($products as $product) {
            $ids[] = $product['product_id'];
        }
        */
        return $products;
    }

    /**
     * @param $product_id
     * @param null $group_id
     * @param int $maxcount
     * @param string $action
     * @return bool
     */
    public function increaseCount($product_id, $group_id = null, $maxcount = 1, $action = 'send')
    {
        $row = $this->getByField(
            array(
                'product_id'    => $product_id,
                'group_id'      => $group_id,
                'action'        => $action,
            )
        );
        if ($row['count'] >= $maxcount) {
            $this->deleteByField(
                array(
                    'product_id'    => $product_id,
                    'group_id'      => $group_id,
                    'action'        => $action,
                )
            );
            return true;
        } else {
            $this->query(
                'UPDATE shop_vkshop_products_temp_queue SET count = count + 1 WHERE product_id = i:product_id AND group_id = i:group_id AND action = s:action',
                array(
                    'product_id' => $product_id,
                    'group_id' => $group_id,
                    'action' => $action,
                )
            );
            return false;
        }
    }
}

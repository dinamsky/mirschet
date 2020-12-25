<?php

class shopUnclaimedPlugin extends shopPlugin
{

    public function backendProducts()
    {

        $view = wa()->getView();
        return array(
            'sidebar_top_li' => $view->fetch($this->path.'/templates/hooks/backend_products.sidebar_top_li.html'),
        );
    }


    public function productsCollection(&$params)
    {
        /**
         * @var shopProductsCollection $collection
         */
        $collection = $params['collection'];

        if($collection instanceof shopProductsCollection) {
            $hash = $collection->getHash();

            if ($hash[0] == 'unclaimed') {

                $m = new shopOrderModel();
                $date = strtotime(sprintf('-%d days', $this->getSettings('order_create')));
                $date = date('Y-m-d 00:00:00', $date);

                $sql = 'SELECT DISTINCT i.product_id FROM shop_order o '.
                    'JOIN shop_order_items i ON o.id = i.order_id '.
                    'WHERE o.create_datetime >= :create_datetime';
                $pids = $m->query($sql, array(
                    'create_datetime' => $date
                ))->fetchAll(null, true);

                if ($pids) {
                    $collection->addWhere('p.id NOT IN('.implode(',',$pids).')');

                    if($this->getSettings('product_status')) {
                        $collection->addWhere('p.status=1');
                    }

                    $date = strtotime(sprintf('-%d days', $this->getSettings('product_create')));
                    $date = date('Y-m-d 00:00:00', $date);
                    $collection->addWhere('p.create_datetime <= "'.$date.'"');
                }

                if ($params['auto_title']) {
                    $collection->addTitle('Невостребованные товары');
                }

                return true;
            }
        }
        return false;
    }
}

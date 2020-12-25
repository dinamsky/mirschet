<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @version 2.0.0
 * @copyright Serge Rodovnichenko, 2016
 */

/**
 * готово к 3.3.0
 */
class shopVkshopPluginBackendFormAction extends waViewAction
{
    /**
     * @throws waException
     */
    public function execute()
    {
        if (wa()->getUser()->getRights('shop', 'products')) {
            $plugin = wa('shop')->getPlugin('vkshop');
            $settings = $plugin->getSettings();
            $product = new shopProduct($this->getRequest()->get('product_id', 0, waRequest::TYPE_INT));
            $images = array();

            $group_model = new shopVkshopPluginGroupModel();
            $groups = $group_model->getLoginedGroups();

            if ($product->getId() && !empty($groups)) {
                $images = $product['images'];
                $vpm = new shopVkshopPluginProductsModel();

                $vk_products = $vpm->getByField('product_id', $product->getId(), true);
                $vk_groups = array();

                if (!empty($vk_products)) {

                    foreach ($groups as $i => $group) {
                        if ($group['auth']) {
                            $group['vk']->checkItemsAlbums($vk_products);
                        }

                        $vk_groups[$i] = array(
                            'name'      => $group['group_name'],
                            'albums'    => $this->getProductAlbums($product->getId(), $group['id']),
                        );
                    }
                }
            }

            $this->view->assign(compact('images', 'product', 'settings', 'vk_groups', 'vk_products'));
        }
    }

    private function getProductAlbums($product_id, $group_id) {
        $products_albums_model = new shopVkshopPluginProductsAlbumsModel();
        return $products_albums_model-> query(
            'SELECT pa.*, a.name FROM shop_vkshop_products_albums pa '
            . 'JOIN shop_vkshop_albums a ON a.album_id = pa.album_id '
            . 'WHERE pa.product_id = i:product_id AND pa.group_id = i:group_id ',
            array(
                'product_id'    => $product_id,
                'group_id'      => $group_id,
            )
        )->fetchAll();
    }
}

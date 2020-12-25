<?php

class shopVkgoodsPluginWaitcategoryModel extends waModel
{
    protected $table = 'shop_vkgoods_wait_category';

    public function getNewProductByCategory($category_id, $group_ids)
    {
        $products = array();

        if (!$wcats = $this->getByField(array('cid' => $category_id, 'gid' => $group_ids), true)) {
            return;
        }
        $model_category_products = new shopCategoryProductsModel();
        $product_ids_without_subcats = array_keys($model_category_products->getByField('category_id', $category_id, 'product_id'));
        $product_ids_with_subcats = false;
        foreach ($wcats as $wcat) {
            if ($wcat['subcategories']) {
                if ($product_ids_with_subcats === false) {
                    $model_category = new shopCategoryModel();
                    $subcats = $model_category->getSubcategories($category_id, true);
                    $category_ids = array_merge(array($category_id), array_keys($subcats));
                    $product_ids_with_subcats = array_keys($model_category_products->getByField('category_id', $category_ids, 'product_id'));
                }
                $product_ids = $product_ids_with_subcats;
            } else {
                $product_ids = $product_ids_without_subcats;
            }
            $model_vkgoods_product = new shopVkgoodsPluginProductModel();
            $search_data = array(
                'pid' => $product_ids,
                'gid' => $wcat['gid']
            );
            $publish_pids = array_keys($model_vkgoods_product->getByField($search_data, 'pid'));
            $product_ids_to_public = array_diff($product_ids, $publish_pids);
            foreach ($product_ids_to_public as $pid) {
                $products[] = array(
                    'pid' => $pid,
                    'storefront' => $wcat['storefront'],
                    'gid' => $wcat['gid'],
                    'aid' => $wcat['aid'],
                    'category_id' => $wcat['category_id'],
                    'desc' => $wcat['desc'],
                    'all_photo' => $wcat['all_photo'],
                    'f_price' => $wcat['f_price'],
                );
            }
        }
        return $products;
    }

    public function getWaitCategories()
    {
        $result = $this->where('1')->fetchAll('cid', 2);
        $categories = array();
        foreach ($result as $category_id => $publics) {
            foreach ($publics as $public) {
                $categories[$category_id][$public['gid']] = $public;
            }
        }
        return $categories;
    }

    public function getByCategoryID($category_id)
    {
        $publics = $this->getByField('cid', $category_id, true);
        $settings = wa()->getPlugin('vkgoods')->getSettings();
        if ($settings['vk_user_id'] && $settings['token']) {
            $vk_session = new shopVkgoodsPluginVkapi($settings['vk_user_id'], $settings['token']);
            if ($groups = $vk_session->getUserGroups($settings['vk_user_id'])) {
                foreach ($publics as &$public) {
                    $public['group_name'] = ifset($groups[$public['gid']]['name'], 'Сообщество недоступно');
                }
            }
        }
        return $publics;
    }
}
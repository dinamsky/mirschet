<?php

/**
 * Created by PhpStorm.
 * User: onehalf
 * Date: 10.04.15
 * Time: 14:06
 */
class shopF7rootPlugin extends shopPlugin
{
    public function f7backendProducts($param)
    {
        $wp_feature = _wp('feature');
        $wp_feature_select = _wp('select feature');

        $wp_hidden = _wp('hidden');
        $wp_wo_category = _wp('without category');
        $wp_wo_picture = _wp('without picture');
        $wp_searchtext=_wp('search goods in category');//поиск товара в категории

        $select_items = _wp('select items');

        $url = wa_url(true);
    /*    waLog::dump($url);
        waLog::dump(waRequest::request('hash'));
        $hash_searchtext = "";
        $hash_request = waRequest::request('hash');
        if (substr($hash_request, 0, 17) == 'f7root-searchtext'){
            if (substr($hash_request, 17)) {
                $hash_items = explode("-", substr($hash_request, 17));
                $hash_searchtext = $hash_items[0];
            }
        }
    */
        $fm = new waModel();
        if ($this->getSettings('full_metod_root')) {
            $count = $fm->query('SELECT  count(pp.id) pcount FROM shop_product pp
                                LEFT JOIN shop_category_products cp1 ON pp.id = cp1.product_id
                                WHERE cp1.product_id IS NULL')->fetchAll();
        } else {
            $count = $fm->query('SELECT  count(pp.id) pcount FROM shop_product pp
                            WHERE category_id IS NULL')->fetchAll();
        }
        if ($this->getSettings('full_metod_image')) {
            $count_wopicture = $fm->query('SELECT  count(pp.id) pcount FROM shop_product pp
                                LEFT JOIN shop_product_images cp1 ON pp.id = cp1.product_id
                                WHERE cp1.product_id IS NULL')->fetchAll();
        } else {
            $count_wopicture = $fm->query('SELECT  count(pp.id) pcount FROM shop_product pp
                            WHERE image_id IS NULL')->fetchAll();
        }
        $count_hide = $fm->query('SELECT count(pp.id) pcount FROM shop_product pp  WHERE status=0 ')->fetchAll();
        $items = $this->f7rootlistitems();
        $items_options = '';

        foreach ($items as $key => $value) {
            $items_options .= '<option value="' . $key . '">' . $value[1] . '</option>';
        }

        $links = <<<HTML
            <li id="f7root-feature-" class="f7rootfeature">
            <i class="icon16 ss set-dynamic"></i>
            <select name="fid" class="nfid" title="{$wp_feature}" style="width: 140px; margin-bottom: 5px;" >
                <option value="">{$wp_feature_select}</option>
            </select><br />
            <span class="nfvid"></span><span class="count">-</span></li>
            <li id="f7root-">
                <span class="count">{$count[0]['pcount']}</span>
                <a href="#/products/hash=f7root"><i class="icon16 yes"></i>{$wp_wo_category}</a>
            </li>
            <li id="f7root-hide-">
                <span class="count">{$count_hide[0]['pcount']}</span>
                <a href="#/products/hash=f7root-hide"><i class="icon16 ss visibility"></i>{$wp_hidden}</a>
            </li>
            <li id="f7root-wopicture-">
                <span class="count">{$count_wopicture[0]['pcount']}</span>
                <a href="#/products/hash=f7root-wopicture"><i class="icon16 ss pt camera"></i>{$wp_wo_picture}</a>
            </li>
            <li id="f7root-find-" class="f7find">
                <span class="count">-</span>
                <i class="icon16 ss set-dynamic"></i>
                <select name="f7root-find" class="items" title="" style="width: 120px;" >
                    <option value="1" >{$select_items}</option>
                    {$items_options}
                </select>
            </li>
            <li id="f7root-searchtext-" class="f7root-searchtext">
                <span class="count">-</span>
                <i class="icon16 ss pt search"></i><input type="text" placeholder="{$wp_searchtext}" name="f7root-searchtext-input" class="f7root-searchtext-input" value="">
                
            </li>
            <script type="text/javascript" src="{$url}wa-apps/shop/plugins/f7root/js/f7root.js"></script>
HTML;

        return array(
            'sidebar_top_li' => $links,
        );
    }

    private function f7rootlistitems()
    {
        return array(
            'title' => array('where_text', _wp('no title'), 'meta_title'),
            'keywords' => array('where_text', _wp('no keywords'), 'meta_keywords'),
            'M-desc' => array('where_text', _wp('no META-description'), 'meta_description'),
            'desc' => array('where_text', _wp('no description'), 'description'),
            'summary'=> array('where_text',_wp('no summary'),'summary'),
            'badge' => array('where_not_null', _wp('with badge'), 'badge'),
            'wo_badge' => array('where_null', _wp('without badge'), 'badge'),
            'sku' => array('join', _wp('sku not available'), 'shop_product_skus', 'available', 0),
            'sku_more_one' => array('where_more', _wp('sku more one'), 'sku_count', 1),
            'sku_only_one' => array('where_equal', _wp('sku only one'), 'sku_count', 1),
            'sku_type' => array('where_equal', _wp('sku type'), 'sku_type', 1),
            'sku_name_duplicate' => array('join', _wp('sku name duplicate'), ' (SELECT `product_id` FROM `shop_product_skus` GROUP by `product_id`,`name`  HAVING count(*)>1)  ','','' ),
            'sku_code_duplicate' => array('join', _wp('sku code duplicate'), ' (SELECT `product_id` FROM `shop_product_skus` GROUP by `product_id`,`sku`  HAVING count(*)>1)  ','','' ),
            'type_id' => array('where_null', _wp('without type'), 'type_id'),
            'picture' => array('where_not_null', _wp('with image'), 'image_id'),
            'no_url' => array('where_text',_wp('no url'),'url'),
            'url_eq_id'=> array('where_equal', _wp('url eq id'), 'url', 'id')
        );
    }

    public function f7rootproductsCollection($params)
    {
        $collection = $params['collection'];

      //  $collection=new shopProductsCollection();

        $wp_hidden = _wp('hidden');
        $wp_wo_category = _wp('without category');
        $wp_any_appointed = _wp('Any appointed');
        $wp_is_not_appointed = _wp('isn\'t appointed');
        $wp_wo_picture = _wp('without picture');

        $hash = $collection->getHash();
        if (($hash[0] !== 'f7root') and
            ($hash[0] !== 'f7root-hide') and
            (substr($hash[0], 0, 14) !== 'f7root-feature') and
            ($hash[0] !== 'f7root-wopicture') and
            (substr($hash[0], 0, 11) !== 'f7root-find') and
            (substr($hash[0], 0, 17) !== 'f7root-searchtext')
        ){
            return null;
        }
        if ($hash[0] == 'f7root') {
            if ($this->getSettings('full_metod_root')) {
                $collection->addWhere('id in(SELECT DISTINCT pp.id FROM shop_product pp left JOIN shop_category_products cp1 ON pp.id = cp1.product_id WHERE cp1.product_id is null)');
            } else {
                $collection->addWhere('category_id is null');
            }
            if ($params['auto_title']) {
                $collection->addTitle($wp_wo_category);
            }
        } elseif ($hash[0] == 'f7root-wopicture') {
            if ($this->getSettings('full_metod_image')) {
                $collection->addWhere('id in(SELECT DISTINCT pp.id FROM shop_product pp left JOIN shop_product_images cp1 ON pp.id = cp1.product_id WHERE cp1.product_id is null)');
            } else {
                $collection->addWhere('image_id is null');
            }
            if ($params['auto_title']) {
                $collection->addTitle($wp_wo_picture);
            }
        } elseif ($hash[0] == 'f7root-hide') {
            $collection->addWhere('Status=0');
            if ($params['auto_title']) {
                $collection->addTitle($wp_hidden);
            }
        } elseif (substr($hash[0], 0, 14) == 'f7root-feature') {
            if (substr($hash[0], 14)) {
                $feature = explode("-", substr($hash[0], 14));
                $rm = new shopF7rootModel();
                if (!isset($feature[1])) {
                    return false;
                }
                $features = $rm->getFeatureName($feature[0]);
                $category_name = "";
                $alias=null;
                if (isset($feature[2])) {
                    if ($feature[2] <> 0) {
                        $cm = new shopCategoryModel();

                        $category = $cm->getById($feature[2]);

                        $category_name = " - " . _wp('Category') . ": " . $category['name'];

                        if ($this->getSettings('include_subcategory')) {
                            $all_subcategory = $cm->getTree($feature[2]);
                            $all_subcategory_ids = implode(',', array_keys($all_subcategory));

                            $where_category = ' IN (' . $all_subcategory_ids . ")";
                        } else {
                            $where_category = ' = ' . (int)$feature[2];
                        }
                        $alias =':table.product_id = ' . $collection->addJoin('shop_category_products', null, ':table.category_id ' . $where_category). '.product_id';

                    }
                }

                switch ($feature[1]) {
                    case 'yes':
                        $feature_value=$wp_any_appointed;//'Любое назначенное';
                        $collection->addJoin('shop_product_features',  $alias , ':table.feature_id=' . $rm->escape($feature[0], 'int') );
                        break;
                    case 'no':
                        $feature_value=$wp_is_not_appointed;//'Не назначенно';
                        $collection->addWhere('id in(SELECT DISTINCT pp.id FROM shop_product pp left JOIN (select * from shop_product_features where feature_id='.$rm->escape($feature[0], 'int').') cp1 ON pp.id = cp1.product_id WHERE cp1.product_id is null)');
                        break;
                    default:
                        $feature_value = $rm->getFeatureValuesById($feature[0], $feature[1]);
                        $collection->addJoin('shop_product_features', $alias , ':table.feature_id=' . $rm->escape($feature[0], 'int') . ' and :table.feature_value_id=' . $rm->escape($feature[1], 'int'));
                        break;
                }



                if ($params['auto_title']) {
                    $collection->addTitle($features . " - " . $feature_value. $category_name);
                }
            } else {
                return false;
            }
        } elseif (substr($hash[0], 0, 11) == 'f7root-find') {
            if (substr($hash[0], 11)) {
                $listhash = $this->f7rootlistitems();
                $item = substr($hash[0], 12);
                if (isset($listhash[$item])) {
                    switch ($listhash[$item][0]) {
                        case 'where_text':
                            if ($this->getSettings('count_char_min')) {
                                $count_char_min = $this->getSettings('count_char_min');
                            } else {
                                $count_char_min = 0;
                            }
                            $collection->addWhere($listhash[$item][2] . '="" or ' .
                                $listhash[$item][2] . ' is null or ' .
                                'CHAR_LENGTH(`' . $listhash[$item][2] . '`)<' . $count_char_min
                            );
                            break;
                        case 'where_not_null':
                            $collection->addWhere($listhash[$item][2] . ' is not null ');
                            break;
                        case 'where_null':
                            $collection->addWhere($listhash[$item][2] . ' is  null ');
                            break;
                        case 'where_more':
                            $collection->addWhere($listhash[$item][2] . ' > ' . $listhash[$item][3]);
                            break;
                        case 'where_equal':
                            $collection->addWhere($listhash[$item][2] . ' = ' . $listhash[$item][3]);
                            break;
                        case 'join':
                            $joinwhere='';
                            if ($listhash[$item][3]<>'' and $listhash[$item][4]<>'' ){
                                $joinwhere=':table.' . $listhash[$item][3] . ' = ' . $listhash[$item][4];
                            }
                            $collection->addJoin($listhash[$item][2],
                                ':table.product_id = p.id', $joinwhere );
                            break;
                        default:
                            return false;
                    }
                    if ($params['auto_title']) {
                        $collection->addTitle($listhash[$item][1]);
                    }
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } elseif (substr($hash[0], 0, 17) == 'f7root-searchtext'){
            if (substr($hash[0], 17)) {
                $items = explode("-", substr($hash[0], 17));
              //  waLog::dump($items);
                $category_name="";
                if (isset($items[1])) {
                    if ($items[1] <> 0) {
                        $cm = new shopCategoryModel();
                        $category = $cm->getById($items[1]);
                        $category_name = " - " . _wp('Category') . ": " . $category['name'];
                        if ($this->getSettings('include_subcategory')) {
                            $all_subcategory = $cm->getTree($items[1]);
                            $all_subcategory_ids = implode(',', array_keys($all_subcategory));

                            $where_category = ' IN (' . $all_subcategory_ids . ")";
                        } else {
                            $where_category = ' = ' . (int)$items[1];
                        }
                        $alias = ':table.product_id = ' . $collection->addJoin('shop_category_products', null, ':table.category_id ' . $where_category) . '.product_id';
                    }
                }
                $collection->addWhere('p.name  LIKE "%'.$items[0].'%" ');

                if ($params['auto_title']) {
                    $collection->addTitle($items[0] . $category_name);
                }

            }else{
                return false;
            }
        }
        return true;
    }
}
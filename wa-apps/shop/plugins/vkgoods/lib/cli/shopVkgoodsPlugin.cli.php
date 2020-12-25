<?php 

class shopVkgoodsPluginCli extends waCliController
{

    public function execute()
    {
        waLog::log("Обновление запущено", 'shop/plugins/vkgoods/vkgoods.update.log');
        $info = array(
            'del_vkitem' => 0,
            'del_db' => 0,
            'actual' => 0,
            'get_vkitem' => 0,
            'del_db' => 0,
            'edit' => 0,
            'done' => 0,
        	'null_count' => 0,
            'unexp' => 0
        );
        $model = new shopVkgoodsPluginProductModel();
        $vk_prods = $model->getAll();
        $vkgoods = wa()->getPlugin('vkgoods');
        $data['settings'] = $vkgoods -> getSettings();
        $vk_session = new shopVkgoodsPluginVkapi($data['settings']['vk_user_id'], $data['settings']['token']);
        
        if(count($vk_prods) == 0){
            waLog::log("У вас пока не опубликовано ни одного товара.", 'shop/plugins/vkgoods/vkgoods.update.log');
        }else{
        	foreach($vk_prods as $vk_prod){
        		$update = new shopVkgoodsPluginPublish($data);
        		$result = $update->goUpdate($vk_prod, $vk_session);
        		switch ($result['code']) {
        			case 'del_vkitem':
        				$info['del_vkitem'] += 1;
        				break;
        			case 'del_db':
        				$info['del_db'] += 1;
        				break;
        			case 'actual':
        				$info['actual'] += 1;
        				break;
        			case 'get_vkitem':
        				$info['get_vkitem'] += 1;
        				break;
        			case 'edit':
        				$info['edit'] += 1;
        				break;
        			case 'done':
        				$info['done'] += 1;
        				break;
        			case 'null_count':
        				$info['null_count'] += 1;
        				break;
        			default:
        				$info['unexp'] += 1;
        				break;
        		}
        	
        		unset($update);
        	}
        	$str_info = "Обновление закончено";
        	
        	if($info['done'] > 0){
        		$str_info .= ", Обновлено публикаций: ".$info['done'];
        	}
        	
        	if($info['actual'] > 0){
        		$str_info .= ", Не требуется обновления публикаций: ".$info['actual'];
        	}
        	
        	if($info['del_db'] > 0){
        		$str_info .= ", Не найдено публикаций: ".$info['del_db']." (информация в БД актуализирована)";
        	}
        	
        	if($info['null_count'] > 0){
        		$str_info .= ", Удалено публикаций с нулевыми остатками: ".$info['null_count'];
        	}        	
        	
        	if($info['del_vkitem'] > 0){
        		$str_info .= ", Ошибок удаления публикаций: ".$info['del_vkitem'];
        	}
        	
        	if($info['get_vkitem'] > 0){
        		$str_info .= ", Ошибок получения информации о публикации: ".$info['get_vkitem'];
        	}
        	
        	if($info['edit'] > 0){
        		$str_info .= ", Ошибок при обновлении публикаций: ".$info['edit'];
        	}
        	
        	if($info['unexp'] > 0){
        		$str_info .= ", Неизвестных ошибок: ".$info['unexp'];
        	}
        	
        	waLog::log($str_info, 'shop/plugins/vkgoods/vkgoods.update.log');
        	$data['settings']['last_sync'] = date("Y-m-d H:i:s");
        	$vkgoods->saveSettings($data['settings']);        	
        }


        if(isset($data['settings']['auto_public']) && $data['settings']['auto_public'] == 1){
            waLog::log('Начало автоматической публикации', 'shop/plugins/vkgoods/vkgoods.update.log');

            $model_wait_product = new shopVkgoodsPluginWaitproductModel();
            $wait_prds = $model_wait_product->getAll();
            if($wait_prds){
                waLog::log('Отложенные публикации', 'shop/plugins/vkgoods/vkgoods.update.log');
                $this->publishNewProds($wait_prds, 'Отложенные публикации');
                $model_wait_product -> query('TRUNCATE TABLE  shop_vkgoods_wait_product');
            }else{
                waLog::log('Отложенные публикации отсутствуют', 'shop/plugins/vkgoods/vkgoods.update.log');
            }

            $model_wait_category = new shopVkgoodsPluginWaitcategoryModel();
            $wait_category_products = array();
            if($publish_cats = $model_wait_category->getWaitCategories()){
                foreach($publish_cats as $category_id => $publics){
                    if($new_products = $model_wait_category->getNewProductByCategory($category_id, array_keys($publics))){
                        $wait_category_products = array_merge($wait_category_products, $new_products);
                    }
                }
            }

            if($wait_category_products){
                waLog::log('Новые товары в категориях', 'shop/plugins/vkgoods/vkgoods.update.log');
                $this->publishNewProds($wait_category_products, 'Новые товары в категориях');
            }else{
                waLog::log('В выбранных для автоматической публикации категориях новые товары отсутствуют', 'shop/plugins/vkgoods/vkgoods.update.log');
            }
        }
        unset($vk_session);
        return;        
    }

    protected function publishNewProds($wait_prds, $str_info_add ){

        $str_info_add = 'Автоматическая публикация ('.$str_info_add.') завершена';

        $info_add = array(
            'full' => 0,
            'aid_only' => 0,
            'actual' => 0,
            'hidden' => 0,
            'noimg' => 0,
            'delete' => 0,
            'check_vkpid' => 0,
            'img_upload' => 0,
            'public_prd' => 0,
            'add_to_album' => 0,
            'null_count' => 0,
            'unexp' => 0
        );

        $vkgoods = wa()->getPlugin('vkgoods');
        $settings = $vkgoods -> getSettings();
        $vk_session = new shopVkgoodsPluginVkapi($settings['vk_user_id'], $settings['token']);

        foreach($wait_prds as $wait_prd){
/*
            $data['storefront'] = $wait_prd['storefront'];
            $data['gid'] = $wait_prd['gid'];
            $data['aid'] = $wait_prd['aid'];
            $data['category_id'] = $wait_prd['category_id'];
            $data['desc'] = $wait_prd['desc'];
            $data['all_photo'] = $wait_prd['all_photo'];
            $data['f_price'] = $wait_prd['f_price'];
            $data['pid'] = $wait_prd['pid'];
*/
            $wait_prd['settings'] = $settings;
            $export = new shopVkgoodsPluginPublish($wait_prd);
            $result = $export -> goAdd($vk_session);

            switch ($result['code']) {
                case 'full':
                    $info_add['full'] += 1;
                    break;
                case 'aid_only':
                    $info_add['aid_only'] += 1;
                    break;
                case 'actual':
                    $info_add['actual'] += 1;
                    break;
                case 'hidden':
                    $info_add['hidden'] += 1;
                    break;
                case 'noimg':
                    $info_add['noimg'] += 1;
                    break;
                case 'delete':
                    $info_add['delete'] += 1;
                    break;
                case 'check_vkpid':
                    $info_add['check_vkpid'] += 1;
                    break;
                case 'img_upload':
                    $info_add['img_upload'] += 1;
                    break;
                case 'public_prd':
                    $info_add['public_prd'] += 1;
                    break;
                case 'add_to_album':
                    $info_add['add_to_album'] += 1;
                    break;
                case 'null_count':
                    $info_add['null_count'] += 1;
                    break;
                default:
                    $info_add['unexp'] += 1;
                    break;
            }

            unset($export);
        }

        if($info_add['full'] > 0){
            $str_info_add .= ", Полностью экспортировано товаров: ".$info_add['full'];
        }
        if($info_add['aid_only'] > 0){
            $str_info_add .= ", Актуальных товаров добавлено в подборки: ".$info_add['aid_only'];
        }
        if($info_add['actual'] > 0){
            $str_info_add .= ", Ранее размещенных товаров не требующих актуализации: ".$info_add['actual'];
        }
        if($info_add['hidden'] > 0){
            $str_info_add .= ", Товаров не подлежащих публикации: ".$info_add['hidden'];
        }
        if($info_add['null_count'] > 0){
            $str_info_add .= ", Товаров с нулевыми остатками: ".$info_add['null_count'];
        }
        if($info_add['noimg'] > 0){
            $str_info_add .= ", Товаров без изображений (не опубликованы): ".$info_add['noimg'];
        }
        if($info_add['img_upload'] > 0){
            $str_info_add .= ", Отказано в загрузке изображений (не соответствуют требованиям ВК в части размеров): ".$info_add['img_upload'];
        }
        if($info_add['delete'] > 0){
            $str_info_add .= ", Ошибок при удалении товаров из ВК: ".$info_add['delete'];
        }
        if($info_add['public_prd'] > 0){
            $str_info_add .= ", Ошибок при публикации товаров: ".$info_add['public_prd'];
        }
        if($info_add['check_vkpid'] > 0){
            $str_info_add .= ", Ошибок при получении данных о ранее опубликованных товарах: ".$info_add['check_vkpid'];
        }
        if($info_add['add_to_album'] > 0){
            $str_info_add .= ", Ошибок при размещении товара ВК в подборке: ".$info_add['add_to_album'];
        }
        if($info_add['unexp'] > 0){
            $str_info_add .= ", Неизвестных ошибок: ".$info_add['unexp'];
        }

        waLog::log($str_info_add, 'shop/plugins/vkgoods/vkgoods.update.log');
        unset($vk_session);
    }
}
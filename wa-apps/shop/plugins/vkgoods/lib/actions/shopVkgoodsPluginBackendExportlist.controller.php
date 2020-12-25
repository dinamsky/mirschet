<?php
class shopVkgoodsPluginBackendExportlistController extends waLongActionController {
    private $collection;
    /*
     * @desc Проверка полноты и корректности входящих данных
     */
    protected function preInit() {
    	$this -> data['pid_type'] = waRequest::post('pid_type');
    	$this -> data['post_pid'] = urldecode(waRequest::post('pid'));
    	
    	if(waRequest::post('pid_type') == 'wait'){
    		$model = new shopVkgoodsPluginWaitproductModel();
			$this->data['wait_products'] = $model->getAll();
			$this->data['total_products'] = $model->countAll();
    	}
    	 
        $vkgoods = wa() -> getPlugin('vkgoods');
        $this -> data['settings'] = $vkgoods -> getSettings();
        
  		if(waRequest::post('pid_type') != 'wait'){
	        $this -> data['storefront'] = waRequest::post('storefront');
    	    $this -> data['gid'] = waRequest::post('group');
        	$this -> data['aid'] = waRequest::post('vkalbums');
	        $this -> data['aname'] = waRequest::post('title');
    	    $this -> data['category_id'] = waRequest::post('vkcat');
        	$this -> data['main_album'] = waRequest::post('main_album');
	        $this -> data['desc'] = waRequest::post('desc_prod');
    	    $this -> data['all_photo'] = waRequest::post('all_photo');
        	$this -> data['f_price'] = waRequest::post('f_price');

	        if ($this -> data['aid'] == "new") {
    	        $api_session = new shopVkgoodsPluginVkapi($this -> data['settings']['vk_user_id'], $this -> data['settings']['token'], false);
        	    $album_data = array('owner_id' => '-' . $this -> data['gid'], 'title' => $this -> data['aname'], 'main_album' => $this -> data['main_album']);
            	$aid = $api_session -> api("market.addAlbum", $album_data);
	            if (isset($aid -> error)) {
    	            $err_data = array('error' => 'Не удалось создать новую подборку. См. файл vkgoods.log');
        	        echo json_encode($err_data);
            	    return false;
	            } else {
    	            $this -> data['aid'] = $aid -> response -> market_album_id;
        	    }
	        }
  		}else{
  			$this -> data['gid'] = 'tmp';
  			$this -> data['aid'] = 'tmp';
  		}
  		
  		return true;
    }

    /*
     * @desc Сбор, подготовка данных для дальнейшей обработки
     */
    protected function init() {
    	
    	switch ($this->data['pid_type']){
			case 'hash':
				$collection = new shopProductsCollection($this -> data['post_pid']);
				$this -> data['total_products'] = $collection->count(); 
				unset($collection);
				break;
				
			case 'list':
				$collection = new shopProductsCollection('id/'.$this -> data['post_pid']);
				$this -> data['total_products'] = $collection->count();
				unset($collection);
				break;
		}
    	
        $this -> data['exp_products'] = 0;
        $this -> data['err_products'] = 0;
        $this -> data['exp_full'] = 0;
        $this -> data['exp_aid_only'] = 0;
        $this -> data['exp_actual'] = 0;
        $this -> data['timestamp'] = time();
        $this -> data['vk_debug'] = false;
        $this -> data['count'] = 0;
        $this -> data['exp_err_delete'] = 0;
        $this -> data['exp_err_hidden'] = 0;
        $this -> data['exp_err_vkpid'] = 0;
        $this -> data['exp_err_noimg'] = 0;
        $this -> data['exp_err_imgupload'] = 0;
        $this -> data['exp_err_prd'] = 0;
        $this -> data['exp_err_album'] = 0;
        $this -> data['null_count'] = 0;
        $this -> data['vk_session'] = new shopVkgoodsPluginVkapi($this -> data['settings']['vk_user_id'], $this -> data['settings']['token']);

        if ($this->data['pid_type'] != 'wait' && strpos($this -> data['desc'], "%debug%") != false) {
            $this -> data['vk_debug'] = true;
            waLog::log('Группа: ' . $this -> data['gid'], 'shop/plugins/vkgoods/vkgoods.debug.log');
            waLog::log('Подборка: ' . $this -> data['aid'], 'shop/plugins/vkgoods/vkgoods.debug.log');
            waLog::log('Витрина: ' . $this -> data['storefront'], 'shop/plugins/vkgoods/vkgoods.debug.log');
            waLog::log('Товаров: ' . $this -> data['total_products'], 'shop/plugins/vkgoods/vkgoods.debug.log');
            waLog::log('Шаблон описания: ' . $this -> data['desc'], 'shop/plugins/vkgoods/vkgoods.debug.log');
        }

    }

    /*
     * @desc Основная обработка и экспорт данных. За одну итерацию обрабатывается один товар.
     * @desc Если в настройках указана обработка всех изображений товара - все изображения товара обрабатываются в этой итерации.
     */
    protected function step() {
		switch ($this->data['pid_type']){
			case 'hash':
				$collection = new shopProductsCollection($this -> data['post_pid']);
				break;
				
			case 'list':
				$collection = new shopProductsCollection('id/'.$this -> data['post_pid']);
				break;
				
			case 'wait':
				$wait_prd = $this->data['wait_products'][$this->data['count']];
				$this -> data['storefront'] = $wait_prd['storefront'];
				$this -> data['gid'] = $wait_prd['gid'];
				$this -> data['aid'] = $wait_prd['aid'];
				$this -> data['category_id'] = $wait_prd['category_id'];
				$this -> data['desc'] = $wait_prd['desc'];
				$this -> data['all_photo'] = $wait_prd['all_photo'];
				$this -> data['f_price'] = $wait_prd['f_price'];
				$this -> data['pid'] = $wait_prd['pid'];
				break;
		}
    	
		if($this->data['pid_type'] != 'wait'){
			$list_products = $collection -> getProducts('id', $this->data['count'], 1);
			$prd = array_keys($list_products);
			$this -> data['pid'] = $prd[0];
			unset($collection);
		}
        

        $export = new shopVkgoodsPluginPublish($this -> data);
        $result = $export -> goAdd($this -> data['vk_session']);
        unset($export);
		
		if ($result ['status'] == 'error') {
			switch ($result ['code']) {
				case 'delete' :
					$this->data ['exp_err_delete'] += 1;
					break;
				case 'hidden' :
					$this->data ['exp_err_hidden'] += 1;
					break;
				case 'check_vkpid' :
					$this->data ['exp_err_vkpid'] += 1;
					break;
				case 'noimg' :
					$this->data ['exp_err_noimg'] += 1;
					break;
				case 'img_upload' :
					$this->data ['exp_err_imgupload'] += 1;
					break;
				case 'public_prd' :
					$this->data ['exp_err_prd'] += 1;
					break;
				case 'add_to_album' :
					$this->data ['exp_err_album'] += 1;
					break;
				case 'null_count' :
					$this->data ['null_count'] += 1;
					break;
							
			}
			$this->data ['err_products'] += 1;
		} else {
			switch ($result ['code']) {
				case 'full' :
					$this->data ['exp_full'] += 1;
					break;
				case 'aid_only' :
					$this->data ['exp_aid_only'] += 1;
					break;
				case 'actual' :
					$this->data ['exp_actual'] += 1;
					break;
			}
		}
		$this->data ['exp_products'] += 1;
		$this->data ['count'] += 1;
		return !$this->isDone();

    }

    protected function info() {
        $interval = 0;
        if (!empty($this -> data['timestamp'])) {
            $interval = time() - $this -> data['timestamp'];
        }
        $response = array('time' => sprintf('%d:%02d:%02d', floor($interval / 3600), floor($interval / 60) % 60, $interval % 60), 'processId' => $this -> processId, 'progress' => 0.0, 'ready' => $this -> isDone(), 'exp_prd' => $this -> data['exp_products'], 'err_prd' => $this -> data['err_products'], 'exp_full' => $this -> data['exp_full'], 'exp_aid' => $this -> data['exp_aid_only'], 'exp_actual' => $this -> data['exp_actual'], 'err_delete' => $this -> data['exp_err_delete'], 'err_hidden' => $this -> data['exp_err_hidden'], 'err_vkpid' => $this -> data['exp_err_vkpid'], 'err_noimg' => $this -> data['exp_err_noimg'], 'err_imgupload' => $this -> data['exp_err_imgupload'], 'exp_err_prd' => $this -> data['exp_err_prd'], 'null_count' => $this -> data['null_count'], 'err_album' => $this -> data['exp_err_album'], 'link_url' => "//vk.com/market-" . $this -> data['gid'] . "?section=album_" . $this -> data['aid'], );
        $response['progress'] = ($this -> data['exp_products'] / $this -> data['total_products']) * 100;
        $response['progress'] = sprintf('%0.3f%%', $response['progress']);

        echo json_encode($response);
    }

    protected function finish($filename) {

        $this -> info();
        return true;

    }

    protected function isDone() {
    	if($this->data['pid_type'] == 'wait' && $this -> data['exp_products'] >= $this -> data['total_products']){
    		$model = new shopVkgoodsPluginWaitproductModel();
    		$model -> query('TRUNCATE TABLE  shop_vkgoods_wait_product');
    	}
        return $this -> data['exp_products'] >= $this -> data['total_products'];
    }
    
}

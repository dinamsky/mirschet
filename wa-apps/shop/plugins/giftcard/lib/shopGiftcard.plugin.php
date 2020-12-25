<?php
class shopGiftcardPlugin extends shopPlugin
{
	// Плагин посвящается безымянному герою из команды webasyst, 
	// который самоотверженно разбирает наш код и метко выявляет косяки, а потом пишет развернутые комментарии о них.
	// WA-PRO, 2015
	
	// Включен ли плагин
	public function is_on()
	{
		if($this->getSettings('engage_plugin')) {return 1;}
		else {return 0;}
	}

	////////////////////////////////////////////////////////////////////////////////////
	// Карты
	////////////////////////////////////////////////////////////////////////////////////
	
	public function get_card_id()
	{
		$card_id = intval($this->getSettings('giftcardid'));
		if($card_id == 0) {return null;}
		return $card_id;
	}
	
	public function get_card_skus()
	{
		$card_id = $this->get_card_id();
		if(!$card_id) {return null;}
		
		$card_product = new shopProduct($card_id);
		if(!$card_product) {return null;}
		
		$card_product_skus = $card_product->skus;
		foreach($card_product_skus as $key => $sku)
		{
			if(strlen($sku['name']) == 0) {$card_product_skus[$key]['name'] = $card_product['name'];}
		}
		return $card_product_skus;
	}
	
	public function check_card_sku($post_sku_id)
	{
		$sku_id = intval($post_sku_id);
		if($sku_id == 0) {return null;}
		
		$card_skus = $this->get_card_skus();
		if(!$card_skus) {return null;}
		
		if(isset($card_skus[$sku_id])) {return $card_skus[$sku_id];}
		else {return null;}	
	}
	
	////////////////////////////////////////////////////////////////////////////////////
	// Генерация купонов
	////////////////////////////////////////////////////////////////////////////////////
	
	private function generate_coupon_code()
    {
        $alphabet = "QWERTYUIOPASDFGHJKLZXCVBNM1234567890";
        $result = '';
        while(strlen($result) < 21) {
            $result .= $alphabet{mt_rand(0, strlen($alphabet)-1)};
        }
        return $result;
    }
	
	private function generate_safe_coupon_code()
	{
		$continue_generating = true;
		$safe_code = '';
		$coupm = new shopCouponModel();
		while($continue_generating)
		{
			$current_code = $this->generate_coupon_code();
			if(!$coupm->getByField('code', $current_code))
			{
				$safe_code = $current_code;
				$continue_generating = false;
			}
		}
		return $safe_code;
	}
	
	private function create_coupon($order_id, $coupon_cost, $coupon_type = null, $coupon_code = '', $coupon_used = null)
	{
		if(!$coupon_type) {$coupon_type = 'RUB';}
		if(strlen($coupon_code) == 0) {$coupon_code = $this->generate_safe_coupon_code();}
		if(!$coupon_used) {$coupon_used = 0;}
		
		$order_prefix = wa()->getSetting('order_format', '#100{$order.id}', 'shop');
		$order_literal = str_replace('{$order.id}', $order_id, $order_prefix);
		
		$db = new waModel();
		$coupon_data = array(
								'limit' => 1,
								'value' => floatval($coupon_cost),
								'type' => $coupon_type,
								'code' => $coupon_code,
								'used' => $coupon_used,
								'create_contact_id' => wa()->getUser()->getId(),
								'create_datetime' => date('Y-m-d H:i:s'),
								'comment' => 'Автоматически сгенерированный купон за заказ '.$db->escape($order_literal),
							);
		$coupm = new shopCouponModel();
		$new_coupon_id = $coupm->insert($coupon_data);
		
		$db->query("INSERT INTO shop_giftcard_coupons (`order_id`, `coupon_id`) VALUES (".intval($order_id).", ".intval($new_coupon_id).")");
	}
	
	////////////////////////////////////////////////////////////////////////////////////
	// Купоны и заказы
	////////////////////////////////////////////////////////////////////////////////////
	
	private function get_order_currency($order_id)
	{
		$order_model = new shopOrderModel();
		$order = $order_model->getOrder($order_id);
		if($order) {return $order['currency'];}
		else {return 'RUB';}
	}
	
	private function get_coupons_by_order($order_id, $target = null)
	{
		$db = new waModel();
		$target_condition = '';
		if($target !== null) {$target_condition = " AND GC.coupon_id=".intval($target);}
		// ВАЖНО: В приоритете уже использованные сертификаты, чтобы при редактировании заказа плагин оставлял их, если повезет и позволят обстоятельства
		$coupons = $db->query("SELECT GC.coupon_id AS coupon, SC.value AS price, SC.used AS used, SC.code AS code, SC.type AS type FROM 
								shop_giftcard_coupons AS GC 
								LEFT JOIN shop_coupon AS SC
								ON GC.coupon_id = SC.id
								WHERE GC.order_id=".intval($order_id).$target_condition."
								ORDER BY SC.used DESC")->fetchAll();
		if(count($coupons) == 0) {return array();}
		return $coupons;
	}
	
	private function get_giftcards_by_order($order_id)
	{
		$items_model = new shopOrderItemsModel();
		$order_items = $items_model->getItems($order_id);
		$card_id = $this->get_card_id();
		$order_currency = $this->get_order_currency($order_id);
		
		$giftcards = array();
		
		foreach($order_items as $key=>$value)
		{
			if($card_id == $value['product_id'])
			{
				for($i = 0; $i < $value['quantity']; $i++)
				{
					// Округляем цену стандартными средствами webasyst, чтобы не было купонов, например, на 12.12412457 доллара из-за курсов валют
					// Слушаем и повинуемся))) Извините, что долго залипали на, казалось бы, простом моменте.
					$frontend_price = round(shop_currency($value['price'], null, null, false), 2);
					// Это на память
					// $currency_class = new waCurrency();
					// $frontend_price = str_replace(',', '.', $currency_class->format('%', $value['price'], null));
					// $frontend_price = str_replace(',', '.', trim(preg_replace("([^0-9.,])", "", shop_currency($value['price'])), '.'));
					array_push($giftcards, array('item'=>$value['id'], 'price' => $frontend_price));
				}
			}
		}
		
		return $giftcards;
	}
	
	private function get_coupons_diff($order_id)
	{
		$coupons = $this->get_coupons_by_order($order_id);
		$giftcards = $this->get_giftcards_by_order($order_id);
		
		$coupons_diff = array();
		$giftcards_diff = array();
		
		$order_currency = $this->get_order_currency($order_id);
		
		// Поиск подарочных карт, для которых не сгенерированы купоны
		if(count($giftcards))
		{
			$coupons_tmp = $coupons;
			foreach($giftcards as $ukey=>$giftcard)
			{
				$this_giftcard_found = 0;
				if(count($coupons_tmp))
				{
					foreach($coupons_tmp as $lkey=>$coupon)
					{
						if($coupon['price'] == $giftcard['price'])
						{
							$this_giftcard_found = 1;
							unset($coupons_tmp[$lkey]);
							break;
						}
					}
				}
				if($this_giftcard_found == 0) {array_push($giftcards_diff, array('item' => $giftcard['item'], 'price' => $giftcard['price'], 'currency' => $order_currency));}
			}
		}
		
		// Поиск купонов, для которых нет соответствующей подарочной карты
		if(count($coupons))
		{
			$giftcards_tmp = $giftcards;
			foreach($coupons as $ukey=>$coupon)
			{
				$this_coupon_found = 0;
				if(count($giftcards_tmp))
				{
					foreach($giftcards_tmp as $lkey=>$giftcard)
					{
						if($giftcard['price'] == $coupon['price'])
						{
							$this_coupon_found = 1;
							unset($giftcards_tmp[$lkey]);
							break;
						}
					}
				}
				if($this_coupon_found == 0) {array_push($coupons_diff, array('coupon'=>$coupon['coupon'], 'price' => $coupon['price']));}
			}
		}
		
		return array('giftcards' => $giftcards_diff, 'coupons' => $coupons_diff);
	}
	
	private function generate_order_coupons($order_id)
	{
		$diff = $this->get_coupons_diff($order_id);
		
		if(count($diff['giftcards']))
		{
			foreach($diff['giftcards'] as $key=>$giftcard)
			{
				$this->try_restore_coupon($order_id, $giftcard['price'], $giftcard['currency']);
			}
		}
		
		if(count($diff['coupons']))
		{
			$db = new waModel();
			$coupm = new shopCouponModel();
			foreach($diff['coupons'] as $key=>$coupon)
			{
				$this->update_coupon_configuration($order_id, $coupon['coupon']);
				$coupm->delete($coupon['coupon']);
				$db->query("DELETE FROM shop_giftcard_coupons WHERE coupon_id=".intval($coupon['coupon']));
			}
		}
	}
	
	private function try_restore_coupon($order_id, $price, $type)
	{
		$db = new waModel();
		$coupon = $db->query("SELECT * FROM shop_giftcard_save_state WHERE order_id=".intval($order_id)." AND coupon_value=".str_replace(',', '.', floatval($price))." AND coupon_type='".$db->escape($type)."'")->fetchAll();
		if(count($coupon) == 0)
		{
			$this->create_coupon($order_id, $price, $type);
		}
		else
		{ 
			$coupon = $coupon[0];
			// Проверка, не сгенерировался ли купон с таким же кодом, пока заказ был удален или отменен
			$coupm = new shopCouponModel();
			if(!$coupm->getByField('code', $coupon['coupon_code']))
			{
				$this->create_coupon($order_id, $price, $type, $coupon['coupon_code'], $coupon['coupon_used']);
				$db->query("DELETE FROM shop_giftcard_save_state WHERE id=".intval($coupon['id']));
			}
			else
			{
				$this->create_coupon($order_id, $price, $type);
			}
		}
	}
	
	private function delete_coupons_by_order($order_id)
	{
		$this->save_coupon_configuration($order_id);
		$coupons = $this->get_coupons_by_order($order_id);
		if($coupons)
		{
			$coupm = new shopCouponModel();
			foreach($coupons as $key=>$id)
			{
				$coupm->delete($id);
			}
		}
		$db = new waModel();
		$db->query("DELETE FROM shop_giftcard_coupons WHERE order_id=".intval($order_id));
	}
	
	private function save_coupon_configuration($order_id)
	{
		$db = new waModel();
		// Удаляем предыдущую конфигурацию, если она была сохранена
		$db->query("DELETE FROM shop_giftcard_save_state WHERE order_id=".intval($order_id));
		
		// Сохраняем новую конфигурацию
		$coupons = $this->get_coupons_by_order($order_id);
		if(count($coupons))
		{
			foreach($coupons as $key=>$coupon)
			{
				// Проверка, не был ли купон удален администратором
				if($coupon['code'])
				{
					$db->query("INSERT INTO shop_giftcard_save_state 
								(`order_id`, `coupon_code`, `coupon_used`, `coupon_value`, `coupon_type`) 
								VALUES (".intval($order_id).", '".$db->escape($coupon['code'])."', ".intval($coupon['used']).", ".str_replace(',', '.', floatval($coupon['price'])).", '".$db->escape($coupon['type'])."')");
				}
			}
		}
	}
	
	private function update_coupon_configuration($order_id, $coupon_id)
	{
		$db = new waModel();
		$coupon_data = $this->get_coupons_by_order($order_id, $coupon_id);
		if(count($coupon_data))
		{
			$coupon = $coupon_data[0];
			if($coupon['code'])
			{
				$db->query("INSERT INTO shop_giftcard_save_state 
								(`order_id`, `coupon_code`, `coupon_used`, `coupon_value`, `coupon_type`) 
								VALUES (".intval($order_id).", '".$db->escape($coupon['code'])."', ".intval($coupon['used']).", ".str_replace(',', '.', floatval($coupon['price'])).", '".$db->escape($coupon['type'])."')");
			}
		}
	}
	
	public function get_frontend_coupon($coupon_id, $brute_protection)
	{
		$coupm = new shopCouponModel();
		$coupon = $coupm->getByField('id', $coupon_id);
		//echo $brute_protection."<br>";
		//echo md5($coupon['id'].floatval($coupon['value']).$coupon['code'])."<br>";
		//echo $coupon['id']."|".$coupon['value']."|".$coupon['code']."<br>";
		if(!$coupon) {return null;}
		if($brute_protection != md5($coupon['id'].floatval($coupon['value']).$coupon['code'])) {return null;}
		return $coupon;
	}
	
	////////////////////////////////////////////////////////////////////////////////////
	// Хуки
	////////////////////////////////////////////////////////////////////////////////////
	
	public function order_paid($order_data) {if($this->is_on()) {$this->generate_order_coupons($order_data['order_id']);}}
	public function order_complete($order_data) {if($this->is_on()) {$this->generate_order_coupons($order_data['order_id']);}}
	public function order_edit($order_data) 
	{
		if($this->is_on()) 
		{
			// Стопудово найдется кто-то, кто умудрится редактировать возвращенные или удаленные заказы
			if($order_data['before_state_id'] != 'refunded' && $order_data['before_state_id'] != 'deleted')
			{
				$this->generate_order_coupons($order_data['order_id']);
			}
		}
	}
	public function order_delete($order_data) {if($this->is_on()) {$this->delete_coupons_by_order($order_data['order_id']);}}
	public function order_refund($order_data){if($this->is_on()) {$generated_coupons = $this->delete_coupons_by_order($order_data['order_id']);}}
	
	public function backend_order($order_data)
	{
		if(!$this->is_on()) {return array();}
		
		$order_id = $order_data['id'];
		$view = wa()->getView();
		//$view->assign('gc_diff', $this->get_coupons_diff($order_id));
		$view->assign('gc_coupons', $this->get_coupons_by_order($order_id));
		
		return array('info_section' => $view->fetch(wa()->getAppPath(null, 'shop').'/plugins/giftcard/templates/backend_order.html'));
	}
	
	public function frontend_my_order($params)
	{
		if(!isset($params['id'])) {return '';}
		$order_id = intval($params['id']);
		if(!$this->is_on()) {return '';}
		
		$view = wa()->getView();
		$coupons = $this->get_coupons_by_order($order_id);
		foreach($coupons as $key=>$coupon)
		{
			if(!$coupon['code']) {unset($coupons[$key]);}
			else
			{
				$coupons[$key]['bruteforce_protection'] = md5($coupon['coupon'].floatval($coupon['price']).$coupon['code']);
			}
		}
		$view->assign('gc_coupons', $coupons);
		$view->assign('gc_shop_url', wa()->getRouteUrl('shop/frontend'));
		
		return $view->fetch($this->get_templates_path().'frontend_order.html');
	}
	
	////////////////////////////////////////////////////////////////////////////////////
	// Сервис и настройки
	////////////////////////////////////////////////////////////////////////////////////
	
	public function are_templates_copied()
	{
		$copy_path = dirname(__FILE__).'/../../../../../wa-data/protected/shop/plugins/giftcard/user_defined_templates/';
		if(is_dir($copy_path)) {return 1;}
		else {return 0;}
	}
	
	public function copy_templates()
	{
		$repo_path = dirname(__FILE__).'/../templates/user_defined_templates/';
		$copy_path = dirname(__FILE__).'/../../../../../wa-data/protected/shop/plugins/giftcard/user_defined_templates/';
		waFiles::copy($repo_path, $copy_path); 
		
		if(is_dir($copy_path)){return 1;}
		else {return 0;}
	}
	
	public function reset_templates()
	{
		if($this->are_templates_copied())
		{
			$tpl_base_dir = dirname(__FILE__).'/../../../../../wa-data/protected/shop/plugins/giftcard/user_defined_templates/';
			waFiles::delete($theme_base_dir);
			return 1;
		}
		return 0;
	}
	
	public function get_repo_path()
	{
		return dirname(__FILE__).'/../templates/user_defined_templates/';
	}
	
	public function get_templates_path()
	{
		if($this->are_templates_copied()) {return dirname(__FILE__).'/../../../../../wa-data/protected/shop/plugins/giftcard/user_defined_templates/';}
		else {return dirname(__FILE__).'/../templates/user_defined_templates/';}
	}
	
	public function save_template($tpl_name, $tpl_content)
	{
		if(!$this->are_templates_copied())
		{
			$copy_success = $this->copy_templates();
			if(!$copy_success) {return 'Невозможно создать папку для хранения шаблонов. Проверьте права на запись в папку /wa-data/protected/shop/plugins/';}
		}
		
		if(file_put_contents($this->get_templates_path().$tpl_name.'.html', $tpl_content) !== false) 
		{
			return true;
		}
		return 'Невозможно сохранить шаблон. Проверьте права на запись в папку /wa-data/protected/shop/plugins/';
	}
	
	public function reset_template($tpl_name)
	{
		$original_file = file_get_contents($this->get_repo_path().$tpl_name.'.html');
		if(!$this->are_templates_copied())
		{
			$copy_success = $this->copy_templates();
			if(!$copy_success) {return 'Невозможно создать папку для хранения шаблонов. Проверьте права на запись в папку /wa-data/protected/shop/plugins/';}
		}
		if(file_put_contents($this->get_templates_path().$tpl_name.'.html', $original_file) !== false) 
		{
			return true;
		}
		return 'Невозможно сохранить шаблон. Проверьте права на запись в папку /wa-data/protected/shop/plugins/';
	}
	
	public function check_the_card($product_id)
	{
		$product = new shopProduct($product_id);
		if(!$product->getData()) {return 'Товар с указанным ID не существует в магазине';}
		$product_data = $product->getData();
		if($product_data['sku_type'] == 1) {return 'Товар-карта не подходит. Убедитесь, что в настройках товара выбрана вкладка "Варианты покупки"';}
		return true;
	}
}
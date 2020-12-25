<?php
/**
* shopDopinfoPlugin
*
* @desc 
* Вывод дополнительной информации в бекэнде заказа
*
* @author мастерская BNP <support@byloneprosto.ru>
* @version 3.2
*/

class shopDopinfoPlugin extends shopPlugin
{    
    private $contact_id;
    private $contact_email;
    private $contact_phone;
    private $order_id;
    private $black_group;
    protected $plugin_id = array('shop', 'dopinfo');


    /**
    * getInfo
    * Получаем и выводим дополнительную информацию:	
    *
    * @param array $params
    * @return string
    */	
    public function getInfo($params)
    {
        $client_email = $params['contact']['email'];
	$order_id = $params['id'];
        $client_phone = $params['contact']['phone'];
        $app_settings_model = new waAppSettingsModel();        
        $settings = $app_settings_model->get($this->plugin_id);
        
        if(!isset($settings['state']) || $settings['state'] == 0){
            return;
        }        
        
        $view = wa()->getView();
        
        //Инициализируем свойства
        $this->contact_id = $params['contact_id'];
        $this->contact_email = $params['contact']['email'];
        $this->black_group = $this->getSettings('black_list');
        $this->contact_phone = $params['contact']['phone'];
        $this->order_id = $params['id'];
        
        $view->assign('contact_id', $this->contact_id);    
        
        
        //Вывод заказов по статусам для клиента
        if(isset($settings['state_info']) && $settings['state_info'] == 1){
            $state_info = $this->getOrdersInfo();
        }
        
        if(isset($state_info) && !empty($state_info)){
            $view->assign('state_info', $state_info);
        }
        
        //Прверка регистрации
        $is_reg = $this->isReg();
        
        if(isset($is_reg) && !empty($is_reg)){
            $view->assign('is_reg', $is_reg);
        }
        
        //Вывод информации по группам клиента
        if(isset($settings['client_group']) && $settings['client_group'] == 1){
            $categories = $this->getContactGroup();
        }
        
        if(isset($categories) && !empty($categories)){
            $view->assign('categories', $categories);
        }
        
        //Вывод заказов по email
        if(isset($this->contact_email) && !empty($this->contact_email) && (isset($settings['email_check']) && $settings['email_check'] == 1)){
            $view->assign('email', $this->contact_email);
            
            //Задана ли проверка по черному списку
            if(isset($settings['black_list_state']) && $settings['black_list_state'] == 1){
                $black = $this->getBlack();
            }            
            if(isset($black) && $black == 'true'){
                $view->assign('black_client', 'yes');
            }
        }
        
        //Заказы по телефону
        if(isset($this->contact_phone) && !empty($this->contact_phone) && (isset($settings['phone_check']) && $settings['phone_check'] == 1)){
            $view->assign('phone', $this->contact_phone);
            $phone_client = $this->getOrdersByPhone();
            if(isset($phone_client['error']) && $phone_client['error'] == 'Number'){
                $view->assign('phone_error', '1');                
            }else{
                $view->assign('phone_client_count', $phone_client['count']);
                if(isset($phone_client['black']) && $phone_client['black'] == 'true' && (isset($settings['black_list_state']) && $settings['black_list_state'] == 1)){
                    $view->assign('black_phone', 'yes');
                }
            }            
        }
        
        //Заказы по ip
        if(isset($settings['ip_check']) && $settings['ip_check'] == 1){ 
            $orders_by_ip = $this->getOrdersByIp();
        }
        
        if(isset($orders_by_ip) && !empty($orders_by_ip)){
            $view->assign('orders_ip_count', $orders_by_ip['count']);
            $view->assign('order_ip', $orders_by_ip['ip']);
        }
        
        $content = $view->fetch($this->path.'/templates/new_info.html');
		
	$return['action_button'] =  $content;		
	return $return;   
    }
    
    /**
     * getOrdersByIp
     * @desc
     * Вывод заказов по ip клиента
     * 
     * @return array [ip - ip для заказа, count - количество заказов с похожим ip]
     */
    public function getOrdersByIp()
    {
        $model = new shopOrderParamsModel();
        
        $order_ip = $model->select('value')
                          ->where('name = "ip" AND order_id = i:0', $this->order_id)
                          ->fetchField();
        
        if(!isset($order_ip) || empty($order_ip)){
            return FALSE;
        }                
        
        $ip_exp = explode('.', $order_ip);
        $ips = $ip_exp[0].'.'.$ip_exp[1].'.'.$ip_exp[2];
        
        
        
        $orders_by_ip = $model->select('order_id')
                              ->where('name = "ip" AND value LIKE "'.$model->escape($ips).'%"')        			  					
                              ->fetchAll();
        
        return array('ip' => $order_ip, 'count' => count($orders_by_ip)); 
        
    }
    
    /**
     * getBlack
     * @desc
     * Проверка на черный список
     * 
     * @return boolean
     */
    public function getBlack()
    {
    	$contact_email_model = new waContactEmailsModel();
        
        
        $sql = "SELECT t1.contact_id, t2.category_id 
                FROM wa_contact_emails AS t1
                JOIN wa_contact_categories AS t2
                ON t1.email = s:0 
                AND t2.contact_id = t1.contact_id";
        
        $tt = $contact_email_model->query($sql, $this->contact_email)->fetchAll('category_id', true);
        
        $black = array_key_exists($this->black_group, $tt);
        
	return $black;
    }     
        
    
    /**
     * getOrdersInfo
     * @desc
     * Информация о статусах и количестве заказов клиента
     * 
     * @return string $state_info
     * 
     */
    public function getOrdersInfo()
    {
        $order_model = new shopOrderModel();
        $workflow = new shopWorkflow();
        
        $orders = $order_model->select('id, state_id')
                              ->where('contact_id = '.(int)$this->contact_id)
                              ->fetchAll('id', true);
        
        $state_info = '';
        
        foreach(array_count_values($orders) as $val => $key){
            $state_info .= $workflow->getStateById($val)->getName();
            $state_info .= ' - <a href="javascript:void(0)" class="order_state_id" data-state-id="'.$val.'" title="Кликните чтобы открыть список">';
            $state_info .= $key.'</a>; ';             
        }
        
        return $state_info;
    }
    
    /**
     * isReg
     * @desc
     * Информация о регистрации пользователя
     * 
     * @return string
     */
    public function isReg()
    {
        $contact_model = new waContactModel();
        
        $pass = $contact_model->select('password, create_contact_id')
                              ->where('id = "i:0"', $this->contact_id)
                              ->fetchAll();
        if(!empty($pass[0]['password'])){
            return 'Пользователь зарегистрирован';
        }else{
            if($pass[0]['create_contact_id']){
                $cr_name = $contact_model->select('name')
                                         ->where('id = "i:0"', $pass[0]['create_contact_id'])
                                         ->fetchField();
                return 'Пользователя внес '.$cr_name;
            }else{
                return 'Пользователь НЕ регистрировался';
            }
        }
                     
    }
    
    /**
     * getContactGroup
     * @desc
     * Группы клиента
     * 
     * @return string|boolean
     */
    public function getContactGroup()
    {
        $ccsm = new waContactCategoriesModel();
        $contact_categories = $ccsm->getContactCategories($this->contact_id);
        $black_group_id_setting = $this->getSettings('black_list');        
        
        if(isset($contact_categories) && !empty($contact_categories)){        
            $categories = '';            
            foreach($contact_categories as $val){
                if($val['id'] == $black_group_id_setting){
                    $categories .= '<span class="black_group">'.$val['name'].'</span>; ';
                }else{
                    $categories .= $val['name'].'; ';
                }                
            }
            return $categories;
        }  
        
        return FALSE;    
    }
    
    /**
     * getOrdersByPhone
     * @desc
     * Заказы по телефону
     * 
     * @return array [count - количество заказов, black - есть ли клиенты из черного списка]
     */
    public function getOrdersByPhone()
    {
        $m = new waModel();
        $dq = preg_replace("/[^\d]+/", '', $this->contact_phone); 
        
        $phone_number = $this->getSettings('phone_number');
        
        $ph = substr($dq, -$phone_number);
        
        if(!$ph){
            return array('error' => 'Number');
        }
        
        $sql = "SELECT c.id, t2.category_id 
                FROM wa_contact AS c
                JOIN wa_contact_data AS d
                JOIN wa_contact_categories AS t2 
                ON d.contact_id=c.id AND d.field='phone'
                WHERE d.value LIKE '%".$m->escape($ph, 'like')."%'
                AND t2.contact_id = c.id";
                   
        $tt = $m->query($sql)->fetchAll('id', true);
        
        $bool = in_array($this->black_group, $tt);        
        
        return array('count' => count($tt), 'black' => $bool);              
        
    }   
    
}

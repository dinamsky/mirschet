<?php
/**
 * shopDopinfoPluginBackendActions
 * @desc
 * Различные экшены для бекенда 
 */

class shopDopinfoPluginBackendActions extends waJsonActions
{
    /**
     * viewStateOrderAction
     * @desc
     * Вывод информации по статусам заказов для клиента      
     */
    public function viewStateOrderAction()
    {
        $state_id = waRequest::post('state_id', '');
        $contact_id = waRequest::post('contact_id', '', 'int');
        
        if(empty($state_id)){
            $this->response['error'] = 'Статус заказов не найден';
            return;
        }
        
        if(empty($contact_id)){
            $this->response['error'] = 'Клиент не найден';
            return;
        }
        
        
        if((isset($contact_id) && !empty($contact_id)) && (isset($state_id) && !empty($state_id))){
            $workflow = new shopWorkflow();
            
            $state_id = strip_tags($state_id);
            
            $orders_array = $this->parsingOrders($contact_id, $state_id);
            
            if(!$orders_array){
                $this->response['error'] = 'Нет заказов';
            }
            
            $this->response['orders'] = $orders_array['array'];
            $this->response['orders_count'] = $orders_array['count'];
            $this->response['state_name'] = $workflow->getStateById($state_id)->getName();
            $this->response['state_summ'] = $orders_array['state_summ'];            
        }else{
            $this->response['error'] = 'Что-то пошло не так';
        }     
         
    }
    
    public function viewPhoneOrderAction()
    {
        $phone = waRequest::post('phone', '');
        
        if(empty($phone)){
            $this->response['error'] = 'Телефон не указан';
            return;
        }
        
        $phone_number = wa('shop')->getPlugin('dopinfo')->getSettings('phone_number');
        
        $contact_id = waRequest::post('contact_id', '', 'int');
        
        if(empty($contact_id)){
            $contact_id = FALSE;
        }
        
        $dq = preg_replace("/[^\d]+/", '', $phone);
        $ph = substr($dq, -$phone_number);
        
        if(!$ph){
            $this->response['error'] = 'Что-то с телефоном';
            return;
        }
        
        $contact_model = new waContactDataModel();
        
        $ids = $contact_model->select('contact_id, value')
                             ->where('field = "phone" AND value LIKE "%'.$contact_model->escape($ph).'%"')
                             ->fetchAll();
        
        if(!isset($ids) || empty($ids)){
            $this->response['error'] = 'Покупатели не найдены';
            return;
        }
        
        $this->parsingIds($ids, $contact_id);
        
    }


    public function viewEmailOrderAction()
    {
        $email = waRequest::post('email', '');
        
        if(empty($email)){
            $this->response['error'] = 'Email не указан';
            return;
        }
        
        $contact_email_model = new waContactEmailsModel();
        
        $ids = $contact_email_model->select('contact_id')
                                   ->where('email = "'.$contact_email_model->escape($email).'"')
                                   ->fetchAll();
        
        if(!isset($ids) || empty($ids)){
            $this->response['error'] = 'Покупатели не найдены';
            return;
        }
        
        $this->parsingIds($ids);        
       
    }
    
    public function viewIpOrderAction()
    {
        $order_ip = waRequest::post('order_ip', '');
        
        if(empty($order_ip)){
            $this->response['error'] = 'IP не указан';
            return;
        }
        
        $ip_exp = explode('.', $order_ip);
        $ips = $ip_exp[0].'.'.$ip_exp[1].'.'.$ip_exp[2];        
        
        $model = new shopOrderParamsModel();        
        
        
        $sql = 'SELECT *
                FROM shop_order_params AS t1
                JOIN shop_order AS t2
                ON t1.name = "ip" 
                AND t1.value LIKE "l:0%"
                AND t2.id = t1.order_id';        
        
        
        $ids = $model->query($sql, $ips)->fetchAll();
        
        $orders_array = $this->parsingOrders('1', FALSE, $ids);
        $this->response['orders'] = $orders_array['array'];
        $this->response['orders_count'] = $orders_array['count'];
        $this->response['ips'] = $ips;
        $this->response['state_summ'] = $orders_array['state_summ'];
        
    }
    
    public function parsingOrders($contact_id, $state_id = FALSE, $orders = FALSE)
    {
        $order_model = new shopOrderModel();
        $workflow = new shopWorkflow();
        $states = $workflow->getAllStates();
        
        if(!$orders){
            if($state_id){
                $orders = $order_model->getByField(array('contact_id' => $contact_id, 'state_id' => $state_id), true);
            }else{
                $orders = $order_model->getByField('contact_id', $contact_id, true);
            }  
        }
        
        if(!isset($orders) || empty($orders)){
            return FALSE;
        }
        
        $orders_array = array();
        $state_summ = 0.0;
        foreach($orders as $val => $key){
            $order = $order_model->getOrder($key['id']);
            $summ = $key['total'] * $key['rate'];
            $shipping_address = shopHelper::getOrderAddress($order['params'], 'shipping');
            if($state_id){
                $state = FALSE;
                $icon = FALSE;
                $style = FALSE;
            }else{
                $state = $workflow->getStateById($key['state_id'])->getName();
                $icon = $states[$key['state_id']]->getOption('icon');
                $style = $states[$key['state_id']]->getStyle();
            }
            if(isset($order['params']['ip'])){
                $ip = $order['params']['ip'];
            }else{
                $ip = 'Не задан';
            }
            if(isset($order['params']['shipping_name'])){
                $shipping = $order['params']['shipping_name'];
            }else{
                $shipping = 'Не указано';
            }  
            
            $orders_array[$key['id']] = array('date' => $key['create_datetime'],
                                              'id' => $key['id'],
                                              'summ' => shop_currency($summ, true),
                                              'currency' => $key['currency'],
                                              'addres' => $shipping_address,
                                              'ip' => $ip,
                                              'state_id' => $state,
                                              'shipping' => $shipping,
                                              'icon' => $icon,
                                              'style' => $style,
                                             );
            $state_summ += $summ;
        }
        
        return array('array' => $orders_array, 'state_summ' => shop_currency($state_summ, true), 'count' => count($orders));         
        
    }
    
    
    public function parsingIds($ids, $contact_id = FALSE)
    {
        $contact_model = new waContactModel();
        $orders_array = array();
        foreach($ids as $val){
            if(isset($contact_id) && ($val['contact_id'] == $contact_id)){
                continue;
            }            
            $contact = strip_tags($contact_model->getName($val['contact_id']));
            $orders_array[$val['contact_id']] = $this->parsingOrders($val['contact_id']);                            
            $orders_array[$val['contact_id']]['name'] = $contact;
            $orders_array[$val['contact_id']]['black'] = $this->boolBlack($val['contact_id']);
            if(isset($val['value'])){
                $orders_array[$val['contact_id']]['phone'] = strip_tags($val['value']);
            }
        }
        
        $this->response['ids'] = $orders_array;
        
    }
    
    public function boolBlack($contact_id)
    {
        $black_group = wa('shop')->getPlugin('dopinfo')->getSettings('black_list');
        
        $contact_categories_model = new waContactCategoriesModel();
        
        $cat = $contact_categories_model->select('category_id, contact_id')
                                        ->where('contact_id = i:0', $contact_id)
                                        ->fetchAll('category_id', true);
        
        return array_key_exists($black_group, $cat); 
    }
}
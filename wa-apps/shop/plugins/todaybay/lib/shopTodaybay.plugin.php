<?php
/**
* shopTodaybayPlugin
*
* @desc 
* Вывод купленных сегодня товаров
*
* @author мастерская BNP <support@byloneprosto.ru>
* @version 1.3
*/

class shopTodaybayPlugin extends shopPlugin
{
    /**
     * useHook
     * 
     * @desc 
     * Если в настройках включено "использовать хук",
     * выводим в стандартном месте 
     * 
     * @return html template
     */
    public function useHook()
    {        
        if($this->getSettings('usehook') != 'hook'){
            return;
        }
        
        $mode = $this->getSettings('output');
        
        return $this->getOutput($mode);        
    }
    
    /**
     * getShoppingList
     * 
     * @desc
     * Получаем список товаров для вывода
     * 
     * @return array
     */
    public function getShoppingList()
    {        
        //Если плагин выключен - ничего не возвращаем        
        if($this->getSettings('state') == 0){
            return;
        }
        
        //Получаем список купленных товаров за текущий день
        $order_model = new shopOrderModel();
        $current_date = date("Y-m-d"); 
        
        $todays_orders = $order_model->select("id")
                                     ->where("create_datetime LIKE '".$order_model->escape($current_date)."%'")
                                     ->fetchALL();
        
        $orders_items_model = new shopOrderItemsModel();
        $order_items = array();
        
        foreach($todays_orders as $key){
            $pre_order = $orders_items_model->getItems($key['id']);
            
            foreach ($pre_order as $param){
                //Проверяем наличие повторных позиций
                $bool = in_array($param['product_id'], $order_items);               
                if(!$bool){
                    $order_items[] = $param['product_id'];
                }                
            }            
        } 
        
        //Проверяем что делать, если список товаров пуст
        $nothing = $this->getSettings('nothingshow');
        
        if((count($order_items) == 0) && ($nothing == 'nothing')){
            return NULL;           
        }  
        
        //Получаем коллекцию товаров
        $ret = $this->getCollection($order_items);            
        
        return $ret;        
    }
    
    /**  
     * getCollection
     * 
     * @desc
     * Получаем коллекцию товаров для вывода
     * 
     * @param array $items
     * @return array
     */
    public function getCollection($items)
    {
        //Проверяем что делать, если позиций меньше чем заданно в настройках
        $dowhat = $this->getSettings('dowhat');
        
        //Сколько позиций выводить
        $count = $this->getSettings('count');
        
        $ids = '';        
        foreach($items as $key){
            $ids .= $key.',';
        }
        
        $collection = new shopProductsCollection('id/'.$ids);
        $products = $collection->getProducts('*', 0, $count); 
        
        if((count($items) < $count) && ($dowhat == 'nothing') && (count($items) != 0)){
            return $products;
        } 
        
        if(count($products) < $count){
            //Если выбранных продуктов меньше чем указано в настройках
            //добавляем продукты из указанного списка
            $howmuch = $count - count($products);
            $list = $this->getSettings('list');
            $dop_collection = new shopProductsCollection('set/'.$list);
            $dop_products = $dop_collection->getProducts('*', 0, $howmuch);
            $result = array_merge($products, $dop_products);
        }else{
            $result = $products;
        } 
        
        return $result;
               
    }
        
    /**
     * getOutput
     * 
     * @desc
     * Вывод плагина
     * 
     * @param string $mode
     *  
     * @return string
     */
    public function getOutput($mode)
    {
        $list = $this->getShoppingList();
        
        if(empty($list)){
            return;
        }
        
        $view = wa()->getView();
        
        if($mode == 'list'){            
            $path = 'plugins/todaybay/templates/todaybaylist.html';                                           
        }elseif($mode == 'template'){
            $path = 'plugins/todaybay/templates/Todaybay.html';                       
        }  
        
        $template_path = wa()->getDataPath($path, false, 'shop', false);
        
        if(!file_exists($template_path)){
            $template_path = wa()->getAppPath($path, 'shop');            
        }
        
        $view->assign('list', $list);
        $content = $view->fetch($template_path); 
        
        return $content;  
                
    }
        
    
    /**
     * getProductCollection
     * 
     * @desc
     * Хелпер {shopTodaybayPlugin::getProductCollection()}
     * Возвращает коллецию товаров для подстановки
     * 
     * @return array
     */
    public static function getProductCollection()
    {
        $collection = wa()->getPlugin('todaybay')->getShoppingList();
        return $collection;
    }
    
    /**
     * getProductList
     * 
     * @desc
     * Хелпер {shopTodaybayPlugin::getProductList()}
     * Возвращает вывод в виде шаблона
     * 
     * @return type
     */
    public static function getProductList()
    {
        $mode = wa('shop')->getPlugin('todaybay')->getSettings('output');
        
        $ret = wa('shop')->getPlugin('todaybay')->getOutput($mode);
        
        return $ret;        
    }
}
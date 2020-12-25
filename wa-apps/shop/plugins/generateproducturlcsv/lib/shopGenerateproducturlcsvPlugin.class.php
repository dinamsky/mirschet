<?php

class shopGenerateproducturlcsvPlugin extends shopPlugin {
    
    function productSave($params)
    {
        if((int)$this->getSettings('generate_product') == 0) {
            return;
        }
        
        if(waRequest::get('module') != 'csv') {
            return;
        }
        
        $product_id = (int)$params['data']['id'];
        $model = new shopProductModel();
        
        if(isset($params['data']['edit_datetime']) && (int)$this->getSettings('generate_product') == 1) {
            return;
        }
        
        if((int)$params['data']['url'] != $product_id) {
            return;
        }
        
        $data = array(
            'url' => shopHelper::transliterate($params['data']['name'])
        );
        
        $model->updateById($product_id, $data);
    }
    
}
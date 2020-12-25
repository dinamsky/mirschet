<?php
class shopVkgoodsPluginBackendExportproductController extends waJsonController
{
    public function execute(){

        $vkgoods = wa()->getPlugin('vkgoods');
        $data = array(
            'pid' => waRequest::post('pid'),
            'storefront' => waRequest::post('storefront'),
            'gid' => waRequest::post('group'),
            'aid' => waRequest::post('vkalbums'),
            'aname' => waRequest::post('title'),
            'settings' => $vkgoods -> getSettings(),
            'desc' => waRequest::post('desc_prod'),
            'all_photo' => waRequest::post('all_photo'),
            'f_price' => waRequest::post('f_price'),
            'main_album' => waRequest::post('main_album'),
            'category_id' => waRequest::post('vkcat'),
            'vk_debug' => false,
        );
        
        if($data['aid'] == 'new'){
            $vk_session = new shopVkgoodsPluginVkapi($data['settings']['vk_user_id'], $data['settings']['token']);
            $response = $vk_session->api("market.addAlbum", array('owner_id' => "-".$data['gid'], 'title' => $data['aname'], 'main_album' => $data['main_album']));
            unset($vk_session);
            
            if(!isset($response->error) & isset($response->response->market_album_id)){
                $data['aid'] = $response->response->market_album_id;
            }else{
                $result = array('check' => 'error', 'description' => 'Не удалось создать новую подборку. Подробности см. в файле vkgoods.log');
                $this->errors = array('message' => 'Ошибка создания подборки', 'data' => array('owner_id' => $data['gid'], 'title' => $data['aname'], 'main_album' => $data['main_album']));
                
                return $this->errors;
            }
        }
        
        $export = new shopVkgoodsPluginPublish($data);
        $result = $export->goAdd();
       
        unset($export);
        
        switch ($result['code']) {
            case 'delete':
                $event_detail = 'Ошибка при попытке удалить товар из сообщества ВК. см. файл vkgoods.log';
                break;
            case 'hidden':
                $event_detail = 'Товар не опубликован в ВК т.к. он или удален или скрыт с сайта';
                break;
            case 'check_vkpid':
                $event_detail = 'Ошибка при попытке получения данных о товаре. см. файл vkgoods.log';
                break;
            case 'noimg':
                $event_detail = 'Товар не опубликован в ВК т.к. у него отсуствуют изображения';
                break;
            case 'img_upload':
                $event_detail = 'Ошибка при загрузке изображений на сервер ВК. Подробнее см. файл vkgoods.log';
                break;
            case 'actual':
                $event_detail = 'Актуальная информация о товаре уже опубликована, обновление не требуется';
                break;
            case 'public_prd':
                $event_detail = 'Ошибка при публикации товара в сообществе ВК. Подробнее см. файл vkgoods.log';
                break;
            case 'add_to_album':
                $event_detail = 'Ошибка при попытке разместить товар в подборке. Подробнее см. файл vkgoods.log';
                break;
            case 'aid_only':
                $event_detail = 'Информация о товаре публиковалась ранее. Товар помещен в выбранную подборку';
                break;
            case 'full':
                $event_detail = 'Товар успешно опубликован в ВК';
                break;
            default:
                $event_detail = 'Непредвиденная ошибка. Подробнее см. файл vkgoods.log';
                break;
        }

        if($result['status'] == 'error'){
            $this->errors = array(
                'message' => 'В процессе публикации возникли ошибки',
                'data' => $event_detail
            );
        }else{
            $this->response = array(
                'message' => 'Публикация завершена успешно',
                'data' => $event_detail
            );
        }
        
    }
} 
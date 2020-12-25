<?php

class shopWtspPluginSettingsSaveController extends waJsonController
{
    public function execute()
    {
        $id = (int) waRequest::post('id');
        $name = waRequest::post('name');
        $description = waRequest::post('description');
        if( strlen($name) < 3 OR strlen($description) < 3 ){
            $this->errors['data'] = "Заполните все поля формы";
            return;
        }

        $data = array(
            'name' => $name,
            'description' => $description,
        );

        $model = new shopWtspPluginModel();

        if($id){
            $model->updateById($id, $data);
        } else {
            $model->insert($data);
        }
    }

}

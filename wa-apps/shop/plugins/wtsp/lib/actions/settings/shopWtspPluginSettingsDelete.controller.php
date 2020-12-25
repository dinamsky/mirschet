<?php

class shopWtspPluginSettingsDeleteController extends waJsonController
{
    public function execute()
    {
        $id = (int) waRequest::request('id');

        if($id){
            $model = new shopWtspPluginModel();
            $model->deleteById($id);
        }
    }

}

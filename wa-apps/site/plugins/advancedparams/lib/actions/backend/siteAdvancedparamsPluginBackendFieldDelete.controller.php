<?php

class siteAdvancedparamsPluginBackendFieldDeleteController extends waJsonController {
    
    public function execute() {
        if(waRequest::method()=='post') {
            // Принимаем данные
            $id = waRequest::post('id', 0, waRequest::TYPE_INT);
            $force = waRequest::post('force', 0, waRequest::TYPE_INT);
            $field_model = new siteAdvancedparamsFieldModel();
            $field = $field_model->getFieldById($id);
            // Если поле существует
            if(!empty($field)) {
                // Если у поля установлены доп параметры экшена
                if(intval($field['count_values']) > 0) {
                    // Запрашиваем подтверждение удаления
                    if(empty($force)) {
                        $this->response = array(
                            'confirm' => 'Внимание! У параметров связанных с данным полем имеются установленые значения на витрине. Удалить само поле и все установленные значения параметров на витрине?'
                        );
                        return;
                    } else {
                        // Удаляем установленные доп. параметры на витрине
                        $params_model = new siteAdvancedparamsParamsModel($field['action']);
                        $params_model->deleteByName($field['name']);
                        $delete_field_flag = true;
                    }
                } else {
                    $delete_field_flag = true;
                }
                if($delete_field_flag) {
                    // Удаляем дополнительно сохраненные реальные значения параметров
                    $param_value_model = new siteAdvancedparamsParamValueModel($field['action']);
                    $param_value_model->deleteValuesByName($field['name']);
                    // Удаляем добавленные варианты значения полей параметра
                    $field_values_model = new siteAdvancedparamsFieldValuesModel();
                    $field_values_model->deleteByFieldId($field['id']);
                    // Удаляем файлы параметра и значения из базы
                    $param_file_model = new siteAdvancedparamsParamFileModel($field['action']);
                    $param_file_model->deleteByName($field['name']);
                    $field_model->deleteById($field['id']);
                    $this->response = 'ok';
                }
            } else {
                $this->errors[] = 'Передан неверный идентификатор поля!';
            }
        } else {
            $this->errors[] = 'Неверный запрос!';
        }
    }
}
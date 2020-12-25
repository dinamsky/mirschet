<?php

class siteAdvancedparamsPluginBackendFieldChangeTypeController extends waJsonController {
    
    public function execute() {
        if(waRequest::method()=='post') {
            $data = waRequest::post();
            $field_model = new siteAdvancedparamsFieldModel();
            $field = $field_model->getById(intval($data['id']));
            $field_types = siteAdvancedparamsPlugin::getConfigParam('field_types');
            // Проверяем существование поля и типа поля
            if($field && isset($data['type']) && array_key_exists($data['type'], $field_types)) {
               $params_model = new siteAdvancedparamsParamsModel($field['action']);
               $field_values_model = new siteAdvancedparamsFieldValuesModel();
               // Проверяем типы полей старый и новый
               $data_selectable =  siteAdvancedparamsPlugin::isSelectableType($data['type']);
               $field_selectable =  siteAdvancedparamsPlugin::isSelectableType($field['type']);
               // Если тип изменен на выбираемый формируем возможные значения из реальный значений параметров
               if($data_selectable && !$field_selectable) {
                   // Получаем возможные значения из найденных в доп параметрах
                   $values = $params_model->getParamValues($field['name']);
                   $data_value = array(
                       'field_id'=> $field['id'],
                       'default' => 1,
                       'value' => ''
                   );
                   // Записываем возможные значения поля
                   $default = false;
                   foreach ($values as $v) {
                       $data_value['value'] = $v;
                       $field_values_model->insert($data_value);
                       // Первому значению ставим флаг по умолчанию, остальным 0
                       if(!$default) {
                           $data_value['default'] = 0;
                           $default = true;
                       }
                   }
                // Если тип изменен на статичный просто удаляем все значения
               } elseif(!$data_selectable && $field_selectable) {
                   $field_values_model->deleteByField('field_id', $field['id']);
               }
               // Меняем тип поля
               $field_model->setFieldType($field['id'], $data['type']);
               // Отдаем новые данные поля
               $this->response = $field_model->getFieldById($field['id']);
            } else {
               $this->errors[] = 'Такого поля не существует';
            }
        } else {
            $this->errors[] = 'Неправильный запрос!';
        }
    }
}
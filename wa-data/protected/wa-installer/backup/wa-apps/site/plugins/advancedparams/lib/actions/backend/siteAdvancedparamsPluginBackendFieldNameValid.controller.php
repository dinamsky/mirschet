<?php

class siteAdvancedparamsPluginBackendFieldNameValidController extends waJsonController {
    
    public function execute() {
        if(waRequest::method()=='post') {
            $data = waRequest::post();
            $data['name'] = trim($data['name']);
            if(empty($data['name'])) {
                $this->errors[] = 'Ключ поля не может быть пустым!';
                return;
            }
            // Проверяем корректность символов ключа поля
            if(preg_match('~[^a-z0-9_\.]~',$data['name'])) {
                $this->errors[] = 'Для ключа поля доступны только латинские символы и символ подчеркивания без пробелов!';
                return;
            }
            // Проверяем доступно ли имя поля для создания
            if(siteAdvancedparamsPlugin::isBannedField($data['action'], $data['name'])) {
                $this->errors[] = 'Поле с таким ключом не может быть создано, оно зарезервировано системой!';
                return;
            }
            // Проверяем существование поля
            $field_model = new siteAdvancedparamsFieldModel();
            if($field_model->field_exists($data['name'], $data['action'])) {
                $this->errors[] = 'Поле с таким ключом уже есть!';
                return;
            }

            $this->response = 'ok';
        } else {
            $this->errors[] = 'Неправильный запрос!';
        }
    }
}
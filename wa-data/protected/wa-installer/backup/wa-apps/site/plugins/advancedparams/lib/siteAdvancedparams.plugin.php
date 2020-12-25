<?php

/**
 * Class siteAdvancedparamsPlugin
 */
class siteAdvancedparamsPlugin extends sitePlugin
{
    /**
     * Идентификатор приложения
     */
    const APP = 'site';
    /**
     * Идентификатор плагина
     */
    const PLUGIN_ID = 'advancedparams';
    /**
     * Файл конфига
     */
    const CONFIG_FILE = 'config.php';

    /**
     * ключ типов полей в конфиге
     */
    const CONFIG_FIELD_TYPES_KEY = 'field_types';
    /**
     * Префикс имен полей в бекенде прим: name="advancedparams[param1]"
     */
    const PARAM_FIELD_NAME = 'advancedparams_plugin';
    /**
     * Css Класс полей в бекенде
     */
    const PARAM_FIELD_CLASS = 'advancedparams_plugin-param';
    protected static $models = array();

    
    /**
     * Возвращает HTML код дополнительных полей для страницы для старых версий фреймворка
     * @param $page
     * @return array
     */
    public function backendPageEdit($page) {
        $html = '';
        if($this->getSettings('status')=='1') {
            if (isset($page['id']) && intval($page['id']) > 0) {
                $html = $this->getFields('page', $page['id']);
            }
        }
        return array(
            'settings_section'=> $html,
        );
    }

    /**
     * Возвращает HTML код дополнительных полей для страницы для новых версий фреймворка
     * @param $data
     * @return string
     */
    public function PageEdit($data) {
        if($this->getSettings('status')=='1') {
            $page_id = 0;
            if (isset($data['page']) && isset($data['page']['id'])) {
                $page_id = intval($data['page']['id']);
            }
            $html = $this->getFields('page', $page_id);
            return $html;
        }
        return '';
    }

    /**
     * Сохраняет доп. параметры страницы
     * @param $page
     */
    public function pageSave($page) {
        if($this->getSettings('status') == '1') {
            if (isset($page['page']) && intval($page['page']['id']) > 0) {
                $this->saveParams('page', $page['page']['id']);
            }
        }
    }

    /**
     * Удаляет все сохраненные данные страницы
     * @param int $page
     */
    public function pageDelete($event_params) {
        if($this->getSettings('status')=='1') {
            $this->deleteParams('page', $event_params);
        }
    }

    /**
     * Возвращает готовый к показу HTML код дополнительных полей в зависимости от типа экшена и ID
     * @param $action
     * @param $action_id
     * @return string
     */
    protected function getFields($action, $action_id = 0) {
           
            $fieldsClass = new siteAdvancedparamsPluginFields($action);
            $paramsModel = new siteAdvancedparamsParamsModel($action);
            // Получаем все доп. параметры
            $params = $paramsModel->get($action_id);
            // Получаем все поля в виде массива с HTML кодом
            $fields_array  = $fieldsClass->getFields($action_id, $params);
            $fields_html = '';
            // Объединяем все поля
            foreach ($fields_array as $v) {
                $fields_html .= $v;
            }
            // Готовим окончательный макет для вывода полей
            $html = '<div class="field-group advancedparams_plugin-field-group">
<link href="'.$this->getPluginUrlStatic().'css/siteAdvancedparamsPlugin.css" rel="stylesheet" type="text/css">
'.$this->getRedactorScripts().'
<script type="text/javascript" src="'.$this->getPluginUrlStatic().'js/siteAdvancedparamsPlugin.js"></script>
<h2 class="advancedparams_plugin-toggle"><i class="icon16 '.(!$this->getSettings('scroll')?" rarr":" darr").'"></i>Дополнительные параметры</h2>
<div class="advancedparams_plugin-fields'.(!$this->getSettings('scroll')?" advancedparams_plugin-hide":"").'">
<div class="field-group">'.$this->getActionDescription($action).'</div>';
        if(!empty($fields_html)) {
            $html .= $fields_html;
        } else {
            $html .= '<p>Добавьте необходимые поля в <a href="/'.wa()->getConfig()->getBackendUrl().'/'.siteAdvancedparamsPlugin::APP.'/#/plugins/advancedparams/">настройках плагина</a>!</p>';
        }
        $html .='<div class="field">
<a href="#" class="advancedparams_plugin-add-param"><i class="icon16 add"></i>Добавить параметр</a>
</div>
<div class="clear-both"></div>
</div>
</div>

<script type="text/javascript">
$(function () {
    $.siteAdvancedparamsPlugin.init("'.$action.'",'.(int)$action_id.');
});
</script>';
            return $html;
    }
    public function getRedactorScripts(){
        $app = wa();
        $version = $app->getVersion('webasyst');
        if(version_compare($version,'1.7.13.173','>')) {
            $control = '<link rel="stylesheet" href="'.self::getPluginUrlStatic().'css/redactor.css">';
            $control .= '<script type="text/javascript" src="'.self::getPluginUrlStatic().'js/redactor.plugin.source.js"></script>';
        } else {
            $wa_url = $app->getRootUrl();
            $lang = substr($app->getLocale(), 0, 2);

            $control = '<link rel="stylesheet" href="' . $wa_url . 'wa-content/js/redactor/redactor.css">';
            $control .= '<script src="' . $wa_url . 'wa-content/js/redactor/redactor.min.js"></script>';
            $control .= '<script src="' . $wa_url . 'wa-content/js/redactor/redactor.plugins.js"></script>';
            if ($lang != 'en') {
                $control .= '<script src="' . $wa_url . 'wa-content/js/redactor/' . $lang . '.js"></script>';
            }
        }

       return $control;
    }
    protected function getActionDescription($action) {
        $action_variables = siteAdvancedparamsPlugin::getConfigParam('action_variable');
        $action_variable = $action_variables[$action];
        return '<span class="hint">Все установленные параметры будут доступны в шаблонах как {'.$action_variable.'.<strong>key</strong>}</span>';
    }

    /**
     * Сохраняет все доп параметры экшена по ID
     * @param $action
     * @param $action_id
     */
    protected function saveParams($action, $action_id) {
            $paramsClass = new siteAdvancedparamsPluginParams($action);
            $params = waRequest::post(siteAdvancedparamsPlugin::PARAM_FIELD_NAME);
            $paramsClass->saveParams($action_id, $params);
    }

    /**
     * Удаляет все доп. параметры экшена и данные сохраненные плагином
     * @param $action
     * @param $action_id
     */
    protected function deleteParams($action, $action_data) {
        $paramsClass = new siteAdvancedparamsPluginParams($action);
        $paramsClass->deleteParams($action_data);
    }
   
    public static function getPageParams($page_id, $params = null) {
        return self::getParams('page',$page_id, $params);
    }
    protected static function getModel($action, $model_name, $param1 = false, $param2 = false) {
        $model_name = 'siteAdvancedparams'.ucfirst($model_name).'Model';
        if(!isset(self::$models[$action])) {
            self::$models[$action] = array();
        }
        if(!isset(self::$models[$action][$model_name])) {
            if(class_exists($model_name)) {
                if($param1 && !$param2) {
                    self::$models[$action][$model_name] = new $model_name($param1);
                } elseif($param1 && $param2) {
                    self::$models[$action][$model_name] = new $model_name($param1,$param2);
                } else {
                    self::$models[$action][$model_name] = new $model_name();
                }
            }
        }
        if(isset(self::$models[$action][$model_name])) {
            return self::$models[$action][$model_name];
        }
        return null;
    }
    public static function getParams($action, $action_id, $params = array()) {
        $action_id = intval($action_id);
        if(siteAdvancedparamsPlugin::actionExists($action)) {
            $params_model = new siteAdvancedparamsParamsModel($action);
            if(!empty($action_id)) {
                if(empty($params)) {
                    $params = $params_model->get($action_id);
                }
                if(!empty($params)) {
                    $fields_model = self::getModel($action,'Field');
                    foreach ($params as $k => $v) {
                        $field = $fields_model->getFieldByName($action, $k);
                        $params[$k] = new siteAdvancedparamsPluginParam($field, $v);
                    }
                }
            }
        }
        return $params;
    }
    /**
     * Возвращает URL плагина от корня домена
     * @param bool $absolute
     * @return string
     */
    public static function getPluginUrlStatic($absolute = false) {
        return wa()->getAppStaticUrl(self::APP, $absolute).'plugins/'.self::PLUGIN_ID.'/';
    }

    /**
     * Проверяет существование типа экшена
     * @param $action
     * @return bool
     */
    public static function actionExists($action) {
        $action_types = siteAdvancedparamsPlugin::getConfigParam('action_types');
        if(array_key_exists($action, $action_types)) {
            return true;
        }
        return false;
    }

    /**
     * Проверяет является ли тип поля многострочным для дублирующего сохранения исходных значений
     * @param $type
     * @return bool
     */
    public static function isPersistentType($type) {
        $field_types_persistent = siteAdvancedparamsPlugin::getConfigParam('field_types_persistent');
        if(array_key_exists($type, $field_types_persistent)) {
            return true;
        }
        return false;
    }

    /**
     * Проверяет является ли тип поля выбираемым
     * @param $type
     * @return bool
     */
    public static function isSelectableType($type) {
        $field_types_selectable = siteAdvancedparamsPlugin::getConfigParam( 'field_types_selectable');
        if(array_key_exists($type,  $field_types_selectable)) {
            return true;
        }
        return false;
    }

    /**
     * Проверяет является ли поле файловым
     * @param $type
     * @return bool
     */
    public static function isFileType($type) {
          $field_types_file = siteAdvancedparamsPlugin::getConfigParam('field_types_file');
        if(array_key_exists($type,  $field_types_file)) {
            return true;
        }
        return false;
    }

    /**
     * Пишет в лог плагина сообщение
     * @param $message
     */
    public static function log($message) {
        waLog::log($message,'site/plugins/advancedparams.log');
    }

    /**
     * Проверяет является ли имя поля зарезервированным другим функционалом
     * @param string $action
     * @param string $name
     * @return bool
     */
    public static function isBannedField($action = '', $name = '') {
        $banned_fields = siteAdvancedparamsPlugin::getConfigParam('banned_fields');
        if(!empty($name)) {
            if(isset($banned_fields[$action])) {
                if(!array_key_exists($name, $banned_fields[$action])) {
                    return false;
                }
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * Возвращает объект приложения
     * @return waSystem
     */
    public static function getConfig() {
        return wa(self::APP);
    }

    /**
     * Возвращает параметр конфигурации по ключу
     * @param null $param
     * @return array|mixed|null
     */
    public static function getConfigParam($param = null)
    {
        static $config = null;
        if (is_null($config)) {
            $app_config = self::getConfig();
            $files = array(
                $app_config->getAppPath('plugins/'.self::PLUGIN_ID, self::APP).'/lib/config/'.self::CONFIG_FILE, // defaults
                $app_config->getConfigPath(self::APP.'/plugins/'.self::PLUGIN_ID).'/'.self::CONFIG_FILE, // custom
            );
            $config = array();
            foreach ($files as $file_path) {
                if (file_exists($file_path)) {
                    $config = include($file_path);
                    if ($config && is_array($config)) {
                        foreach ($config as $name => $value) {
                            $config[$name] = $value;
                        }
                    }
                }
            }
        }
        return ($param === null) ? $config : (isset($config[$param]) ? $config[$param] : null);
    }

   
}
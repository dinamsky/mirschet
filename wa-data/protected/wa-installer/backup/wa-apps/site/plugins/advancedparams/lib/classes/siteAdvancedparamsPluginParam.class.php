<?php

class siteAdvancedparamsPluginParam {
    public $field = array();
    public $value = '';
    public function __construct($field = null, $value = null)
    {
        $this->field = $field;
        $this->value = $value;
    }
    public function getFieldTitle() {
        if(isset($this->field['title'])) {
            return $this->field['title'];
        }
        return '';

    }
    public function getValue() {
        return $this->value;
    }
    public function getFieldValues() {
        $values = array();
        if(isset($this->field['type']) && siteAdvancedparamsPlugin::isSelectableType($this->field['type']) ) {
            $values = $this->field['values'];
        }
        return $values;
    }
    public function getDefault() {
        if(isset($this->field['type']) && siteAdvancedparamsPlugin::isSelectableType($this->field['type']) && isset($this->field['default_value'])) {
            return $this->field['default_value'];
        }
        return '';
    }
}
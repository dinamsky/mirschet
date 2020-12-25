<?php
/**
 * @version 1.0.0
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 *
 * Adopted for Webasyst Framework by Serge Rodovnichenko
 */

namespace Syrnik;


class Html
{
    protected $_tags = array(
        'tag'            => '<%s%s>%s</%s>',
        'tagstart'       => '<%s%s>',
        'tagend'         => '</%s>',
        'tagselfclosing' => '<%s%s/>',
    );

    /**
     * Свойства (атрибуты, которым не нужны значения)
     *
     * @var array
     */
    protected $_htmlProperties = array(
        'compact', 'checked', 'declare', 'readonly', 'disabled', 'selected',
        'defer', 'ismap', 'nohref', 'noshade', 'nowrap', 'multiple', 'noresize',
        'autoplay', 'controls', 'loop', 'muted', 'required', 'novalidate', 'formnovalidate'
    );

    /**
     * Общий формат атрибутов. attr="value"
     *
     * @var string
     */
    protected $_htmlAttributeFormat = '%s="%s"';

    /**
     * Формат свойств. По умолчанию attr="attr".
     *
     * Например disabled="disabled" или selected="selected", как у атрибутов. Но наследники могут
     * переопределить, чтоб было просто disabled или selected
     *
     * @var string
     */
    protected $_htmlPropertyFormat = '%s="%s"';

    /**
     * Возвращает отформатированный тэг, например DIV, SPAN, P.
     *
     * @param string $name название тэга
     * @param string $text Содержимое тега. Если пусто, то будет выведен только открывающий элемент
     * @param array $options Атрибуты
     * @return string отформатированный элемент html
     */
    public function tag($name, $text = null, $options = array())
    {
        if (empty($name)) {
            return $text;
        }
        $tag = $text === null ? 'tagstart' : 'tag';

        return sprintf($this->_tags[$tag], $name, $this->_parseAttributes($options, ' ', ''), $text, $name);
    }

    public function div($class = null, $text = null, $options = array())
    {
        if (!empty($class)) {
            $options['class'] = $class;
        }
        return $this->tag('div', $text, $options);
    }

    public function p($text, $options = [])
    {
        if (is_string($options)) {
            $options = ['class' => $options];
        }
        return $this->tag('p', $text, $options);
    }

    public function section($class = null, $text = null, $options = array())
    {
        if (!empty($class)) {
            $options['class'] = $class;
        }
        return $this->tag('section', $text, $options);
    }

    /**
     * Возвращает строку с атрибутами.
     *
     * Если ключ массива (название атрибута) есть в массиве "свойств" `Html::$_htmlProperties` и значение
     * этого элемента одно из:
     *
     * - '1' (string)
     * - 1 (integer)
     * - true (boolean)
     * - 'true' (string)
     *
     * То значением атрибута станет его название. Если значение какое-либо иное, атрибут будет
     * проигнорирован.
     *
     * Если значение какого-либо отрибута `null` или `false`, то он не будет выведен
     *
     * @param array $options массив атрибутов
     * @param string $insertBefore строка, которая будет добавлена в начало возвращаемой строки
     * @param string $insertAfter строка, которая будет добавлена в конец возвращаемой строки
     * @return string строка атрибутов
     */
    protected function _parseAttributes($options, $insertBefore = ' ', $insertAfter = null)
    {
        if (!is_string($options)) {
            $options = (array)$options;
            $attributes = array();

            foreach ($options as $key => $value) {
                if ($value !== false && $value !== null) {
                    $attributes[] = $this->_formatAttribute($key, $value);
                }
            }
            $out = implode(' ', $attributes);
        } else {
            $out = $options;
        }
        return $out ? $insertBefore . $out . $insertAfter : '';
    }

    protected function _formatAttribute($key, $value)
    {
        if (is_array($value)) {
            $value = implode(' ', $value);
        }
        if (is_numeric($key)) {
            return sprintf($this->_htmlPropertyFormat, $value, $value);
        }
        $truthy = array(1, '1', true, 'true', $key);
        $isProperty = in_array($key, $this->_htmlProperties);
        if ($isProperty && in_array($value, $truthy, true)) {
            return sprintf($this->_htmlPropertyFormat, $key, $key);
        }
        if ($isProperty) {
            return '';
        }
        return sprintf($this->_htmlAttributeFormat, $key, $value);
    }
}

<?php

class shopLinkcanonicalViewHelper
{
	public static function getLink()
	{
		return shopLinkcanonicalLink::getLink(true);
	}

	/**
	 * @param array $attrs
	 * @return string
	 */
	public static function attrHTML($attrs)
	{
		$attr_text = '';
		if ($attrs !== null && is_array($attrs))
		{
			foreach ($attrs as $key => $attr)
			{
				$attr_text .= ' ' . $key . '="' . $attr . '"';
			}
		}

		return $attr_text;
	}

	/**
	 * @param string $tag
	 * @param string $text
	 * @param array|null $attrs
	 * @return string
	 */
	public static function tagHTML($tag, $text, $attrs = null)
	{
		return '<' . $tag . self::attrHTML($attrs) . '>' . $text . '</' . $tag . '>';

	}

	public static function divHTML($text, $attr = null)
	{
		return self::tagHTML('div', $text, $attr);
	}

	public static function inputHTML($attr = null)
	{
		return '<input' . self::attrHTML($attr) . '>';
	}

	public static function fieldHTML($type, $name, $value = '', $attrs = null)
	{
		if (!is_array($attrs))
		{
			$attrs = array();
		}
		$nm = self::divHTML($name, array('class' => 'name'));
		$val = '';
		switch ($type)
		{
			case waHtmlControl::INPUT:
				$input = self::inputHTML(
					array('class' => 'long input', 'name' => 'linkcanonical_field', 'value' => $value)
				);
				$val = self::divHTML($input, array('class' => 'value no-shift'));
		}
		$field = self::divHTML($nm . $val, array_merge(array('class' => 'field'), $attrs)) . '<br>';

		return $field;
	}

	/**
	 * Проверяет валидность URL
	 *
	 * @param $url
	 * @return bool
	 */
	public static function isURL($url)
	{
		return !!filter_var($url, FILTER_VALIDATE_URL);
	}
}
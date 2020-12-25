<?php

class shopCatalogreviewsRawSettingsAccess
{
	const TYPE_TEXT = 'TEXT';
	const TYPE_BOOLEAN = 'BOOLEAN';
	const TYPE_INT = 'INT';
	const TYPE_DOUBLE = 'DOUBLE';
	const TYPE_JSON = 'JSON';
	const TYPE_JSON_ARRAY = 'JSON_ARRAY';

	const DEFAULT_TYPE = self::TYPE_TEXT;

	private $settings_specification;
	private $raw_settings;

	public function __construct($settings_specification)
	{
		$this->settings_specification = $settings_specification;
		$this->raw_settings = [];
	}

	public function __get($name)
	{
		return $this->handleGetSetting($name);
	}

	public function __set($name, $value)
	{
		$this->handleSetSetting($name, $value);
	}

	public function getRawSettings()
	{
		return $this->raw_settings;
	}

	public function setRawSettings($raw_settings)
	{
		$this->raw_settings = [];

		$this->raw_settings = $raw_settings;

		//foreach ($raw_settings as $name => $value)
		//{
		//	$this->handleSetSetting($name, $value);
		//}
	}

	private function handleGetSetting($name)
	{
		if (!array_key_exists($name, $this->settings_specification))
		{
			return null;
		}

		$field_specification = $this->settings_specification[$name];

		if (!array_key_exists($name, $this->raw_settings))
		{
			return array_key_exists('default_value', $field_specification)
				? $field_specification['default_value']
				: null;
		}

		$type = array_key_exists('type', $field_specification)
			? $field_specification['type']
			: self::DEFAULT_TYPE;

		$raw_value = $this->raw_settings[$name];

		if ($type === self::TYPE_TEXT)
		{
			return strval($raw_value);
		}
		elseif ($type === self::TYPE_BOOLEAN)
		{
			return $raw_value === '1';
		}
		elseif ($type === self::TYPE_INT)
		{
			return intval($raw_value);
		}
		elseif ($type === self::TYPE_DOUBLE)
		{
			return doubleval($raw_value);
		}
		elseif ($type === self::TYPE_JSON)
		{
			return json_decode($raw_value, true);
		}
		elseif ($type === self::TYPE_JSON_ARRAY)
		{
			return json_decode($raw_value, true);
		}
		else
		{
			throw new InvalidArgumentException('Unknown type');
		}
	}

	private function handleSetSetting($name, $value)
	{
		if (!array_key_exists($name, $this->settings_specification))
		{
			return;
		}

		$field_specification = $this->settings_specification[$name];

		$type = self::DEFAULT_TYPE;

		if (array_key_exists('type', $field_specification))
		{
			$type = $field_specification['type'];
		}

		if ($type === self::TYPE_TEXT)
		{
			$raw_value = strval($value);
		}
		elseif ($type === self::TYPE_BOOLEAN)
		{
			$raw_value = $value ? '1' : '0';
		}
		elseif ($type === self::TYPE_INT)
		{
			$raw_value = intval($value);
		}
		elseif ($type === self::TYPE_DOUBLE)
		{
			$raw_value = doubleval($value);
		}
		elseif ($type === self::TYPE_JSON)
		{
			$raw_value = json_encode($value);
		}
		elseif ($type === self::TYPE_JSON_ARRAY)
		{
			$raw_value = json_encode(array_values($value));
		}
		else
		{
			throw new InvalidArgumentException('Unknown type');
		}


		//$default_value = array_key_exists('default_value', $field_specification)
		//	? $field_specification['default_value']
		//	: null;
		//
		//if ($default_value === $value)
		//{
		//	unset($this->raw_settings[$name]);
		//	return;
		//}
		//
		//if ($type === self::TYPE_BOOLEAN)
		//{
		//	$value = $value ? 1 : 0;
		//}
		//elseif ($type === self::TYPE_JSON)
		//{
		//	$value = json_encode($value);
		//}

		$this->raw_settings[$name] = $raw_value;
	}
}

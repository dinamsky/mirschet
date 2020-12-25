<?php

class shopCatalogreviewsPluginSettingsModel extends waModel implements shopCatalogreviewsPluginSettingsSource
{
	protected $table = 'shop_catalogreviews_plugin_settings';

	public function fetchSettings()
	{
		return $this->getAll('name', true);
	}

	public function storeSettings(array $raw_settings)
	{
		if (count($raw_settings) === 0)
		{
			return true;
		}

		$values_query_parts = [];
		$query_params = [];

		$value_index = 1;
		foreach ($raw_settings as $name => $raw_value)
		{
			$name_key = "name_{$value_index}";
			$value_key = "value_{$value_index}";

			$values_query_parts[] = "(:{$name_key}, :{$value_key})";
			$query_params[$name_key] = $name;
			$query_params[$value_key] = $raw_value;

			$value_index++;
		}

		$values_query = implode(',', $values_query_parts);

		$insert_sql = "
INSERT INTO `{$this->table}`
(`name`, `value`)
VALUES {$values_query}
ON DUPLICATE KEY
UPDATE `value` = VALUES(`value`)
";

		return $this->query($insert_sql, $query_params);
	}
}

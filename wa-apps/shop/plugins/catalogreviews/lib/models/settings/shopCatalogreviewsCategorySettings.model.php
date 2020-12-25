<?php

class shopCatalogreviewsCategorySettingsModel extends waModel implements shopCatalogreviewsCategorySettingsSource
{
	protected $table = 'shop_catalogreviews_category_settings';

	public function fetchSettings($category_id)
	{
		return $this->select('name,value')
			->where('category_id = :category_id', ['category_id' => $category_id])
			->fetchAll('name', true);
	}

	public function storeSettings($category_id, array $raw_settings)
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
			$category_key = "category_{$value_index}";
			$name_key = "name_{$value_index}";
			$value_key = "value_{$value_index}";

			$values_query_parts[] = "(:{$category_key}, :{$name_key}, :{$value_key})";
			$query_params[$category_key] = $category_id;
			$query_params[$name_key] = $name;
			$query_params[$value_key] = $raw_value;

			$value_index++;
		}

		$values_query = implode(',', $values_query_parts);

		$insert_sql = "
INSERT INTO `{$this->table}`
(`category_id`, `name`, `value`)
VALUES {$values_query}
ON DUPLICATE KEY
UPDATE `value` = VALUES(`value`)
";

		return $this->query($insert_sql, $query_params);
	}
}

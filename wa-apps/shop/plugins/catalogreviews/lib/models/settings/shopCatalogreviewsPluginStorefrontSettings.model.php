<?php

class shopCatalogreviewsPluginStorefrontSettingsModel extends waModel implements shopCatalogreviewsPluginStorefrontSettingsSource
{
	protected $table = 'shop_catalogreviews_plugin_storefront_settings';

	public function fetchStorefrontSettings($storefront)
	{
		return $this->select('name,value')
			->where('storefront = :storefront', ['storefront' => $storefront])
			->fetchAll('name', true);
	}

	public function storeStorefrontSettings($storefront, array $raw_settings)
	{
		if (count($raw_settings) === 0)
		{
			return true;
		}

		$storefront_key = 'storefront';

		$values_query_parts = [];
		$query_params = [
			$storefront_key => $storefront,
		];

		$value_index = 1;
		foreach ($raw_settings as $name => $raw_value)
		{
			$name_key = "name_{$value_index}";
			$value_key = "value_{$value_index}";

			$values_query_parts[] = "(:{$storefront_key}, :{$name_key}, :{$value_key})";
			$query_params[$name_key] = $name;
			$query_params[$value_key] = $raw_value;

			$value_index++;
		}

		$values_query = implode(',', $values_query_parts);

		$insert_sql = "
INSERT INTO `{$this->table}`
(`storefront`, `name`, `value`)
VALUES {$values_query}
ON DUPLICATE KEY
UPDATE `value` = VALUES(`value`)
";

		return $this->query($insert_sql, $query_params)->result();
	}

	public function deleteStorefrontSettings($storefront)
	{
		return $this->deleteByField('storefront', $storefront);
	}

	public function getStorefrontsWithPersonalSettings()
	{
		$all = $this->select('DISTINCT storefront')->fetchAll('storefront');
		unset($all[shopCatalogreviewsGeneralStorefront::NAME]);

		return array_keys($all);
	}
}

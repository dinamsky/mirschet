<?php

class shopCatalogreviewsCategoryStorefrontSettingsModel extends waModel implements shopCatalogreviewsCategoryStorefrontSettingsSource
{
	protected $table = 'shop_catalogreviews_category_storefront_settings';

	public function fetchSettings($storefront, $category_id)
	{
		return $this->select('name,value')
			->where('storefront = :storefront', ['storefront' => $storefront])
			->where('category_id = :category_id', ['category_id' => $category_id])
			->fetchAll('name', true);
	}

	public function storeSettings($storefront, $category_id, array $raw_settings)
	{
		if (count($raw_settings) === 0)
		{
			return true;
		}

		$storefront_key = 'storefront';
		$category_id_key = 'category_id';

		$values_query_parts = [];
		$query_params = [
			$storefront_key => $storefront,
			$category_id_key => $category_id,
		];

		$value_index = 1;
		foreach ($raw_settings as $name => $raw_value)
		{
			$name_key = "name_{$value_index}";
			$value_key = "value_{$value_index}";

			$values_query_parts[] = "(:{$storefront_key}, :{$category_id_key}, :{$name_key}, :{$value_key})";
			$query_params[$name_key] = $name;
			$query_params[$value_key] = $raw_value;

			$value_index++;
		}

		$values_query = implode(',', $values_query_parts);

		$insert_sql = "
INSERT INTO `{$this->table}`
(`storefront`, `category_id`, `name`, `value`)
VALUES {$values_query}
ON DUPLICATE KEY
UPDATE `value` = VALUES(`value`)
";

		return $this->query($insert_sql, $query_params);
	}

	public function getStorefrontsWithPersonalSettings($category_id)
	{
		$all = $this
			->select('DISTINCT storefront')
			->where('category_id = :id', ['id' => $category_id])
			->fetchAll('storefront');

		unset($all[shopCatalogreviewsGeneralStorefront::NAME]);

		return array_keys($all);
	}

	public function deleteSettings($storefront_to_clear, $category_id)
	{
		return $this->deleteByField([
			'storefront' => $storefront_to_clear,
			'category_id' => $category_id,
		]);
	}
}

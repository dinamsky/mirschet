<?php

class shopLastmodifiedSettingsModel extends waModel
{
	protected $table = 'shop_lastmodified_settings';

	public function getGroup($group, array $names)
	{
		return $this->getByField(array(
			'group' => $group,
			'name' => $names,
		), true);
	}

	public function update(array $rows)
	{
		foreach ($rows as $row)
		{
			$this->insert($row, 1);
		}
	}

	public function deleteAll()
	{
		return $this->query('delete from `'.$this->getTableName().'`');
	}
}
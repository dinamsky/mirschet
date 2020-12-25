<?php


class shopLastmodifiedSettings
{
	private $group_id;
	private $settings;
	
	public function getGroupId()
	{
		return $this->group_id;
	}
	
	public function setGroupId($group_id)
	{
		$this->group_id = $group_id;
	}
	
	public function getSettings()
	{
		return $this->settings;
	}
	
	public function setSettings($settings)
	{
		$this->settings = $settings;
	}
	
	public function toArray()
	{
		return $this->getSettings();
	}
}
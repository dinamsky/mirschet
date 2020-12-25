<?php


class shopLastmodifiedSettingsPlugin extends shopLastmodifiedSettings
{
	public function isEnable()
	{
		$settings = $this->getSettings();
		
		return $settings['is_enable'];
	}
	
	public function getType()
	{
		$settings = $this->getSettings();
		
		return $settings['type'];
	}
}
<?php


class shopLastmodifiedSettingsGroup extends shopLastmodifiedSettings
{
	public function getFor()
	{
		$settings = $this->getSettings();
		
		return $settings['for'];
	}
	
	public function getDate()
	{
		$settings = $this->getSettings();
		
		return $settings['date'];
	}
	
	public function getAllowAgents()
	{
		return $this->getFor() == 'off'
			? array()
			: ($this->getFor() == 'bots'
				? array('bot')
				: array('user', 'bot')
			);
	}
}
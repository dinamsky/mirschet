<?php

interface shopCatalogreviewsPluginSettingsSource
{
	public function fetchSettings();

	public function storeSettings(array $raw_settings);
}

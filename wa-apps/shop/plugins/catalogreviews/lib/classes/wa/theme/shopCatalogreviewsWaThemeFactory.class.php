<?php

class shopCatalogreviewsWaThemeFactory
{
	private $themes = [];

	/**
	 * @param waTheme $wa_theme
	 * @return shopCatalogreviewsWaTheme
	 */
	public function getTheme(waTheme $wa_theme)
	{
		$key = $wa_theme->app_id . '/' . $wa_theme->id;

		if (!array_key_exists($key, $this->themes))
		{
			$this->themes[$key] = new shopCatalogreviewsWaTheme($wa_theme);
		}

		return $this->themes[$key];
	}
}

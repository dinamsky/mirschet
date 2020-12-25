<?php

interface shopCatalogreviewsPluginEnv
{
	/** @return shopCatalogreviewsFullConfig */
	public function getConfig();

	/** @return array|null */
	public function getCategory();

	/** @return shopCatalogreviewsWaPluginRouting */
	public function getPluginRouting();

	public function getStorefront();

	public function getCurrentReviewsSortMode();

	/** @return array */
	public function getActiveRouting();
}
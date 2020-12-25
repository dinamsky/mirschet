<?php


interface shopCatalogreviewsStorefrontSource
{
	/**
	 * @return string[]
	 */
	public function getStorefronts();

	public function getCurrentStorefront();
}

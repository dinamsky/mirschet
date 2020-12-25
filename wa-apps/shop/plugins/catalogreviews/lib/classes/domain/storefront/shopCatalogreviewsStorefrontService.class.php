<?php


class shopCatalogreviewsStorefrontService
{
	private $source;

	public function __construct(shopCatalogreviewsStorefrontSource $source)
	{
		$this->source = $source;
	}

	public function getStorefronts()
	{
		return $this->source->getStorefronts();
	}

	public function getCurrentStorefront()
	{
		return $this->source->getCurrentStorefront();
	}
}

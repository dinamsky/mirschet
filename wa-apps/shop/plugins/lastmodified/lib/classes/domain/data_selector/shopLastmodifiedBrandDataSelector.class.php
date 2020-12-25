<?php


class shopLastmodifiedBrandDataSelector implements shopLastmodifiedDataSelector
{
	public function getData()
	{
		return array(
			'brand' => wa()->getView()->getVars('brand'),
			'products' => wa()->getView()->getVars('products'),
		);
	}
}
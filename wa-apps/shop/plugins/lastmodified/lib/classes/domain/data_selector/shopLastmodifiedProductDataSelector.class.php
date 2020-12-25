<?php


class shopLastmodifiedProductDataSelector implements shopLastmodifiedDataSelector
{
	public function getData()
	{
		$product = wa()->getView()->getVars('product');
		$trimmer = new shopLastmodifiedDataTrimmer();
		$product = $trimmer->trimProduct($product);
		
		return array(
			'product' => $product,
		);
	}
}
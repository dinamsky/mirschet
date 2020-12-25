<?php

class shopLinkcanonicalProductCanonicalModel extends waModel
{
	protected $table = "shop_linkcanonical_product_canonical";

	public function addCanonicals($product_id, shopLinkcanonicalItemCanonicalList $list)
	{
		$this->deleteByField('product_id', $product_id);
		$data = array();

		/** @var shopLinkcanonicalProductCanonical $item */
		foreach ($list->getList() as $item)
		{
			if ($item->hasCanonical())
			{
				$data[] = array(
					'hash' => $item->getHash(),
					'product_id' => $item->getId(),
					'storefront' => $item->getStorefront(),
					'canonical' => $item->getCanonical(),
				);
			}
		}
		if (!empty($data))
		{
			$this->multipleInsert($data);
		}
	}

	public function addCanonical(shopLinkcanonicalProductCanonical $canonical)
	{
		$this->deleteByField('product_id', $canonical->getId());
		if ($canonical->hasCanonical())
		{
			$data = array(
				'hash' => $canonical->getHash(),
				'product_id' => $canonical->getId(),
				'storefront' => $canonical->getStorefront(),
				'canonical' => $canonical->getCanonical(),
			);
			$this->insert($data);
		}
	}
}
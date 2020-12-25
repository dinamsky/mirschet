<?php

class shopLinkcanonicalCategoryCanonicalModel extends waModel
{
	protected $table = "shop_linkcanonical_category_canonical";

	public function addCanonicals($category_id, shopLinkcanonicalItemCanonicalList $list)
	{
		$this->deleteByField('category_id', $category_id);
		$data = array();

		/** @var shopLinkcanonicalCategoryCanonical $item */
		foreach ($list->getList() as $item)
		{
			if ($item->hasCanonical())
			{
				$data[] = array(
					'hash' => $item->getHash(),
					'category_id' => $item->getId(),
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

	public function addCanonical(shopLinkcanonicalCategoryCanonical $canonical)
	{
		$this->deleteByField('category_id', $canonical->getId());
		if ($canonical->hasCanonical())
		{
			$data = array(
				'hash' => $canonical->getHash(),
				'category_id' => $canonical->getId(),
				'storefront' => $canonical->getStorefront(),
				'canonical' => $canonical->getCanonical(),
			);
			$this->insert($data);
		}
	}
}
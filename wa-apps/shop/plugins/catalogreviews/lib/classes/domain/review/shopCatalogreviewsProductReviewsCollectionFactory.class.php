<?php

class shopCatalogreviewsProductReviewsCollectionFactory
{
	private $product_reviews_source;

	public function __construct(shopCatalogreviewsProductReviewsSource $product_reviews_source)
	{
		$this->product_reviews_source = $product_reviews_source;
	}

	/**
	 * @param string $storefront
	 * @return shopCatalogreviewsIProductReviewsCollection
	 */
	public function buildAllReviewsCollection($storefront = '*')
	{
		return $this->buildReviewsCollection('all', $storefront);
	}

	/**
	 * @param $category_id
	 * @param string $storefront
	 * @return shopCatalogreviewsIProductReviewsCollection
	 */
	public function buildCategoryReviewsCollection($category_id, $storefront = '*')
	{
		if (!wa_is_int($category_id) || !($category_id > 0))
		{
			throw new InvalidArgumentException("invalid category_id [{$category_id}]");
		}

		return $this->buildReviewsCollection("category/{$category_id}", $storefront);
	}

	/**
	 * @param $hash
	 * @param $storefront
	 * @return shopCatalogreviewsIProductReviewsCollection
	 */
	private function buildReviewsCollection($hash, $storefront)
	{
		$collection = new shopCatalogreviewsProductReviewsCollection(
			$hash,
			$this->product_reviews_source
		);

		return new shopCatalogreviewsProductReviewsCollectionCachedProxy($collection, $storefront);
	}
}

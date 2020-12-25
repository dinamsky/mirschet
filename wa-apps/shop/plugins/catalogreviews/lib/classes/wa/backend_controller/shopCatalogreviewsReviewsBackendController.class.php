<?php

class shopCatalogreviewsReviewsBackendController
{
	private $category_settings_storage;
	private $category_source;
	private $product_source;
	private $product_reviews_storage;
	private $product_reviews_collection_factory;
	private $settings_assoc_mapper;

	public function __construct(
		shopCatalogreviewsCategorySettingsStorage $category_settings_storage,
		shopCatalogreviewsCategorySource $category_source,
		shopCatalogreviewsProductSource $product_source,
		shopCatalogreviewsProductReviewsStorage $product_reviews_storage,
		shopCatalogreviewsProductReviewsCollectionFactory $product_reviews_collection_factory,
		shopCatalogreviewsSettingsAssocMapper $settings_assoc_mapper
	)
	{
		$this->category_settings_storage = $category_settings_storage;
		$this->category_source = $category_source;
		$this->product_source = $product_source;
		$this->product_reviews_storage = $product_reviews_storage;
		$this->product_reviews_collection_factory = $product_reviews_collection_factory;
		$this->settings_assoc_mapper = $settings_assoc_mapper;
	}

	public function getState()
	{
		$categories_by_left_key = $this->category_source->getCategoriesByLeftKey();
		$root_categories = $this->category_source->getRootCategories();

		return [
			'plugin_categories_settings' => $this->getCategoriesSettings($categories_by_left_key),

			'categories_by_left_key' => $categories_by_left_key,
			'total_reviews_count' => $this->getTotalReviewsCount(),
			'category_reviews_count' => $this->getCategoryReviewsCount($root_categories),
			'current_user' => $this->getCurrentUser(),
			'contacts_app_url' => $this->getContactsAppUrl(),
		];
	}

	public function getReviewsState($category_id, $offset, $limit)
	{
		$collection = wa_is_int($category_id) && $category_id > 0
			? $this->product_reviews_collection_factory->buildCategoryReviewsCollection($category_id)
			: $this->product_reviews_collection_factory->buildAllReviewsCollection();

		$collection->setWithReplies(true);

		$reviews = $collection->getReviews($offset, $limit);

		return [
			'reviews' => $reviews,
			'reviews_count' => $collection->count(),

			'products' => $this->getReviewsProducts($reviews),
		];
	}

	private function getCategoriesSettings($categories)
	{
		$categories_settings = [];

		foreach ($categories as $category)
		{
			$settings = $this->category_settings_storage->getSettings($category['id']);

			$categories_settings[] = [
				'category_id' => $category['id'],
				'settings' => $this->settings_assoc_mapper->settingsToAssoc($settings),
			];
		}

		return $categories_settings;
	}

	private function getReviewsProducts($reviews)
	{
		$product_ids = [];

		foreach ($reviews as $review)
		{
			$product_id = $review['product_id'];
			$product_ids[$product_id] = $product_id;
		}

		$image_size = '48x48';
		$shop_url = wa()->getAppUrl('shop');

		$products = [];
		foreach ($this->product_source->getShortProductsByIds($product_ids) as $product)
		{
			$image_url = '';
			if ($product['image_id'] > 0)
			{
				$image = [
					'id' => $product['image_id'],
					'product_id' => $product['id'],
					'ext' => $product['ext'],
					'filename' => $product['image_filename'],
				];

				$image_url = shopImage::getUrl($image, $image_size);
			}

			$edit_url = $shop_url . "?action=products#/product/{$product['id']}/";

			$products[] = [
				'id' => $product['id'],
				'name' => $product['name'],
				'image_url' => $image_url,
				'edit_url' => $edit_url,
			];
		}

		return $products;
	}

	public function unpublishReview($review_id)
	{
		if (!$this->product_reviews_storage->unpublishReview($review_id))
		{
			throw new Exception('');
		}
	}

	public function publishReview($review_id)
	{
		if (!$this->product_reviews_storage->publishReview($review_id))
		{
			throw new Exception('');
		}
	}

	public function deleteReview($review_id)
	{
		if (!$this->product_reviews_storage->deleteReview($review_id))
		{
			throw new Exception('');
		}
	}

	public function addReviewReply($contact_id, $review_id, $reply_text)
	{
		if (!$this->product_reviews_storage->addReviewReply($contact_id, $review_id, $reply_text))
		{
			throw new Exception('');
		}
	}

	public function getCategoriesReviews($category_ids)
	{
		$categories_reviews_count = [];

		foreach ($category_ids as $category_id)
		{
			$collection = $this->product_reviews_collection_factory->buildCategoryReviewsCollection($category_id);

			$categories_reviews_count[] = [
				'category_id' => $category_id,
				'reviews_count' => $collection->count(),
			];
		}

		return $categories_reviews_count;
	}

	// todo pass in construct
	private function getCurrentUser()
	{
		$user = wa()->getUser();

		return [
			'id' => $user->getId(),
			'name' => $user->getName(),
			'image' => '',
		];
	}

	private function getContactsAppUrl()
	{
		$info = wa()->getAppInfo('contacts');

		// todo проверка прав

		return is_array($info)
			? wa()->getAppUrl('contacts')
			: '';
	}

	private function getTotalReviewsCount()
	{
		return $this->product_reviews_collection_factory
			->buildAllReviewsCollection()
			->count();
	}

	private function getCategoryReviewsCount($categories)
	{
		$result = [];

		foreach ($categories as $category)
		{
			$category_id = intval($category['id']);
			$collection = $this->product_reviews_collection_factory->buildCategoryReviewsCollection($category_id);

			$result[] = [
				'category_id' => $category_id,
				'reviews_count' => intval($collection->count()),
			];
		}

		return $result;
	}
}

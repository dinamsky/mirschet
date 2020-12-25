<?php

class shopCatalogreviewsProductReviewsCollection implements shopCatalogreviewsIProductReviewsCollection
{
	private $reviews_collection_options;

	private $reviews_source;

	public function __construct(
		$hash,
		shopCatalogreviewsProductReviewsSource $reviews_source
	)
	{
		$this->reviews_collection_options = new shopCatalogreviewsProductReviewsCollectionOptions();
		$this->reviews_source = $reviews_source;

		$this->reviews_collection_options->hash = $hash;
	}

	public function setWithReplies($with_replies)
	{
		$this->reviews_collection_options->with_replies = $with_replies;
	}

	public function setIsPlainRepliesStructure($is_plain_replies_structure)
	{
		$this->reviews_collection_options->is_plain_replies_structure = $is_plain_replies_structure;
	}

	public function setPublishedOnly($published_only)
	{
		$this->reviews_collection_options->published_only = $published_only;
	}

	public function setSort($sort, $order)
	{
		$this->reviews_collection_options->sort = $sort;
		$this->reviews_collection_options->order = $order;
	}

	public function addReviewsplusPluginFields($add_reviewsplus_plugin)
	{
		$this->reviews_collection_options->add_reviewsplus_plugin = $add_reviewsplus_plugin;
	}

	public function getReviews($offset, $limit)
	{
		$reviews = $this->reviews_source->getByCollectionOptions($this->reviews_collection_options, $offset, $limit);

		$this->extendReviews($reviews);

		return $reviews;
	}

	public function count()
	{
		return $this->reviews_source->countByCollectionOptions($this->reviews_collection_options);
	}

	public function getOptions()
	{
		return [
			'hash' => $this->reviews_collection_options->hash,
			'with_replies' => $this->reviews_collection_options->with_replies,
			'is_plain_replies_structure' => $this->reviews_collection_options->is_plain_replies_structure,
			'published_only' => $this->reviews_collection_options->published_only,
			'sort' => $this->reviews_collection_options->sort,
			'order' => $this->reviews_collection_options->order,
			'add_reviewsplus_plugin' => $this->reviews_collection_options->add_reviewsplus_plugin,
		];
	}

	protected function extendReviews(&$reviews)
	{
		foreach ($reviews as &$review)
		{
			try
			{
				$contact = new waContact($review['contact_id']);
			}
			catch (waException $e)
			{
				$contact = null;
			}

			if (!$contact || !$contact->exists())
			{
				$author_name = $review['name'];
				$author_is_user = false;
			}
			else
			{
				$author_name = $contact->getName();
				$author_is_user = $contact->get('is_user');
			}

			$review['author'] = [
				'name' => $author_name,
				'is_user' => $author_is_user,
			];

			$review['product'] = new shopProduct($review['product_id']);

			if (array_key_exists('replies', $review) && is_array($review['replies']))
			{
				$this->extendReviews($review['replies']);
			}
		}
	}
}

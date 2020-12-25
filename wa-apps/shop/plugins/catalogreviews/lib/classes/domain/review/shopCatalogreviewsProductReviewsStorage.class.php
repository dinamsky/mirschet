<?php

class shopCatalogreviewsProductReviewsStorage
{
	private $product_reviews_source;

	public function __construct(shopCatalogreviewsProductReviewsSource $product_reviews_source)
	{
		$this->product_reviews_source = $product_reviews_source;
	}

	public function unpublishReview($review_id)
	{
		return $this->product_reviews_source->unpublishReview($review_id);
	}

	public function publishReview($review_id)
	{
		return $this->product_reviews_source->publishReview($review_id);
	}

	public function deleteReview($review_id)
	{
		return $this->product_reviews_source->deleteReview($review_id);
	}

	public function addReviewReply($contact_id, $review_id, $reply_text)
	{
		return $this->product_reviews_source->addReviewReply($contact_id, $review_id, $reply_text);
	}
}

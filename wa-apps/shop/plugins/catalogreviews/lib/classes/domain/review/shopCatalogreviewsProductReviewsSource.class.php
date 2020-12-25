<?php

interface shopCatalogreviewsProductReviewsSource
{
	public function getByCollectionOptions(shopCatalogreviewsProductReviewsCollectionOptions $reviews_collection_options, $offset, $limit);

	/**
	 * @param shopCatalogreviewsProductReviewsCollectionOptions $reviews_collection_options
	 * @return int
	 */
	public function countByCollectionOptions(shopCatalogreviewsProductReviewsCollectionOptions $reviews_collection_options);

	public function unpublishReview($review_id);

	public function publishReview($review_id);

	public function deleteReview($review_id);

	public function addReviewReply($contact_id, $review_id, $reply_text);
}

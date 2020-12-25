<?php

interface shopCatalogreviewsIProductReviewsCollection
{
	public function setWithReplies($with_replies);

	public function setIsPlainRepliesStructure($is_plain_replies_structure);

	public function setPublishedOnly($published_only);

	public function setSort($sort, $order);

	public function addReviewsplusPluginFields($add_reviewsplus_plugin);

	public function getReviews($offset, $limit);

	/** @return int */
	public function count();

	/** @return array */
	public function getOptions();
}
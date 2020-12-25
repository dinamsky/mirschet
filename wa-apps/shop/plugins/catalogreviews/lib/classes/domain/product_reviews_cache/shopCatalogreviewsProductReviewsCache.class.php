<?php

interface shopCatalogreviewsProductReviewsCache
{
	public function isCached();

	public function get();

	public function set($count);

	public function delete();
}
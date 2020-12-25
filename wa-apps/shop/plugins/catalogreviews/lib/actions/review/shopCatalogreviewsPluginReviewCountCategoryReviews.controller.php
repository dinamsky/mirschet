<?php

class shopCatalogreviewsPluginReviewCountCategoryReviewsController extends waJsonController
{
	public function execute()
	{
		$this->response['success'] = false;

		try
		{
			$category_ids = $this->getCategoryIds();

			$backend_context = shopCatalogreviewsContext::getBackendInstance();

			$controller = $backend_context->getReviewsController();
			$this->response['categories_reviews_count'] = $controller->getCategoriesReviews($category_ids);
		}
		catch (Exception $e)
		{
			return;
		}

		$this->response['success'] = true;
	}

	private function getCategoryIds()
	{
		$category_ids = json_decode(waRequest::post('category_ids_json'));

		if (!is_array($category_ids))
		{
			throw new InvalidArgumentException('category_ids_json');
		}

		return $category_ids;
	}
}
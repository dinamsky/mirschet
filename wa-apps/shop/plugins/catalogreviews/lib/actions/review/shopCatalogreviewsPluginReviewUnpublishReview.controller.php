<?php

class shopCatalogreviewsPluginReviewUnpublishReviewController extends waJsonController
{
	public function execute()
	{
		$this->response['success'] = false;

		try
		{
			$review_id = $this->tryGetReviewId();

			$backend_context = shopCatalogreviewsContext::getBackendInstance();

			$controller = $backend_context->getReviewsController();
			$controller->unpublishReview($review_id);
		}
		catch (Exception $e)
		{
			return;
		}

		$this->response['success'] = true;
	}

	private function tryGetReviewId()
	{
		$id = waRequest::post('review_id', 0, waRequest::TYPE_INT);

		if ($id <= 0)
		{
			throw new InvalidArgumentException('review_id');
		}

		return $id;
	}
}

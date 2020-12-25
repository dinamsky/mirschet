<?php

class shopCatalogreviewsPluginReviewGetReviewsListController extends waJsonController
{
	public function execute()
	{
		$this->response['success'] = false;

		try
		{
			list($category_id, $offset, $limit) = $this->tryGetParams();
		}
		catch (HttpInvalidParamException $e)
		{
			return;
		}

		$backend_context = shopCatalogreviewsContext::getBackendInstance();

		$reviews_controller = $backend_context->getReviewsController();

		$this->response['state'] = $reviews_controller->getReviewsState($category_id, $offset, $limit);
		$this->response['success'] = true;
	}

	private function tryGetParams()
	{
		$offset = waRequest::get('offset');
		if (!wa_is_int($offset) || $offset < 0)
		{
			throw new HttpInvalidParamException('invalid param [\'offset\'] value');
		}
		$offset = intval($offset);

		$limit = waRequest::get('limit');
		if (!wa_is_int($offset) || $limit <= 0)
		{
			throw new HttpInvalidParamException('invalid param [\'limit\'] value');
		}
		$limit = intval($limit);

		$category_id = waRequest::get('category_id', 0, waRequest::TYPE_INT);
		if ($category_id < 0)
		{
			throw new HttpInvalidParamException('invalid param [\'category_id\'] value');
		}

		return [$category_id, $offset, $limit];
	}
}

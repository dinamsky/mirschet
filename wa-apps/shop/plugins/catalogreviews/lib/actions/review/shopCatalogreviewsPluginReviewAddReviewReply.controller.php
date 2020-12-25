<?php

class shopCatalogreviewsPluginReviewAddReviewReplyController extends waJsonController
{
	public function execute()
	{
		$this->response['success'] = false;

		try
		{
			list($review_id, $reply_text) = $this->tryGetParams();
			$user = wa()->getUser();

			$backend_context = shopCatalogreviewsContext::getBackendInstance();

			$controller = $backend_context->getReviewsController();
			$controller->addReviewReply($user->getId(), $review_id, $reply_text);
		}
		catch (Exception $e)
		{
			return;
		}

		$this->response['success'] = true;
	}

	private function tryGetParams()
	{
		$id = waRequest::post('review_id', 0, waRequest::TYPE_INT);
		if ($id <= 0)
		{
			throw new InvalidArgumentException('review_id');
		}

		$reply_text = waRequest::post('reply_text', '', waRequest::TYPE_STRING_TRIM);
		if ($reply_text === '')
		{
			throw new InvalidArgumentException('reply_text');
		}

		return [$id, $reply_text];
	}
}

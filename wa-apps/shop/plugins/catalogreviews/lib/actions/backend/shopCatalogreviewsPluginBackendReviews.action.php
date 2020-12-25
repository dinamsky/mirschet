<?php

class shopCatalogreviewsPluginBackendReviewsAction extends shopCatalogreviewsBackendViewAction
{
	public function execute()
	{
		$this->setTemplate('BackendReviews');
		$this->getResponse()->setTitle('Каталог отзывов');

		$backend_context = shopCatalogreviewsContext::getBackendInstance();
		$controller = $backend_context->getReviewsController();

		$this->view->assign('state', $controller->getState());
	}
}

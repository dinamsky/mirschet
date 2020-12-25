<?php

class shopCatalogreviewsPluginCategorySettingsGetContextStateController extends waJsonController
{
	public function execute()
	{
		$this->response['success'] = false;

		try
		{
			$category_id = $this->tryGetCategoryId();
		}
		catch (Exception $e)
		{
			return;
		}

		$backend_context = shopCatalogreviewsContext::getBackendInstance();

		$category_settings_controller = $backend_context->getCategorySettingsController();

		$this->response['state'] = $category_settings_controller->getSettingsPageState($category_id, []);

		$this->response['success'] = true;
	}

	private function tryGetCategoryId()
	{
		$category_id = waRequest::get('category_id', 0, waRequest::TYPE_INT);

		if (!($category_id > 0))
		{
			throw new InvalidArgumentException('category_id');
		}

		return $category_id;
	}
}

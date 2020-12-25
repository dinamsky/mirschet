<?php

class shopCatalogreviewsPluginCategorySettingsGetCategoryStorefrontSettingsController extends waJsonController
{
	public function execute()
	{
		$this->response['success'] = false;

		try
		{
			list($category_id, $storefront) = $this->tryGetParams();
		}
		catch (Exception $e)
		{
			$this->errors[] = $e->getMessage();

			return;
		}

		$backend_context = shopCatalogreviewsContext::getBackendInstance();

		$category_settings_controller = $backend_context->getCategorySettingsController();

		$this->response['state'] = $category_settings_controller->getCategoryStorefrontSettingsState($storefront, $category_id);

		$this->response['success'] = true;
	}

	private function tryGetParams()
	{
		$category_id = waRequest::get('category_id', 0, waRequest::TYPE_INT);

		if (!($category_id > 0))
		{
			throw new InvalidArgumentException('category_id');
		}

		$storefront = waRequest::get('storefront', '', waRequest::TYPE_STRING_TRIM);
		if ($storefront === '')
		{
			throw new InvalidArgumentException('storefront');
		}

		return [$category_id, $storefront];
	}
}

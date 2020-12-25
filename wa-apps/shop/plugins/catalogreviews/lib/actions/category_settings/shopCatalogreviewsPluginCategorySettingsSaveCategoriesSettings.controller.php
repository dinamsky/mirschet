<?php

class shopCatalogreviewsPluginCategorySettingsSaveCategoriesSettingsController extends waJsonController
{
	public function execute()
	{
		$this->response['success'] = false;

		$backend_context = shopCatalogreviewsContext::getBackendInstance();

		$category_settings_controller = $backend_context->getCategorySettingsController();

		try
		{
			$categories_settings = $this->getCategoriesSettings();
		}
		catch (waException $e)
		{
			return;
		}

		$category_settings_controller->saveCategoriesSettings($categories_settings);

		$this->response['success'] = true;
	}

	private function getCategoriesSettings()
	{
		$categories_settings_assoc = json_decode(waRequest::post('categories_settings_json'), true);

		if (!is_array($categories_settings_assoc))
		{
			throw new waException();
		}

		return $categories_settings_assoc;
	}
}

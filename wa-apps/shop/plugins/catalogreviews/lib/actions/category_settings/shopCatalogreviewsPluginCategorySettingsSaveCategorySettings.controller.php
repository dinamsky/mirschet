<?php

class shopCatalogreviewsPluginCategorySettingsSaveCategorySettingsController extends waJsonController
{
	public function execute()
	{
		list($category_id, $state, $loaded_storefronts, $current_storefront) = $this->tryGetParams();

		$this->response['success'] = false;

		if (count($this->errors) > 0)
		{
			return;
		}

		if (
			$current_storefront !== shopCatalogreviewsGeneralStorefront::NAME
			&& !in_array($current_storefront, $loaded_storefronts)
		)
		{
			$loaded_storefronts[] = $current_storefront;
		}

		$backend_context = shopCatalogreviewsContext::getBackendInstance();

		$category_settings_controller = $backend_context->getCategorySettingsController();

		$category_settings_controller->saveSettings($category_id, $state, $loaded_storefronts);

		$this->response['new_state'] = $category_settings_controller->getSettingsPageState($category_id, $loaded_storefronts);

		$this->response['success'] = true;
	}

	private function tryGetParams()
	{
		$category_id = waRequest::post('category_id', 0, waRequest::TYPE_INT);
		$state_json = waRequest::post('state_json');
		$loaded_storefronts_json = waRequest::post('loaded_storefronts_json');
		$current_storefront = waRequest::post('current_storefront', '', waRequest::TYPE_STRING_TRIM);

		$state = json_decode($state_json, true);
		$loaded_storefronts = json_decode($loaded_storefronts_json, true);

		if (!($category_id > 0))
		{
			$this->errors['category_id'] = 'Invalid param [category_id]';
		}

		if (!is_array($state))
		{
			$this->errors['state'] = 'Invalid param [state]';
		}

		if (!is_array($loaded_storefronts))
		{
			$this->errors['loaded_storefronts'] = 'Invalid param [loaded_storefronts';
		}

		if (!is_string($current_storefront) || $current_storefront === '')
		{
			$this->errors['current_storefront'] = 'Invalid param [current_storefront]';
		}

		return [$category_id, $state, $loaded_storefronts, $current_storefront];
	}
}

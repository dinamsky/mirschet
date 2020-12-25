<?php

class shopCatalogreviewsPluginSettingsSaveSettingsController extends waJsonController
{
	private $state;
	private $loaded_storefronts;
	private $current_storefront;

	public function __construct()
	{
		list($state, $loaded_storefronts, $current_storefront) = $this->tryGetParams();

		$this->state = $state;
		$this->loaded_storefronts = $loaded_storefronts;
		$this->current_storefront = $current_storefront;
	}

	public function execute()
	{
		$this->response['success'] = false;

		if (count($this->errors) !== 0)
		{
			return;
		}

		$loaded_storefronts = $this->loaded_storefronts;

		$backend_context = shopCatalogreviewsContext::getBackendInstance();

		$settings_controller = $backend_context->getPluginSettingsController();

		$settings_controller->save($this->state, $loaded_storefronts);

		if (
			$this->current_storefront !== shopCatalogreviewsGeneralStorefront::NAME
			&& !in_array($this->current_storefront, $loaded_storefronts)
		)
		{
			$loaded_storefronts[] = $this->current_storefront;
		}

		$this->response['new_state'] = $settings_controller->getSettingsState($loaded_storefronts);

		$this->response['success'] = true;
	}

	private function tryGetParams()
	{
		$state_json = waRequest::post('state_json');
		$loaded_storefronts_json = waRequest::post('loaded_storefronts_json');
		$current_storefront = waRequest::post('current_storefront', '', waRequest::TYPE_STRING_TRIM);

		$state = json_decode($state_json, true);
		$loaded_storefronts = json_decode($loaded_storefronts_json, true);

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

		return [$state, $loaded_storefronts, $current_storefront];
	}
}

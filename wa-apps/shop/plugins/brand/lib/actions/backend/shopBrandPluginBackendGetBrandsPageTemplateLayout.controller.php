<?php

class shopBrandPluginBackendGetBrandsPageTemplateLayoutController extends shopBrandWaBackendJsonController
{
	public function execute()
	{
		$this->response['success'] = false;
		$storefront = waRequest::get('storefront');

		$storage = new shopBrandBrandsPageTemplateLayoutStorage();
		$this->response['template_layout'] = $storage->getMeta($storefront)->assoc();

		$this->response['success'] = true;
	}
}

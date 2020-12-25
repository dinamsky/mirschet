<?php

class shopBrandBrandsActionTemplate extends shopBrandActionTemplate
{
	protected function getPluginTemplateFileName()
	{
		return 'FrontendBrands.html';
	}

	protected function getThemeTemplateFileName()
	{
		return 'brand_plugin_frontend_brands.html';
	}

	protected function getPluginCssFileName()
	{
		return 'brands_page.css';
	}

	protected function getPluginJsFileName()
	{
		return false;
	}
}
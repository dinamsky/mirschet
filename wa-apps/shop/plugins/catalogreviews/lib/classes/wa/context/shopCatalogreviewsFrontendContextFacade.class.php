<?php

class shopCatalogreviewsFrontendContextFacade
{
	private $context;

	public function __construct(shopCatalogreviewsContext $context)
	{
		$this->context = $context;
	}

	public function getConfigStorage()
	{
		return $this->context->getConfigStorage();
	}

	public function getStorefrontService()
	{
		return $this->context->getStorefrontService();
	}

	public function getWaRouting()
	{
		return $this->context->getWaRouting();
	}

	public function getWaPluginRoutingDispatcher()
	{
		return $this->context->getWaPluginRoutingDispatcher();
	}

	public function getProductReviewsCollectionFactory()
	{
		return $this->context->getProductReviewsCollectionFactory();
	}

	public function getReviewsCatalogDataRenderer()
	{
		return $this->context->getReviewsCatalogDataRenderer();
	}

	public function getViewHelper()
	{
		return $this->context->getViewHelper();
	}

	public function getWaThemeFactory()
	{
		return $this->context->getWaThemeFactory();
	}

	public function getWaActionTemplateStorage(shopCatalogreviewsWaTheme $theme)
	{
		return $this->context->getWaActionTemplateStorage($theme);
	}

	public function getFrameworkEnv()
	{
		return $this->context->getFrameworkEnv();
	}
}

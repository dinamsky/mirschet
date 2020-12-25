<?php

class shopCatalogreviewsBackendContextFacade
{
	private $context;

	public function __construct(shopCatalogreviewsContext $context)
	{
		$this->context = $context;
	}

	public function getPluginSettingsController()
	{
		return $this->context->getPluginSettingsController();
	}

	public function getReviewsController()
	{
		return $this->context->getReviewsController();
	}

	public function getCategorySettingsController()
	{
		return $this->context->getCategorySettingsController();
	}

	public function userHasRights(waContact $contact = null)
	{
		return $this->context->userHasRights($contact);
	}
}

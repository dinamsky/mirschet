<?php

abstract class shopCatalogreviewsWaShopFrontendAction extends shopFrontendAction
{
	protected $env;
	protected $context;
	protected $action_template_storage;

	public function __construct($params = null)
	{
		parent::__construct($params);

		/** @var shopCatalogreviewsPlugin $plugin */
		$plugin = wa('shop')->getPlugin('catalogreviews');

		$this->env = $plugin->getEnv();
		$this->context = shopCatalogreviewsContext::getFrontendInstance();

		$theme = $this->context->getWaThemeFactory()->getTheme($this->getTheme());
		$this->action_template_storage = $this->context->getWaActionTemplateStorage($theme);
	}

	protected function preExecute()
	{
		parent::preExecute();

		$catalogreviews_helper = shopCatalogreviewsContext::getFrontendInstance()->getViewHelper();

		$this->view->assign('catalogreviews_helper', $catalogreviews_helper);
	}

	protected function setActionTemplate(shopCatalogreviewsWaActionTemplate $action_template)
	{
		if ($action_template->isThemeTemplate())
		{
			$this->setThemeTemplate($action_template->getActionTemplate());
		}
		else
		{
			$this->setTemplate($action_template->getActionTemplate());
		}

		$this->view->assign('sub_templates', $action_template->getSubTemplates());
	}
}

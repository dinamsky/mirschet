<?php

class shopCatalogreviewsBackendLayout extends shopBackendLayout
{
	public function display()
	{
		waSystem::popActivePlugin();

		try
		{
			parent::display();
		}
		catch (Exception $e)
		{}

		waSystem::pushActivePlugin('catalogreviews', 'shop');
	}

	protected function getTemplate()
	{
		$shop_layout = new shopBackendLayout();

		return $shop_layout->getTemplate();
	}

	protected function getPage()
	{
		$page = parent::getPage();

		return $page;
	}
}

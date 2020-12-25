<?php

class shopBrandBrandPagesTabsContentAction extends shopBrandBrandPageContentAction
{
	protected $with_pages_tabs = false;

	protected function getActionTemplate()
	{
		return new shopBrandBrandPagesTabsTemplate($this->getTheme());
	}

	protected function executeBrandPage(shopBrandFetchedLayout $fetched_layout)
	{
		$this->view->assign(array(
			'brand' => $this->action->getBrand(),
			'page' => $this->action->getPage(),
			'brand_page' => $this->action->getBrandPage(),
			'pages' => $this->action->getPages(),
		));
	}
}
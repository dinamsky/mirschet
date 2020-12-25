<?php

class shopCatalogreviewsBackendViewAction extends waViewAction
{
	protected function preExecute()
	{
		parent::preExecute();

		$layout = new shopCatalogreviewsBackendLayout();
		$layout->assign('no_level2', true);
		$this->setLayout($layout);

		$this->view->assign('asset_version', shopCatalogreviewsWaHelper::getAssetVersion());

		$this->getResponse()->addJs('wa-content/js/ace/ace.js');

		$this->getResponse()->addJs('wa-content/js/codemirror/lib/codemirror.js');
		$this->getResponse()->addCss('wa-content/js/codemirror/lib/codemirror.css');
	}
}

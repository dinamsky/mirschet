<?php

class shopSeoredirectInfoPluginCli extends shopSeoredirectPluginCli
{
	public function countAutoRedirects()
	{
		$shop_urls_model = new shopSeoredirectShopUrlsModel();
		$this->dumpc('Count items ' . $shop_urls_model->countAll());
	}

	public function countRedirects()
	{
		$redirect_model = new shopSeoredirectRedirectModel();
		$this->dumpc('Count items ' . $redirect_model->countAll());
	}

	public function countErrors()
	{
		$error_storage = new shopSeoredirectErrorStorage();
		$this->dumpc('Count items ' . $error_storage->getCount());
	}
}
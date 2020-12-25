<?php

class shopSeoredirectDeletePluginCli extends shopSeoredirectPluginCli
{
	public function autoRedirects()
	{
		$shop_urls_model = new shopSeoredirectShopUrlsModel();
		$shop_urls_model->query('TRUNCATE TABLE ' . $shop_urls_model->getTableName());
	}

	public function redirects()
	{
		$redirect_model = new shopSeoredirectRedirectModel();
		$redirect_model->query('TRUNCATE TABLE ' . $redirect_model->getTableName());
	}

	public function errors()
	{
		$error_storage = new shopSeoredirectErrorStorage();
		$error_storage->clean();
	}
}
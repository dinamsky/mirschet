<?php
try
{
	$model = new waModel();
	$model->exec("
alter table shop_seoredirect_shop_urls
	alter full_url drop default;
alter table shop_seoredirect_shop_urls
	change column full_url full_url varchar(255) not null after url;
");
	$shop_urls_model = new shopSeoredirectShopUrlsModel();
	$shop_urls_model->recalculateHash();
	
	$url_archivator = new shopSeoredirectUrlArchivator();
	$url_archivator->run();
}
catch (Exception $ignored)
{

}
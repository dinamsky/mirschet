<?php

$model = new waModel();
$sql = 'ALTER TABLE shop_seoredirect_redirect DROP COLUMN `execute`';

try
{
	$model->query($sql);
}
catch (waException $e)
{

}
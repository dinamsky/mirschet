<?php

class shopSeoredirectInstallPluginCli extends shopSeoredirectPluginCli
{
	public function execute()
	{
		$url_archivator = new shopSeoredirectUrlArchivator();
		$url_archivator->run();
	}
}
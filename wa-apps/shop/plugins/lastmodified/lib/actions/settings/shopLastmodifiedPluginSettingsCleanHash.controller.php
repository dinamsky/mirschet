<?php


class shopLastmodifiedPluginSettingsCleanHashController extends waJsonController
{
	public function execute()
	{
		$hash_model = new shopLastmodifiedHashModel();
		$hash_model->truncate();
	}
}
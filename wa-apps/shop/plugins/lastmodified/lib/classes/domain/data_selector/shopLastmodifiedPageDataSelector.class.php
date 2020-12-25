<?php


class shopLastmodifiedPageDataSelector implements shopLastmodifiedDataSelector
{
	public function getData()
	{
		$page = wa()->getView()->getVars('page');
		$trimmer = new shopLastmodifiedDataTrimmer();
		$page = $trimmer->trimPage($page);
		
		return array(
			'page' => $page,
		);
	}
}
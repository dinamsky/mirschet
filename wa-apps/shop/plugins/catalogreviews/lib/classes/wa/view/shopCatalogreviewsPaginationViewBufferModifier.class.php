<?php


class shopCatalogreviewsPaginationViewBufferModifier
{
	public function modify($page, shopCatalogreviewsViewBuffer $view_buffer)
	{
		$view_buffer->assign('page_number', $page);
	}
}

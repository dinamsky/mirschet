<?php

interface shopCatalogreviewsCategoryViewBufferModifier
{
	public function modify($storefront, $category_id, shopCatalogreviewsViewBuffer $view_buffer);
}

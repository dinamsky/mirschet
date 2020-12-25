<?php

class shopCatalogreviewsViewBufferModifiers
{
	/** @var shopCatalogreviewsCustomViewBufferModifier[] */
	private $modifiers = [];

	public function addModifier(shopCatalogreviewsCustomViewBufferModifier $modifier)
	{
		$this->modifiers[] = $modifier;
	}

	public function modify(shopCatalogreviewsViewBuffer $view_buffer)
	{
		foreach ($this->modifiers as $modifier)
		{
			$modifier->modify($view_buffer);
		}
	}
}

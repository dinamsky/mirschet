<?php

interface shopCatalogreviewsILayout
{
	/** @return array */
	public function getLayoutAssoc();

	/**
	 * @param string $field_name
	 * @return bool
	 */
	public function hasField($field_name);
}

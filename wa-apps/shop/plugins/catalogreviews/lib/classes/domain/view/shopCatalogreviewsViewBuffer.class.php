<?php

interface shopCatalogreviewsViewBuffer
{
	public function getVars();

	public function assign($name, $value = null);

	/**
	 * @param string $template
	 * @return string
	 */
	public function render($template);

	/**
	 * @param array $templates
	 * @return array
	 */
	public function renderAll($templates);
}

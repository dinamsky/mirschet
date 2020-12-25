<?php

abstract class shopCatalogreviewsWaActionTemplate
{
	protected $action_template;
	protected $sub_template_paths;

	/**
	 * @param string $action_template
	 * @param array $sub_template_paths
	 */
	public function __construct($action_template, array $sub_template_paths = [])
	{
		$this->action_template = $action_template;
		$this->sub_template_paths = $sub_template_paths;
	}

	/**
	 * @return bool
	 */
	abstract public function isThemeTemplate();

	public function getActionTemplate()
	{
		return $this->action_template;
	}

	public function getSubTemplates()
	{
		return $this->sub_template_paths;
	}
}

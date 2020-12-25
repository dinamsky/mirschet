<?php

abstract class shopCatalogreviewsConfig
{
	protected $config_params;

	public function __construct(array $config_params)
	{
		$this->config_params = $config_params;
	}

	public function __get($name)
	{
		if ($name === 'root_url_keyword')
		{
			return 'reviews';
		}

		return array_key_exists($name, $this->config_params)
			? $this->config_params[$name]
			: null;
	}
}

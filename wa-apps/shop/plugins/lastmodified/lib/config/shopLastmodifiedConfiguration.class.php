<?php


class shopLastmodifiedConfiguration
{
	private $settings_storage;
	private $request_handler_builder;
	private $settings_config = array(
		'plugin' => array(
			'is_enable' => array(
				'default' => 0
			),
			'type' => array(
				'values' => array(
					'lm',
					'lm304',
				),
				'default' => 'lm'
			),
		),
		'home' => array(
			'for' => array(
				'values' => array(
					'off',
					'bots',
					'all',
				),
				'default' => 'off',
			),
			'date' => array(
				'values' => array(
					'now',
					'prev_hour',
					'prev_day',
				),
				'default' => 'now',
			),
		),
		'category' => array(
			'for' => array(
				'values' => array(
					'off',
					'bots',
					'all',
				),
				'default' => 'off',
			),
			'date' => array(
				'values' => array(
					'actual',
					'now',
					'prev_hour',
					'prev_day',
				),
				'default' => 'actual',
			),
		),
		'product' => array(
			'for' => array(
				'values' => array(
					'off',
					'bots',
					'all',
				),
				'default' => 'off',
			),
			'date' => array(
				'values' => array(
					'actual',
					'now',
					'prev_hour',
					'prev_day',
				),
				'default' => 'actual',
			),
		),
		'page' => array(
			'for' => array(
				'values' => array(
					'off',
					'bots',
					'all',
				),
				'default' => 'off',
			),
			'date' => array(
				'values' => array(
					'actual',
					'now',
					'prev_hour',
					'prev_day',
				),
				'default' => 'actual',
			),
		),
		'brand' => array(
			'for' => array(
				'values' => array(
					'off',
					'bots',
					'all',
				),
				'default' => 'off',
			),
			'date' => array(
				'values' => array(
					'actual',
					'now',
					'prev_hour',
					'prev_day',
				),
				'default' => 'actual',
			),
		),
	);
	
	public function __construct()
	{
		$this->settings_storage = new shopLastmodifiedSettingsStorage($this->settings_config);
		$hash_storage = new shopLastmodifiedHashStorage();
		$this->request_handler_builder = new shopLastmodifiedRequestHandlerBuilder(
			$hash_storage, $this->settings_storage
		);
	}
	
	public function getRequestHandlerBuilder()
	{
		return $this->request_handler_builder;
	}
	
	public function getSettingsStorage()
	{
		return $this->settings_storage;
	}
}
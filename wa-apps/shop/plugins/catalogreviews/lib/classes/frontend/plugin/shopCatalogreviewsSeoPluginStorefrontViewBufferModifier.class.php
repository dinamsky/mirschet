<?php

class shopCatalogreviewsSeoPluginStorefrontViewBufferModifier implements shopCatalogreviewsStorefrontViewBufferModifier
{
	/** @var shopSeoStorefrontDataCollector */
	private $storefront_data_collector;

	public function __construct($storefront_data_collector)
	{
		$this->storefront_data_collector = $storefront_data_collector;
	}

	public function modify($storefront, shopCatalogreviewsViewBuffer $view_buffer)
	{
		$storefront_name = $this->storefront_data_collector->collectStorefrontName($storefront, $info);
		$storefront_fields = $this->storefront_data_collector->collectFieldsValues($storefront, $info);

		$view_buffer->assign('storefront', [
			'name' => $storefront_name,
			'fields' => $storefront_fields,
		]);
	}
}

<?php

class shopCatalogreviewsReviewsCatalogDataRenderer
{
	private $view_buffer_factory;
	private $catalog_view_buffer_modifier;
	private $seo_plugin_category_view_buffer_modifier;
	private $storefront_view_buffer_modifier;
	private $pagination_view_buffer_modifier;
	private $view_buffer_modifiers;

	public function __construct(
		shopCatalogreviewsViewBufferFactory $view_buffer_factory,
		shopCatalogreviewsReviewsCatalogViewBufferModifier $catalog_view_buffer_modifier,
		shopCatalogreviewsCategoryViewBufferModifier $seo_plugin_category_view_buffer_modifier,
		shopCatalogreviewsStorefrontViewBufferModifier $storefront_view_buffer_modifier,
		shopCatalogreviewsPaginationViewBufferModifier $pagination_view_buffer_modifier,
		shopCatalogreviewsViewBufferModifiers $view_buffer_modifiers
	)
	{
		$this->view_buffer_factory = $view_buffer_factory;
		$this->catalog_view_buffer_modifier = $catalog_view_buffer_modifier;
		$this->seo_plugin_category_view_buffer_modifier = $seo_plugin_category_view_buffer_modifier;
		$this->storefront_view_buffer_modifier = $storefront_view_buffer_modifier;
		$this->pagination_view_buffer_modifier = $pagination_view_buffer_modifier;
		$this->view_buffer_modifiers = $view_buffer_modifiers;
	}

	/**
	 * @param string $storefront
	 * @param int $category_id
	 * @param int $page
	 * @param string $template
	 * @return string
	 */
	public function render($storefront, $category_id, $page, $template)
	{
		$view = $this->getViewBuffer($storefront, $category_id, $page);

		return $view->render($template);
	}

	/**
	 * @param string $storefront
	 * @param int $category_id
	 * @param int $page
	 * @param array $templates
	 * @return array
	 */
	public function renderAll($storefront, $category_id, $page, array $templates)
	{
		$view = $this->getViewBuffer($storefront, $category_id, $page);

		return $view->renderAll($templates);
	}

	private function getViewBuffer($storefront, $category_id, $page)
	{
		$view_buffer = $this->view_buffer_factory->createViewBuffer();
		$this->catalog_view_buffer_modifier->modify($storefront, $category_id, $view_buffer);

		// category.seo_name, category.fields
		$this->seo_plugin_category_view_buffer_modifier->modify($storefront, $category_id, $view_buffer);
		$this->storefront_view_buffer_modifier->modify($storefront, $view_buffer);
		$this->pagination_view_buffer_modifier->modify($page, $view_buffer);

		$this->view_buffer_modifiers->modify($view_buffer);

		return $view_buffer;
	}
}

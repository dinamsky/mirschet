<?php

class shopCatalogreviewsWaFrontendNavHandler
{
	private $plugin;

	public function __construct(shopCatalogreviewsPlugin $plugin)
	{
		$this->plugin = $plugin;
	}

	public function handle()
	{
		$env = $this->plugin->getEnv();
		$config = $env->getConfig();

		if (!$config->plugin_is_enabled || !$config->category_is_enabled)
		{
			return null;
		}

		$is_category_action = shopCatalogreviewsContext::getFrontendInstance()->getWaRouting()->isCategoryAction();
		$category = $env->getCategory();
		$is_in_sidebar = $config->reviews_page_link_display_mode === shopCatalogreviewsReviewsPageLinkDisplayMode::SIDEBAR;

		if (!$is_category_action || !$category || !$is_in_sidebar)
		{
			return null;
		}

		$link_text = $this->getLinkText($config, $category);

		$href = $env->getPluginRouting()->getReviewsPageUrl($category);

		$view = wa()->getView();
		$template_path = shopCatalogreviewsWaHelper::getPath('templates/event_handlers/FrontendNav.html');

		$view->assign([
			'catalogreviews_category' => $category,
			'catalogreviews_reviews_catalog_page_url' => $href,
			'catalogreviews_nav_link_text' => $link_text,
		]);

		shopCatalogreviewsWaAppConfig::renamePlugin(wa()->getConfig(), '');

		return $href
			? $view->fetch($template_path)
			: null;
	}

	/**
	 * @param shopCatalogreviewsFullConfig $config
	 * @param array $category
	 * @return string
	 */
	private function getLinkText(shopCatalogreviewsFullConfig $config, $category)
	{
		$env = $this->plugin->getEnv();

		$link_template = $config->reviews_page_link_template;
		$page = waRequest::get('page', 1, waRequest::TYPE_INT);

		$context = shopCatalogreviewsContext::getFrontendInstance();
		$renderer = $context->getReviewsCatalogDataRenderer();
		$link_text = $renderer->render(
			$env->getStorefront(),
			$category['id'],
			$page,
			$link_template
		);

		return $link_text;
	}
}

<?php

class shopCatalogreviewsWaFrontendCategoryHandler
{
	private $plugin;

	public function __construct(shopCatalogreviewsPlugin $plugin)
	{
		$this->plugin = $plugin;
	}

	public function handle($category)
	{
		$env = $this->plugin->getEnv();

		$config = $env->getConfig();

		if (!$config->plugin_is_enabled || !$config->category_is_enabled)
		{
			return '';
		}

		$category_reviews_url = $env->getPluginRouting()->getReviewsPageUrl($category);
		if (!$category_reviews_url)
		{
			return '';
		}

		if ($config->reviews_page_link_display_mode === shopCatalogreviewsReviewsPageLinkDisplayMode::AFTER_H1)
		{
			$link_text = $this->getLinkText($config, $category);
			$this->plugin->getSystem()->getView()->assign([
				'catalogreviews_category_reviews_url' => $category_reviews_url,
				'catalogreviews_category_link_text' => $link_text,
			]);

			$template_path = shopCatalogreviewsWaHelper::getPath('templates/event_handlers/FrontendCategory.html');

			return wa()->getView()->fetch($template_path);
		}

		return '';
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

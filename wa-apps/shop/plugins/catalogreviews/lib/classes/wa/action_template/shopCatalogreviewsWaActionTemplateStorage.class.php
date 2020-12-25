<?php

class shopCatalogreviewsWaActionTemplateStorage
{
	private $theme;

	public function __construct(shopCatalogreviewsWaTheme $theme)
	{
		$this->theme = $theme;
	}

	/**
	 * @return shopCatalogreviewsWaActionTemplate
	 */
	public function getCategoryReviewsTemplate()
	{
		$is_custom_review_theme_template_exists = $this->isThemeFileExists('catalogreviews_plugin_review.html');

		$theme_template = 'catalogreviews_plugin_category_reviews.html';
		$theme_sub_templates = [
			'review' => $is_custom_review_theme_template_exists ? 'catalogreviews_plugin_review.html' : 'review.html',
			'review_wrap' => shopCatalogreviewsWaHelper::getPath('templates/actions/frontend/ReviewWrap.html'),
		];

		$plugin_template = 'FrontendCategoryReviews';
		$plugin_sub_templates = [
			'review' => $is_custom_review_theme_template_exists
				? $this->theme->getPath() . '/catalogreviews_plugin_review.html'
				: $this->theme->getPath() . '/review.html'
		];

		$theme_template = 'catalogreviews_plugin_category_reviews.html';
		$theme_sub_templates = [
			'review_wrap' => 'catalogreviews_plugin_review_wrap.html',
			'review' => 'catalogreviews_plugin_review.html',
		];

		$plugin_template = 'FrontendCategoryReviews';
		$plugin_sub_templates = [
			'review_wrap' => shopCatalogreviewsWaHelper::getPath('templates/actions/frontend/ReviewWrap.html'),
			'review' => shopCatalogreviewsWaHelper::getPath('templates/actions/frontend/Review.html'),
		];

		return $this->isThemeFileExists($theme_template)
			? new shopCatalogreviewsWaActionThemeTemplate($theme_template, $theme_sub_templates)
			: new shopCatalogreviewsWaActionPluginTemplate($plugin_template, $plugin_sub_templates);
	}

	private function isThemeFileExists($theme_template)
	{
		$theme = $this->theme;

		if (is_array($theme_template))
		{
			foreach ($theme_template as $sub_template)
			{
				if (!file_exists($theme->getPath() . '/' . $sub_template))
				{
					return false;
				}
			}
		}
		else
		{
			if (!file_exists($theme->getPath() . '/' . $theme_template))
			{
				return false;
			}
		}

		return true;
	}
}

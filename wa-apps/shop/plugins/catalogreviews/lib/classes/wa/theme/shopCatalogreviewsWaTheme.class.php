<?php

/**
 * Class shopCatalogreviewsWaTheme
 *
 * @property string $name
 * @property string $description
 * @property string $about
 * @property string $instruction
 * @property string $support
 * @property string $version
 * @property string $vendor
 * @property int $edition Incremental counter of theme changes
 * @property string $parent_theme_id Parent theme ID
 * @property-read string $id
 * @property-read string $slug
 * @property string $author
 * @property-read string $app
 * @property-read string $app_id
 * @property-read string $cover
 * @property-read string $path
 * @property-read string $path_custom
 * @property-read string $custom
 * @property-read string $path_original
 * @property-read string $original
 * @property-read string $type
 * @property-read string $url Theme directory URL
 * @property-read string $source_theme_id Source theme ID for duplicated one
 * @property shopCatalogreviewsWaTheme|false $parent_theme Parent theme instance or false
 * @property-read shopCatalogreviewsWaTheme[] $related_themes
 * @property-read array $used theme settlement URLs
 * @property-read bool $system
 * @property-read string[] $thumbs
 * @property-read array[] $requirements
 */
class shopCatalogreviewsWaTheme
{
	private $wa_theme;

	private $_parent_theme;

	public function __construct(waTheme $wa_theme)
	{
		$this->wa_theme = $wa_theme;
	}

	public function __get($name)
	{
		if ($name === 'parent_theme')
		{
			return $this->getParentTheme();
		}
		elseif ($name === 'related_themes')
		{
			return $this->getRelatedThemes();
		}

		return $this->wa_theme->$name;
	}

	public function getPath()
	{
		return $this->wa_theme->getPath();
	}

	/**
	 * @return shopCatalogreviewsWaTheme|false
	 */
	private function getParentTheme()
	{
		if (!isset($this->_parent_theme))
		{
			$wa_parent_theme = $this->wa_theme->parent_theme;

			$this->_parent_theme = $wa_parent_theme
				? new shopCatalogreviewsWaTheme($wa_parent_theme)
				: false;
		}

		return $this->_parent_theme;
	}

	/**
	 * @return waTheme[]
	 * @throws waException
	 */
	private function getRelatedThemes()
	{
		$this_theme_key = sprintf('%s:%s', $this->app_id, $this->id);
		$parent_theme_key = $this->parent_theme_id;

		$related_themes = [
			$this_theme_key => $this,
		];

		if ($parent_theme_key)
		{
			$related_themes[$parent_theme_key] = $this->getParentTheme();
		}

		foreach ($this->wa_theme->related_themes as $theme_key => $wa_related_theme)
		{
			if (array_key_exists($theme_key, $related_themes))
			{
				continue;
			}

			$related_themes[$theme_key] = new shopCatalogreviewsWaTheme($wa_related_theme);
		}

		return $related_themes;
	}

	public function getFullId()
	{
		return $this->wa_theme->app_id . '/' . $this->wa_theme->id;
	}
}

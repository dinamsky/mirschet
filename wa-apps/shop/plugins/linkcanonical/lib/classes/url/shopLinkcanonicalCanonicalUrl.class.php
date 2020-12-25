<?php

class shopLinkcanonicalCanonicalUrl extends shopLinkcanonicalShopUrl
{
	protected $canonical;

	protected $queries_insert = array();

	protected $paths_exclude = array();

	protected $paths_insert = array();

	/**
	 * Класс создает каноническую ссылку
	 *
	 * shopLinkcanonicalCanonical constructor.
	 * @param null|string $url
	 */
	public function __construct($url = null)
	{
		parent::__construct($url);
	}

	public function __toString()
	{
		$canonical = $this->getCanonical();

		return $canonical === null ? '' : $canonical;
	}


	/**
	 * Вернет каноническую ссылку
	 *
	 * @return string|null
	 */
	public function getCanonical()
	{
		if($this->canonical == null)
		{
			$this->createCanonical();
		}
		return $this->canonical;
	}

	/**
	 * Вернет мета тег<br>
	 * <code><link rel="canonical" href="http://example.com"/></code>
	 *
	 * @return string|null
	 */
	public function getLinkCanonical()
	{
		return $this->getCanonical() ? '<link rel="canonical" href="' . $this->getCanonical() . '"/>' : null;
	}

	/**
	 * Установить массив get параметров, которые нужно включить в каноническую ссылку
	 *
	 * @param array $queries_insert: array('page', 'color')
	 * @return $this
	 */
	public function setQueriesInsert($queries_insert)
	{
		if (is_array($queries_insert))
		{
			$this->queries_insert = $queries_insert;
		}

		return $this;
	}

	/**
	 * Добавить массив get параметров, которые нужно включить в каноническую ссылку
	 *
	 * @param array $queries_insert: array('page', 'color')
	 * @return $this
	 */
	public function mergeQueriesInsert($queries_insert)
	{
		if (is_array($queries_insert))
		{
			$this->queries_insert = array_merge($this->queries_insert, $queries_insert);
		}

		return $this;
	}

	/**
	 * Добавить get параметр, который нужно включить в каноническую ссылку
	 *
	 * @param string $query_insert: 'page'
	 * @return $this
	 */
	public function appendQueryInsert($query_insert)
	{
		if (is_string($query_insert))
		{
			$this->queries_insert[] = $query_insert;
		}

		return $this;
	}

	public function getQuriesInsert()
	{
		$result = array();
		foreach ($this->queries_insert as $query_insert)
		{
			$result[$query_insert] = true;
		}

		return array_keys($result);
	}

	/**
	 * Установить массив путей, которые нужно исключить из канонической ссылки<br>     *
	 *
	 * @param array $paths_exclude : array('reviews', 'product_page')
	 * @return $this
	 * @throws waException
	 */
	public function setPathsExclude($paths_exclude)
	{
		if (!is_array($paths_exclude))
		{
			throw new waException('$paths_exclude != array');
		}
		$this->paths_exclude = $paths_exclude;

		return $this;
	}

	/**
	 * Добавляет путь, который нужно исключить из канонической ссылки
	 *
	 * @param string $path_exclude : 'reviews'
	 * @return $this
	 * @throws waException
	 */
	public function appendPathExclude($path_exclude)
	{
		if (!is_string($path_exclude))
		{
			throw new waException('$path_exclude != string');
		}
		$this->paths_exclude[] = $path_exclude;

		return $this;
	}

	/**
	 * Устанавливает пути в конец
	 *
	 * @param array $paths_insert ['my/path', 'path']
	 * @return $this
	 * @throws waException
	 */
	public function setPathsInsert($paths_insert)
	{
		if (!is_array($paths_insert))
		{
			throw new waException('$paths_insert != array');
		}
		$this->paths_insert = $paths_insert;
		return $this;
	}

	/**
	 * Добавляет путь в конец
	 *
	 * @param string $path_insert 'my/path' or 'path'
	 * @return $this
	 * @throws waException
	 */
	public function appendPathInsert($path_insert)
	{
		if (!is_string($path_insert))
		{
			throw new waException('$paths_insert != string');
		}
		$this->paths_insert[] = $path_insert;

		return $this;
	}

	public function getPath()
	{
		$parse_url = $this->getParseUrl();
		$path = isset($parse_url['path']) ? $parse_url['path'] : '';

		foreach ($this->paths_exclude as $path_exclude)
		{
			$path = str_replace('/' . $path_exclude . '/', '/', $path);
		}
		$path_insert = '';
		if (!empty($this->paths_insert))
		{
			$path_insert .= implode('/', $this->paths_insert) . '/';
		}

		return $path . $path_insert;
	}

	public function getQuery()
	{
		$parse_url = $this->getParseUrl();
		$query = isset($parse_url['query']) ? $parse_url['query'] : '';
		parse_str($query, $get_params);
		$q = array();
		foreach ($this->queries_insert as $query_insert)
		{
			if (array_key_exists($query_insert, $get_params))
			{
				$q[$query_insert] = $query_insert == 'page'
					? '' . (int) $get_params[$query_insert]
					: $get_params[$query_insert];
			}
		}
		$query = http_build_query($q);

		return $query;
	}

	public function createCanonical()
	{
		$scheme = $this->getSheme();
		$host = $this->getHost();
		$path = $this->getPath();
		$query = $this->getQuery();


		$this->canonical = $scheme . '://' . $host . $path . (empty($query) ? '' : '?' . $query);

		return $this;
	}
}
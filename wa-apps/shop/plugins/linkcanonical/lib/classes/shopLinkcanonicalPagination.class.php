<?php

class shopLinkcanonicalPagination
{
	/**
	 * @var shopLinkcanonicalUrl
	 */
	protected $url;

	protected $page;

	protected $pages_count;

	protected $full_path;

	public function __construct($url = null, $page = 1, $pages_count = 1)
	{
		$this->setUrl($url);
		$this->page = intval($page);
		$this->pages_count = intval($pages_count);
	}

	public function getPrevPage()
	{
		return $this->page - 1;
	}

	public function getNextPage()
	{
		return $this->page + 1;
	}

	public function getPrevUrl()
	{
		$prev_page = $this->getPrevPage();
		$prev = '';

		if($prev_page > 1 && $prev_page < $this->pages_count)
		{
			$prev = $this->getFullPath() . $this->getQuery($prev_page);
		}
		else if ($prev_page == 1)
		{
			$prev = $this->getFullPath() . $this->getQuery();
		}

		return $prev;
	}

	public function getNextUrl()
	{
		$next_page = $this->getNextPage();
		$next = '';

		if ($this->page == 1 && $next_page <= $this->pages_count)
		{
			$next = $this->getFullPath() . $this->getQuery(2);
		}
		else if($next_page <= $this->pages_count && $next_page > 1)
		{
			$next = $this->getFullPath() . $this->getQuery($next_page);
		}

		return $next;
	}

	public function getPrev()
	{
		$prev = $this->getPrevUrl();

		return empty($prev) ? '' : '<link rel="prev" href="' . $prev . '">';
	}

	public function getNext()
	{
		$next = $this->getNextUrl();

		return empty($next) ? '' : '<link rel="next" href="' . $next . '">';
	}

	public function getTags()
	{
		return $this->getPrev() . $this->getNext();
	}

	protected function getQuery($page = null)
	{
		$query = $this->url->getQuery();
		parse_str($query, $query_params);
		$query_params['page'] = $page;

		if (is_null($page))
		{
			unset($query_params['page']);
		}

		$query = http_build_query($query_params);

		return empty($query) ? '' :  '?' . $query;
	}

	protected function getFullPath()
	{
		if (empty($this->full_path))
		{
			$this->full_path = $this->url->getSheme() . '://' . $this->url->getHost() .  $this->url->getPath();
		}

		return $this->full_path;
	}

	/**
	 * @param shopLinkcanonicalUrl|string|null $url
	 * @return $this
	 */
	public function setUrl($url = null)
	{
		if ($url instanceof shopLinkcanonicalUrl)
		{
			$this->url = $url;
		}
		elseif (is_string($url))
		{
			if(filter_var($url, FILTER_VALIDATE_URL) !== false)
			{
				$this->url = new shopLinkcanonicalUrl($url);
			}
		}

		if (empty($this->url))
		{
			$this->url = new shopLinkcanonicalUrl();
		}

		return $this;
	}
}
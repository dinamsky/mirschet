<?php

class shopLinkcanonicalUrl
{
	protected $url;
	protected $parse_url;

	public function __construct($url = null)
	{
		$this->setUrl($url);
	}

	public function __toString()
	{
		return $this->url ? $this->url : '';
	}

	/**
	 * @return string
	 */
	final public static function getFullUrl()
	{
		$server = waRequest::server();
		$url  = waRequest::isHttps() ? 'https://':  'http://';
		$url .= $server['HTTP_HOST'];
		//$url .= ( $server["SERVER_PORT"] != 80 ) ? ":".$server["SERVER_PORT"] : "";
		$url .= $server["REQUEST_URI"];
		return $url;
	}

	final public function getParseUrl()
	{
		if ($this->parse_url === null)
		{
			$this->parse_url = parse_url($this->url);
		}
		
		return $this->parse_url;
	}

	/**
	 * @return array
	 */
	final public function getExplodePath()
	{
		$parse_url = $this->getParseUrl();
		if (empty($parse_url['path']))
		{
			return array();
		}
		$path_array = explode('/', $parse_url['path']);

		return $path_array;
	}

	/**
	 * @return string
	 */
	final public function getUrl()
	{
		return $this->url;
	}

	/**
	 * @param string $url
	 * @return shopLinkcanonicalUrl
	 */
	final public function setUrl($url = null)
	{
		if (is_string($url))
		{
			if(filter_var($url, FILTER_VALIDATE_URL) === false)
			{
				$url = null;
			}
		}
		$this->url = $url == null
			? self::getFullUrl()
			: (is_string($url) ? $url : null);

		return $this;
	}

	public function getSheme()
	{
		$parse_url = $this->getParseUrl();
		$scheme = isset($parse_url['scheme']) ? $parse_url['scheme'] : '';

		return $scheme;
	}

	public function getHost()
	{
		$parse_url = $this->getParseUrl();
		$host = isset($parse_url['host']) ? $parse_url['host'] : '';

		return $host;
	}

	public function getPath()
	{
		$parse_url = $this->getParseUrl();
		$path = isset($parse_url['path']) ? $parse_url['path'] : '';

		return $path;
	}

	public function getQuery()
	{
		$parse_url = $this->getParseUrl();
		$query = isset($parse_url['query']) ? $parse_url['query'] : '';

		if ($query === '' && substr($this->url, -1, 1) == '?')
		{
			return '?';
		}

		return $query;
	}

	public function getQueryParams()
	{
		$query = $this->getQuery();
		parse_str($query, $get_params);

		return array_keys($get_params);
	}

	/**
	 * @param string $param
	 * @return bool
	 */
	public function hasParam($param)
	{
		return in_array($param, $this->getQueryParams());
	}
}
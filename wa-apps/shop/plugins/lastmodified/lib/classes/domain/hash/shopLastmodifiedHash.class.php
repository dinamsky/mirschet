<?php


class shopLastmodifiedHash
{
	private $url;
	private $hash;
	/** @var DateTime */
	private $date;
	
	public function getUrl()
	{
		return $this->url;
	}
	
	public function setUrl($url)
	{
		$this->url = $url;
	}
	
	public function getHash()
	{
		return $this->hash;
	}
	
	public function setHash($hash)
	{
		$this->hash = $hash;
	}
	
	public function getDate()
	{
		return $this->date;
	}
	
	public function setDate($date)
	{
		$this->date = $date;
	}
	
	public function updateDate()
	{
		$this->date = new DateTime('now', new DateTimezone('UTC'));
	}
}
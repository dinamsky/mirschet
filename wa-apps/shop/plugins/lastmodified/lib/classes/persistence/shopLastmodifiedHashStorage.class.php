<?php


class shopLastmodifiedHashStorage
{
	private $hash_model;
	
	public function __construct()
	{
		$this->hash_model = new shopLastmodifiedHashModel();
	}
	
	public function getByUrl($url)
	{
		$row = $this->hash_model->getByField('url', $url);
		
		if (!isset($row))
		{
			return null;
		}
		
		$hash = new shopLastmodifiedHash();
		$hash->setUrl($row['url']);
		$hash->setHash($row['hash']);
		$hash->setDate(new DateTime($row['date'], new DateTimezone('UTC')));
		
		return $hash;
	}
	
	public function store(shopLastmodifiedHash $hash)
	{
		$row = array(
			'url' => $hash->getUrl(),
			'hash' => $hash->getHash(),
			'date' => $hash->getDate()->format('Y-m-d H:i:s'),
		);
		
		$this->hash_model->replace($row);
	}
}
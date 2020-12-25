<?php


class shopLastmodifiedRequestHandler
{
	private $configuration;
	private $hash_storage;
	private $data_selector;
	
	public function __construct(
		shopLastmodifiedRequestHandlerConfiguration $configuration,
		shopLastmodifiedHashStorage $hash_storage, shopLastmodifiedDataSelector $data_selector
	)
	{
		$this->hash_storage = $hash_storage;
		$this->configuration = $configuration;
		$this->data_selector = $data_selector;
	}
	
	public function handle()
	{
		if (!$this->isAllow())
		{
			return;
		}
		
		$last_modified = $this->getLastModified();
		$if_modified_since = $this->getIfModifiedSince();
		
		$is_send_304 = $this->configuration->getType() == 'lm304';
		
		if ($is_send_304 && $if_modified_since)
		{
			if (shopLastmodifiedDatetimeExtension::getTimestamp($if_modified_since)
				>= shopLastmodifiedDatetimeExtension::getTimestamp($last_modified))
			{
				$this->send304Header();
				
				return;
			}
		}
		
		if (!$is_send_304)
		{
			wa()->getResponse()->addHeader('Cache-Control', 'no-store');
		}
		
		$this->sendLastModifiedHeader($last_modified);
	}
	
	protected function isAllow()
	{
		$user_agent = wa()->getRequest()->getUserAgent();
		$user_agent_analyzer = new shopLastmodifiedUserAgentAnalyzer();
		$user_agent_analyzer->isBot($user_agent);
		$agent = $user_agent_analyzer->isBot($user_agent) ? 'bot' : 'user';
		
		return in_array($agent, $this->configuration->getAllowAgents());
	}
	
	protected function getLastModified()
	{
		if (in_array($this->configuration->getDate(), array('now', 'prev_hour', 'prev_day')))
		{
			$date = shopLastmodifiedDatetimeExtension::getDateByType($this->configuration->getDate());
		}
		else
		{
			$date = $this->getCurrentHash()->getDate();
		}
		
		return $date;
	}
	
	protected function getHash()
	{
		$hash_data = array(
			'data' => $this->data_selector->getData(),
			'meta_title' => wa()->getResponse()->getTitle(),
			'meta_keywords' => wa()->getResponse()->getMeta('keywords'),
			'meta_description' => wa()->getResponse()->getMeta('description'),
		);
		
		return md5(json_encode($hash_data));
	}
	
	protected function getCurrentHash()
	{
		$view_helper = new waViewHelper(new waSmarty3View(wa()));
		$current_url = $view_helper->currentUrl(true);
		$hash = $this->hash_storage->getByUrl($current_url);
		$new_hash = $this->getHash();
		
		$is_new = !isset($hash);
		
		if ($is_new)
		{
			$hash = new shopLastmodifiedHash();
			$hash->setUrl($current_url);
		}
		
		if ($hash->getHash() != $new_hash)
		{
			$hash->setHash($new_hash);
			$hash->updateDate();
		}
		
		$this->hash_storage->store($hash);
		
		return $hash;
	}
	
	protected function getIfModifiedSince()
	{
		$if_modified_since = wa()->getRequest()->server('HTTP_IF_MODIFIED_SINCE');
		
		if (!$if_modified_since)
		{
			return null;
		}
		
		try
		{
			$if_modified_since = new DateTime($if_modified_since);
			$if_modified_since->setTimezone(new DateTimeZone('UTC'));
			
			return $if_modified_since;
		}
		catch (Exception $e)
		{
			return null;
		}
	}
	
	protected function sendLastModifiedHeader(DateTime $last_modified)
	{
		$http_last_modified = $last_modified->format('D, d M Y H:i:s \G\M\T');
		wa()->getResponse()->addHeader('Last-Modified', $http_last_modified);
		wa()->getResponse()->sendHeaders();
	}
	
	protected function send304Header()
	{
		wa()->getResponse()->setStatus(304);
		wa()->getResponse()->sendHeaders();
		exit;
	}
}
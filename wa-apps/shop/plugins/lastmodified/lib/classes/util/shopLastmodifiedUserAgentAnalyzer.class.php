<?php


class shopLastmodifiedUserAgentAnalyzer
{
	public function isBot($user_agent)
	{
		return $this->isYandexBot($user_agent)
			|| $this->isGoogleBot($user_agent)
			|| $this->isBingBot($user_agent)
			|| $this->isYahooBot($user_agent)
			|| $this->isLastModifiedBot($user_agent);
	}
	
	public function isYandexBot($user_agent)
	{
		return strpos($user_agent, '+http://yandex.com/bots') !== false;
	}
	
	public function isGoogleBot($user_agent)
	{
		return strpos($user_agent, '+http://www.google.com/bot.html') !== false;
	}
	
	public function isBingBot($user_agent)
	{
		return strpos($user_agent, '+http://www.bing.com/bingbot.htm') !== false;
	}
	
	public function isYahooBot($user_agent)
	{
		return strpos($user_agent, '+http://help.yahoo.com/help/us/ysearch/slurp') !== false;
	}
	
	public function isLastModifiedBot($user_agent)
	{
		return strpos($user_agent, '+http://last-modified.com/ru/') !== false;
	}
}
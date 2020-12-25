<?php

class shopLinkcanonicalWaRouting
{
    private $routing;

    public function __construct()
    {
        $this->routing = wa()->getRouting();
    }

    public function getStorefronts()
    {
        $domains = $this->routing->getByApp('shop');
        $urls = array();

        foreach ($domains as $domain => $routes)
        {
            foreach ($routes as $route)
            {
                if (!$this->routing->isAlias($domain) and isset($route['url']))
                {
                    $urls[] = $domain . '/' . $route['url'];
                }
            }
        }

        return $urls;
    }

    public function getCurrentStorefront()
    {
        $route = $this->routing->getRoute();

        if ($route['app'] === 'shop')
        {
            $domain = $this->routing->getDomain();

            return $domain . '/' . $route['url'];
        }

        return null;
    }
}
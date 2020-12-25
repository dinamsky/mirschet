<?php

/**
 * Class shopVkontaktePlugin
 */
class shopVkontaktePlugin extends shopPlugin
{


    /**
     * @var
     */
    private static $view;

    /**
     * @return waSmarty3View|waView
     * @throws waException
     */
    private static function getView()
    {
        if (!isset(self::$view)) {
            self::$view = waSystem::getInstance()->getView();
        }
        return self::$view;
    }

    /**
     * @var shopVkontaktePlugin $plugin
     */
    private static $plugin;

    /**
     * @return shopVkontaktePlugin
     * @throws waException
     */
    private static function getPlugin()
    {
        if (!isset(self::$plugin)) {
            self::$plugin = wa()->getPlugin('vkontakte');
        }
        return self::$plugin;
    }

    /**
     * @param shopProduct $product
     * @return array|bool
     * @throws waException
     */
    public function backendProduct(shopProduct $product)
    {
        $settings = $this->getSettings();

        if (isset($settings['domain']) && strlen($settings['domain']) > 4) {
            $product_url = $settings['domain'] . $product->url;
        }
        else {
            $routing = wa('shop')->getRouting();
            $params = array('product_url' => $product->url);
            $product_url = $routing->getUrl('/frontend/product', $params, true);
        }

        if (!isset($settings['desc_size'])) $settings['desc_size'] = 650;
        $view = self::getView();

        $prd = array();

        $prd['id'] = $product['id'];
        $prd['name'] = strip_tags($product['name']);
        $prd['price'] = intval($product['price']) > 0 ? intval($product['price']) : 0;
        $prd['description'] = json_encode(mb_substr(strip_tags($product[$settings['desc']]),0,$settings['desc_size'],'utf-8'));

        $view->assign('product_url', $product_url);
        $view->assign('settings', $settings);
        $view->assign('product', $prd);
        $view->assign('pluginurl', $this->getPluginStaticUrl(true));

        if (isset($settings['app_id']) && is_numeric($settings['app_id'])) {
            return array(
                'toolbar_section' => $view->fetch($this->path . '/templates/auth.html'),
            );
        }
        else return false;
    }


    /**
     * @return array
     * @throws waException
     */
    public static function getSettlements()
    {
        $plugin = self::getPlugin();
        $settings = $plugin->getSettings();
        $settlements = array();
        $current_domain = $settings['domain'];
        $routing = wa()->getRouting();
        $domain_routes = $routing->getByApp('shop');
        foreach ($domain_routes as $domain => $routes) {
            foreach ($routes as $route) {
                $routing->setRoute($route, $domain);
                $settlement = wa()->getRouteUrl('/frontend/product', array('product_url' => false), true);
                if (($settlement == $current_domain) || ($current_domain === '')) {
                    $current_domain = $settlement;
                    $routing->setRoute($route, $domain);
                    waRequest::setParam($route);
                }
                $settlement = rtrim($settlement, '/').'/';
                $settlements[] = $settlement;
            }
        }
        return $settlements;
    }

    /**
     * @return string
     * @throws waException
     */
    public static function settingCustomControlSettlements() {
        $view = self::getView();
        $plugin = self::getPlugin();
        $settlements = self::getSettlements();
        $settings = $plugin->getSettings();
        $current_domain = $settings['domain'];

        $view->assign('current_domain', $current_domain);

        $view->assign('settlements', $settlements);
        return $view->fetch($plugin->path . '/templates/settlements.html');
    }

    /**
     * @return string
     */
    private static function hostType() {
        if (waRequest::isHttps()) {
            $url = 'https://';
        } else {
            $url = 'http://';
        }
        return $url;
    }

    /**
     * @return string
     * @throws waException
     */
    public static function getFeedbackControl()
    {
        $view = self::getView();
        $plugin = self::getPlugin();
        return $view->fetch($plugin->getPluginPath() . '/templates/controls/feedbackControl.html');
    }

    /**
     * @return string
     */
    public function getPluginPath()
    {
        return $this->path;
    }
}

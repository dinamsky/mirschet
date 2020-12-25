<?php

class shopFacebookproPluginBackendSetupAction extends waViewAction
{
    private $plugin_id = 'facebookpro';

    /**
     *
     */
    public function execute()
    {
        $routing = wa()->getRouting();
        $settlements = array();
        $settlement_cur = array();
        //print($routing);
        $profile_helper = new shopImportexportHelper($this->plugin_id);
        $this->view->assign('profiles', $list = $profile_helper->getList());
        $profile = $profile_helper->getConfig();

        $profile['config'] += array(
            'hash'     => '',
            'domain'   => '',
            'lifetime' => 0,
        );
        $current_domain = &$profile['config']['domain'];

        $this->view->assign('current_domain', $current_domain);
        $domain_routes = $routing->getByApp('shop');
        foreach ($domain_routes as $domain => $routes) {
            foreach ($routes as $route) {


                $settlement = $domain.'/'.$route['url'];
                if (($settlement == $current_domain) || ($current_domain === '')) {
                    $current_domain = $settlement;
                    $routing->setRoute($route, $domain);
                    waRequest::setParam($route);
                }
                $settlements[] = $settlement;

                $settlement_cur['sett'] = $settlement;
                $settlement_cur['cur'] = $route['currency'];
            }
        }
	    waRequest::setParam('test2', $settlement_cur);
       
           /// wa_dump($profile);
        $this->view->assign('profile', $profile);


        $info = array();

        $this->view->assign('settlements', $settlements);
       //$this->view->assign('settlement_cur', $settlement_cur);


        if (!empty($profile['id'])) {
            $path = shopFacebookproPlugin::path($profile['id'].'.xml');
            $info['exists'] = file_exists($path);
            $info['mtime'] = $info['exists'] ? filemtime($path) : null;
        } else {
            $info['mtime'] = $info['exists'] = null;
        }

        if ($info['exists']) {
            $route_params = array(
                'plugin' => $this->plugin_id,
                'hash'   => $this->plugin()->getHash($profile['id']),
            );
            $info['url'] = $routing->getUrl('shop/frontend/catalog', $route_params, true);
        } else {
            $info['url'] = null;
        }
        $this->view->assign('info', $info);
        /**
         * @var shopConfig $config ;
         */
        $config = wa('shop')->getConfig();

	    $model = new shopCurrencyModel();
	    $currencyArray = $model->getCurrencies();

	    $currenciesArray = array();
	    foreach ($currencyArray as $key=>$val)
	    {
		    if($val['is_primary'] == true)
		    	continue;
		    else{
		    	$currenciesArray[] = $key;

		    }
	    }
	    array_unshift($currenciesArray, $config->getCurrency());
        $this->view->assign('primary_currency', $config->getCurrency());
        $this->view->assign('user_currency2', $currenciesArray);

	    $rt = '';



        $this->view->assign('company', ifempty($profile['config']['company'], $config->getGeneralSettings('name')));

        $type_model = new shopTypeModel();
        $this->view->assign('types', $type_model->getAll());
        $profile_map = ifset($profile['config']['map'], array());
        $export = ifset($profile['config']['export'], array());
        $set_model = new shopSetModel();
        $map = $this->plugin()->map(array(), null, true);
        $params = array();
        if ($profile_map) {
            foreach ($map as $type => &$type_map) {
                foreach ($type_map['fields'] as $field => &$info) {
                    $info['source'] = ifempty($profile_map[$type][$field], 'skip:');
                    unset($profile_map[$type][$field]);
                    unset($info);
                }
                if (!empty($type_map['fields']['param.*'])) {
                    $params[$type] = -1;
                }
                unset($type_map);
            }
            foreach ($profile_map as $type => $fields) {
                foreach ($fields as $field => $source) {



                    $info_field = (strpos($field, 'param.') === 0) ? 'param.*' : $field;
                    if (isset($map[$type]['fields'][$info_field])) {
                        $info = $map[$type]['fields'][$info_field];
                        $info['source'] = ifempty($source, 'skip:');

                        $map[$type]['fields'][$field] = $info;
                        $params[$type] = max(ifset($params[$type], -1), intval(preg_replace('@\D+@', '', $field)));
                    }
                }
            }
        }

              // echo "<pre>";
        
              /*  $p = (int)$profile['config']['export']['min_price'];
                $ty = gettype($p);
                 echo $ty;*/

                /*print_r($profile['config']['export']['min_price']);
                echo "</pre>";*/







        $this->view->assign('sets', $set_model->getAll());

        $this->view->assign('type_map', $map);
        $this->view->assign('params', array('params' => $params));
        $this->view->assign('export', $export);

        $this->view->assign('types_map', ifset($profile['config']['types'], array()));

        $app_settings_model = new waAppSettingsModel();
        $app_settings = array(
            'ignore_stock_count' => $app_settings_model->get('shop', 'ignore_stock_count', 0),
        );
        $this->view->assign('app_settings', $app_settings);

	    $this->view->assign('export', $export);

        $feature_model = new shopFeatureModel();



        $config = wa('shop')->getConfig();
        /**
         * @var shopConfig $config
         */
        $limit = $config->getOption('features_per_page');
        if ($feature_model->countByField(array('parent_id' => null)) < $limit) {
            $features = $feature_model->getFeatures(true); /*, true*/
            foreach ($features as $id => $feature) {
                if (0&&!empty($feature['multiple'])
                    // || (strpos($feature['type'], shopFeatureModel::TYPE_RANGE.'.') === 0)
                    // || (strpos($feature['type'], shopFeatureModel::TYPE_2D.'.') === 0)
                    // || (strpos($feature['type'], shopFeatureModel::TYPE_3D.'.') === 0)
                    || ($feature['type'] == shopFeatureModel::TYPE_DIVIDER)
                ) {
                    unset($features[$id]);
                }
            }
        } else {
            $this->view->assign('features_autocomplete', true);
            $features = array();
            foreach ($map as $type_map) {
                foreach ($type_map['fields'] as $info) {
                    if (!empty($info['source']) && preg_match('@^feature:([\w\d_\-]+)$@', $info['source'], $matches)) {
                        $features[] = $matches[1];
                    }
                }
            }

            if ($features = array_unique($features)) {
                $features = $feature_model->getFeatures('code', $features);
            } else {
                $features = array();
            }
        }

        foreach ($features as $id => &$feature) {

            if (strpos($feature['type'], shopFeatureModel::TYPE_DIMENSION.'.') === 0) {
                $units = shopDimension::getUnits($feature['type']);

                $feature['units'] = array();
                foreach ($units as $unit) {
                    $feature['units'][] = $unit['title'];
                }
                $feature['units'] = implode(', ', $feature['units']);
            } elseif (preg_match('@\(([^\)]+)\)$@', $feature['name'], $matches)) {
                $feature['units'] = trim($matches[1]);
            }
            unset($feature);
        }

        $this->view->assign('features', $features);

        $fields = array(
            'name'        => _w('Product name'),
            'description' => _w('Description'),
            'sku'         => _w('SKU code'),
            'summary'     => _w('Summary'),
            'count'       => _w('In stock'),
        );
        $this->view->assign('fields', $fields);
    }

    /**
     *
     * @return shopFacebookproPlugin
     */
    private function plugin()
    {
        static $plugin;
        if (!$plugin) {
            $plugin = wa()->getPlugin($this->plugin_id);
        }
        return $plugin;
    }
}

<?php

/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2017-2018
 */
class shopSdekintPluginBackendAction extends waViewAction
{
    /**
     * @throws waException
     */
    public function execute()
    {
        /** @var shopSdekintPlugin $plugin */
        $plugin = wa('shop')->getPlugin('sdekint');

        $this->setLayout(new shopBackendLayout());
        $this->layout->assign('no_level2', true);

        $Country = new waCountryModel();

        $this->getResponse()->addCss('plugins/sdekint/css/jquery.timepicker.min.css?' . $plugin->getVersion(), 'shop');
        $this->getResponse()->addCss('wa-content/js/prettify/prettify.css', false);
        $this->getResponse()->addCss('plugins/sdekint/css/vendors/select2/select2.min.css?' . $plugin->getVersion(), 'shop');
        $this->getResponse()->addJs('plugins/sdekint/js/timepicker/jquery.timepicker.min.js?' . $plugin->getVersion(), 'shop');
        $this->getResponse()->addJs('plugins/sdekint/js/vendors/select2/select2.min.js?' . $plugin->getVersion(), 'shop');
        if (wa()->getLocale() == 'ru_RU') {
            $this->getResponse()->addJs('plugins/sdekint/js/vendors/select2/i18n/ru.js?' . $plugin->getVersion(), 'shop');
        }
        $this->getResponse()->addJs('plugins/sdekint/js/vendors/jsviews/jsviews.min.js?' . $plugin->getVersion(), 'shop');
        $this->getResponse()->addJs('plugins/sdekint/js/vendors/lodash/lodash.min.js?' . $plugin->getVersion(), 'shop');

        if (waSystemConfig::isDebug()) {
            $this->getResponse()->addJs('plugins/sdekint/js/sdekint.js?' . $plugin->getVersion(), 'shop');
            $this->getResponse()->addJs('plugins/sdekint/js/sdekint.dialog.js?' . $plugin->getVersion(), 'shop');
            $this->getResponse()->addJs('plugins/sdekint/js/sdekint.http.js?' . $plugin->getVersion(), 'shop');
            $this->getResponse()->addJs('plugins/sdekint/js/sdekint.select2.js?' . $plugin->getVersion(), 'shop');
            $this->getResponse()->addJs('plugins/sdekint/js/sdekint.utils.js?' . $plugin->getVersion(), 'shop');
            $this->getResponse()->addJs('plugins/sdekint/js/sdekint.ymaps.js?' . $plugin->getVersion(), 'shop');
            $this->getResponse()->addJs('plugins/sdekint/js/sdekint.office.js?' . $plugin->getVersion(), 'shop');
            $this->getResponse()->addJs('plugins/sdekint/js/sdekint.calculator.js?' . $plugin->getVersion(), 'shop');
            $this->getResponse()->addJs('plugins/sdekint/js/sdekint.pickup.js?' . $plugin->getVersion(), 'shop');
            $this->getResponse()->addJs('plugins/sdekint/js/sdekint.calcruleaction.js?' . $plugin->getVersion(), 'shop');
            $this->getResponse()->addJs('plugins/sdekint/js/sdekint.couriercalls.js?' . $plugin->getVersion(), 'shop');
        } else {
            $this->getResponse()->addJs('plugins/sdekint/js/backend.min.js?' . $plugin->getVersion(), 'shop');
        }
        $this->getResponse()->addJs('wa-content/js/prettify/prettify.js', false);

        $sender_city = $plugin->sender_city;

        $data['origin'] = (new shopSdekintPluginCityModel)->getCityByCode(ifempty($sender_city, 'id', 44));
        foreach (['rus', 'blr', 'kaz', 'arm', 'kgz', 'ukr'] as $c) {
            $data['countries'][] = $Country->get($c);
        }

        $data['debug'] = wa()->getConfig()->isDebug() && $plugin->_iddqd_;
        $data['wa_root_url'] = wa()->getRootUrl();

        $this->view->assign(compact('data'));

    }
}

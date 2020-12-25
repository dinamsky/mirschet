<?php


class shopCouponPluginSettingsAction extends waViewAction {

    public function execute()
    {
        /**
         * @var shopCouponPlugin $plugin
         */
        $plugin = wa()->getPlugin('coupon');

        $m = new shopCouponPluginTemplateModel();
        $generators = $m->getAll();
        $this->view->assign('generators', $generators);
        $this->view->assign('remove_expired', $plugin->getSettings('remove_expired'));
    }
}
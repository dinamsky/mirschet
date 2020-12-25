<?php

class shopF7rootPluginBackendFeaturesAction extends waViewAction
{
    public function execute()
    {
        $feature_id = waRequest::request('feature', 0);
        $dm = new shopF7rootModel();
        $features = $dm->getFeature(true);
        $this->view->assign('features', $features);
        $this->view->assign('feature_id', $feature_id);
    }
}

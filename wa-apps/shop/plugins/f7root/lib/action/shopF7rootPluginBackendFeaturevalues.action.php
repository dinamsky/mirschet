<?php

class shopF7rootPluginBackendFeaturevaluesAction extends waViewAction
{
    public function execute()
    {
        $wp_any_appointed = _wp('Any appointed');
        $wp_is_not_appointed = _wp('isn\'t appointed');
        $dm = new shopF7rootModel();
        $feature = waRequest::get('feature',null);
        $feature_value_id = waRequest::request('featurevalue',null);
        if ($feature) {
            $feature_values = $dm->getFeatureValues($feature,true);
        } else {
            $feature_values = array();
        }
        $feature_values['yes']=$wp_any_appointed;//'Любое назначенное';
        $feature_values['no']=$wp_is_not_appointed;//'Не назначенно';
        $this->view->assign('featurevalues', $feature_values);
        $this->view->assign('feature_value_id', $feature_value_id);
    }
}

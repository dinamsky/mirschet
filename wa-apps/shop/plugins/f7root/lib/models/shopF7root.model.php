<?php

/**
 * Created by PhpStorm.
 * User: onehalf
 * Date: 08.03.16
 * Time: 22:40
 */
class shopF7rootModel extends waModel
{
    public function getFeatureValues($featureid, $sort = false)
    {
        $featuremodel = new shopFeatureModel;
        $feature = $featuremodel->getById($featureid);
        $featurevalues = $featuremodel->getFeatureValues($feature);
        if ($sort) {
            asort($featurevalues);
        }
        return $featurevalues;
    }

    public function getFeatureValuesById($feature_id, $feature_values_id)
    {
        switch ($feature_values_id) {
            case 'yes':
                $f_value ='Любое назначенное';
                break;
            case 'no':
                $f_value='Не назначенно';
                break;
            case 'null':
                $f_value='Не выбрано';
                break;
            default:
                $f_value = 'Не выбрано';
                $feature_model = new shopFeatureModel;
                $feature = $feature_model->getById($feature_id);
                $feature_values = $feature_model->getFeatureValues($feature);
                foreach ($feature_values as $id => $fv) {
                    if ($id == $feature_values_id) {
                        $f_value = $fv;
                        break;
                    }
                }
                break;
        }
        return $f_value;
    }

    public function getFeatureName($featuresid)
    {
        $featuremodel = new shopFeatureModel;
        $feature = $featuremodel->getById($featuresid);
        return $feature['name'];
    }

    public function getFeature($sort = false)
    {

        $plugin = wa('shop')->getPlugin('f7root');
        $settings = $plugin->getSettings('view_feature_code');
        //waLog::dump($settings);
        $feature_model = new shopFeatureModel;
        $features = $feature_model->getAll('id', true);
        $fa = array();
    //    waLog::dump($features);
        foreach ($features as $id => $f) {
            if (!$f['parent_id']) {
                $feature_type = explode('.', $f['type']);
                switch ($feature_type[0]) {
                    case 'divider':
                    case '2d':
                    case '3d':
                        break;
                    default:
                        $fa[$id] = $f['name']." (".($settings?$f['code']:"").")";
                        break;
                }
            }
        }
        if ($sort) {
            asort($fa);
        }
        return $fa;
    }
}

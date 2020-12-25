<?php

/**
 * Created by PhpStorm.
 * User: snark | itfrogs.ru
 * Date: 3/11/16
 * Time: 1:36 AM
 */
class shopVkshopPluginPrepare
{
    /**
     * @var shopProduct $product
     */
    protected $product;
    /**
     * @var
     */
    protected $settings;

    /**
     * @var array|null
     */
    protected $group = null;
    /**
     * @var |null
     */
    protected $settlement = null;


    /**
     * shopVkshopPluginPrepare constructor.
     * @param $product
     * @param $settings
     */
    function __construct($product, $settings)
    {
        $this->product = $product;
        $this->settings = $settings;

        if (isset($product->group_id) && $product->group_id > 0) {
            $group_model = new shopVkshopPluginGroupModel();
            $group = $group_model->getById($product->group_id);
            if (!empty($group)) {
                $this->group = $group;

                if (!empty($group['settlement'])) {
                    $this->settlement = $group['settlement'];
                }
            }
        }
    }

    /**
     * @return $this
     */
    public function prepareHashtags()
    {
        if (!is_numeric($this->settings['maxtags'])) {
            $this->settings['maxtags'] = 30;
        } elseif ($this->settings['maxtags'] > 30) {
            $this->settings['maxtags'] = 30;
        } elseif ($this->settings['maxtags'] < 0) {
            $this->settings['maxtags'] = 0;
        }

        if (!is_numeric($this->settings['maxtagslenght'])) {
            $this->settings['maxtagslenght'] = 500;
        } elseif ($this->settings['maxtagslenght'] > 500) {
            $this->settings['maxtagslenght'] = 500;
        } elseif ($this->settings['maxtagslenght'] < 0) {
            $this->settings['maxtagslenght'] = 0;
        }

        $tags = array();
        $lenght = 0;
        $j = 0;
        foreach ($this->product->tags as $i => $tag) {
            if ($lenght < $this->settings['maxtagslenght'] && $j < $this->settings['maxtags']) {
                $tags[$i] = '#' . mb_ereg_replace(
                    '\s+', "",
                    mb_convert_case($tag, MB_CASE_TITLE, "UTF-8")
                ); // because no whitespaces in hashtags
                $lenght += strlen($tag);
            } else {
                unset($tags[$i]);
                break;
            }
            $j++;
        }
        $this->product->hashtags = implode(' ', $tags);
        return $this;
    }

    /**
     * @return $this
     */
    public function prepareLink()
    {
        if ($this->settlement) {
            $this->product->full_url = $this->settlement . $this->product->url;
        } else {
            $routing = wa('shop')->getRouting();
            $params = array('product_url' => $this->product->url);
            $this->product->full_url = $routing->getUrl('/frontend/product', $params, true);
        }
        return $this;
    }

    /**
     * @return shopProduct
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return $this
     */
    public function prepareFeatures(){
        $prd = $this->product;
        /*
         * Т.к. в 6 версии в shopProduct нет getFeatures(),
         * пришлось обойти напрямую через модель
         */
        $mpp = new shopProductFeaturesModel();
        $prd_all_features = $mpp->getData($prd);
        $model_features = new shopFeatureModel();

        if (count($this->settings['features']) == 0) {
            return $this;
        }
        $features_by_code = $model_features->getAll('code');
        $features_txt = "";
        foreach ($prd_all_features as $code => $value) {
            if ($features_by_code[$code]['type'] == shopFeatureModel::TYPE_DIVIDER || $features_by_code[$code]['parent_id'] != null) {
                continue;
            }
            if (isset($this->settings['features'][$features_by_code[$code]['id']])) {
                $features_txt .= $features_by_code[$code]['name'] . ': ';
                switch ($value) {
                    case (is_object($value)):
                        $features_txt .= (string) $value . ', ';
                        break;
                    case (is_array($value)):
                        foreach ($value as $cvalue) {
                            if (is_object($cvalue)) {
                                $features_txt .= (string) $cvalue . ', ';
                            } else {
                                $features_txt .= $cvalue . ', ';
                            }
                        }
                        break;
                    default:
                        $features_txt .= $value . ', ';
                        break;
                }
                $features_txt = mb_substr($features_txt, 0, strlen($features_txt) - 2, 'UTF-8');
                $features_txt .= $this->settings['fdelimeter'];
            }
        }

        $features_txt = mb_substr($features_txt, 0, strlen($features_txt) - strlen($this->settings['fdelimeter']), 'UTF-8');
        $this->product->features = $features_txt;
        return $this;
    }
}
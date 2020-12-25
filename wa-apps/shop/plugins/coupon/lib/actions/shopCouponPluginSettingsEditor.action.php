<?php


class shopCouponPluginSettingsEditorAction extends waViewAction {

    public function execute()
    {
        $id = waRequest::request('id', 0, 'int');

        $m = new shopCouponPluginTemplateModel();
        $gen = $m->getById($id);
        if ($gen) {
            $gen['value'] = (float) $gen['value'];
            if (waRequest::request('delete')) {
                $m->deleteById($id);
                $this->view->assign('deleted', 1);
                return;
            }
        } else if ($id) {
            throw new waException('Generator not found.', 404);
        } else {
            // show form to create new coupon
            $gen = $m->getEmptyRow();
        }

        $is_new = false;

        if (waRequest::post()) {
            $post_gen = waRequest::post('gen');
            if (is_array($post_gen)) {
                $post_gen = array_intersect_key($post_gen, $gen) + array(
                        'type' => '%',
                    );
                if (empty($post_gen['limit'])) {
                    $post_gen['limit'] = null;
                }
                if (!empty($post_gen['value'])) {
                    $post_gen['value'] = (float) str_replace(',', '.', $post_gen['value']);
                }

                if ($post_gen['type'] == '%') {
                    $post_gen['value'] = min(max($post_gen['value'], 0), 100);
                }

                if (empty($post_gen['expire_hours'])) {
                    $post_gen['expire_hours'] = null;
                }

                foreach(array('expire_hours', 'num', 'latin_lowercase', 'latin_uppercase', 'cyr_lowercase', 'cyr_uppercase', 'other') as $k) {
                    if (empty($post_gen[$k])) {
                        $post_gen[$k] = null;
                    }
                }


                if ($id) {
                    $m->updateById($id, $post_gen);
                    $gen = $m->getById($id);
                } else {
                    $post_gen['create_contact_id'] = wa()->getUser()->getId();
                    try {
                        $id = $m->insert($post_gen);
                        $gen = $m->getById($id);
                        $is_new = true;
                    } catch (waDbException $e) {
                        // Duplicate code. Show error in form.
                        $gen = $post_gen + $gen;
                    }
                }
            }
        }

        // Coupon types
        $curm = new shopCurrencyModel();
        $currencies = $curm->getAll('code');
        $types = self::getTypes($currencies);


        $this->view->assign('types', $types);
        $this->view->assign('gen', $gen);
        $this->view->assign('is_new', $is_new);
        $this->view->assign('formatted_value', shopCouponPlugin::formatValue($gen, $currencies));
    }

    public static function getTypes($currencies)
    {
        $result = array(
            '%' => _w('% Discount'),
        );
        foreach($currencies as $c) {
            $info = waCurrency::getInfo($c['code']);
            $result[$c['code']] = $info['sign'].' '.$c['code'];
        }
        $result['$FS'] = _w('Free shipping');
        return $result;
    }
}
<?php

class shopCouponPlugin extends shopPlugin
{

    const CANDIDATES_NUM = 10;
    const MIN_LENGTH = 3;

    /**
     * @param $id
     * @param array $params
     * @return shopCouponPluginCoupon
     */
    public static function gen($id, $params = array())
    {
        $m = new shopCouponModel();
        $tm = new shopCouponPluginTemplateModel();
        if(!$gen = $tm->getById($id)) {
            return new shopCouponPluginCoupon();
        }

        if(!$candidates = self::_candidates($gen)) {
            return new shopCouponPluginCoupon();
        }

        // Кто-то читает код? :)
        // Стоит проверять в цикле, чтоб наверняка?
        //do{
        $exists = $m->select('code')->where('code IN(?)', $candidates)->fetchAll(null, true);
        $candidates = array_diff($candidates, $exists);
        $code = reset($candidates);
        //} while(empty($code));


        $comment = $gen['comment'];
        if(!empty($params['contact_id'])) {
            $contact = new waContact($params['contact_id']);
            if($contact->exists()) {
                $comment .= "\n"._wp('for').' '.$contact->getName();
            }
            $comment = trim($comment);
        }

        try {
            $code = mb_substr($code, 0, 32);
            $coupon = array(
                'code' => $code,
                'type' => $gen['type'],
                'limit' => $gen['limit'],
                'value' => $gen['value'],
                'comment' => $comment,
                'expire_datetime' => $gen['expire_hours'] ? date('Y-m-d H:i:s', time() + $gen['expire_hours'] * 3600) : null,
                'create_datetime' => date('Y-m-d H:i:s'),
                'create_contact_id' => $gen['create_contact_id'],
            );
            $m->insert($coupon);
        } catch(waDbException $e) {
            $coupon = array();
        }

        return new shopCouponPluginCoupon($coupon);
    }


    public static function formatValue($c, $curr = null)
    {
        static $currencies = null;
        if ($currencies === null) {
            if ($curr) {
                $currencies = $curr;
            } else {
                $curm = new shopCurrencyModel();
                $currencies = $curm->getAll('code');
            }
        }

        if ($c['type'] == '$FS') {
            return _wp('Free shipping');
        } else if ($c['type'] === '%') {
            return waCurrency::format('%0', $c['value'], 'USD').'%';
        } else if (!empty($currencies[$c['type']])) {
            return waCurrency::format('%0{s}', $c['value'], $c['type']);
        } else {
            // Coupon of unknown type. Possibly from a plugin?..
            return '';
        }
    }

    protected static function _candidates($gen)
    {
        $charset = '';
        if(!empty($gen['num'])) $charset .= '0987654321';
        if(!empty($gen['latin_lowercase'])) $charset .= 'abcdefghijklmnopqrstuvwxyz';
        if(!empty($gen['latin_uppercase'])) $charset .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        if(!empty($gen['cyr_lowercase'])) $charset .= 'абвгдежзийклмнопрстуфхцчшщъыьэюя';
        if(!empty($gen['cyr_uppercase'])) $charset .= 'АБВГДЕЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ';
        if(!empty($gen['other'])) $charset .= '!@#$%^*()';

        if(!$charset || $gen['length'] < self::MIN_LENGTH) {
            return false;
        }

        $seed = preg_split("//u", $charset, -1, PREG_SPLIT_NO_EMPTY);
        shuffle($seed);
        $seed_l = count($seed) - 1;

        $candidates = array_fill(0, self::CANDIDATES_NUM, $gen['prefix']);

        for($i = 0; $i < self::CANDIDATES_NUM; $i++) {
            for($j = 0; $j < $gen['length']; $j++) {
                $candidates[$i] .= $seed[mt_rand(0, $seed_l)];
            }
        }

        return $candidates;
    }

    public function backendOrders()
    {
        if($this->getSettings('remove_expired')) {
            $m = new shopCouponModel();
            $m->query('DELETE FROM '.$m->getTableName().' WHERE expire_datetime < ? AND used = 0', date('Y-m-d H:i:s'));
        }
        return array();
    }
}

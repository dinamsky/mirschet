<?php


class shopCouponPluginCoupon implements ArrayAccess {

    protected $data = array(
        'code' => '',
        'expire' => null,
        'discount' => null,
    );

    public function __construct($coupon = array())
    {
        waLocale::loadByDomain(array('shop', 'coupon'));
        waSystem::pushActivePlugin('coupon', 'shop');

        $this->data['code'] = ifempty($coupon['code'], '');
        $this->data['expire'] = ifempty($coupon['expire_datetime']);

        $curm = new shopCurrencyModel();
        $currencies = $curm->getAll('code');
        $this->data['discount'] = shopCouponPlugin::formatValue($coupon, $currencies);
        waSystem::popActivePlugin();
    }


    public function __toString()
    {
        return $this->data['code'];
    }

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->__set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->__set($offset, null);
    }

    public function __get($offset)
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }

    public function __set($offset, $value)
    {
    }
}
<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license MIT
 */

namespace SergeR\Webasyst\CdekSDK\API\Order\Response\Type;

use SergeR\CakeUtility\Hash;

class StatusReportPackageItem
{
    /** @var string */
    protected $WareKey;

    /** @var int */
    protected $DelivAmount;

    /** @var int */
    protected $Amount;

    /**
     * @return string
     */
    public function getWareKey()
    {
        return $this->WareKey;
    }

    /**
     * @return int
     */
    public function getDelivAmount()
    {
        return $this->DelivAmount;
    }

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->Amount;
    }

    public static function fromArray(array $values)
    {
        $self = new self();
        $self->WareKey = (string)Hash::get($values, '@WareKey');
        $self->DelivAmount = (int)Hash::get($values, '@DelivAmount');
        $self->Amount = (int)Hash::get($values, '@Amount');

        return $self;
    }
}
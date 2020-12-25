<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license
 */

namespace SergeR\Webasyst\CdekSDK\Type;

use PhpUnitsOfMeasure\PhysicalQuantity\Mass;
use SergeR\Webasyst\CdekSDK\Utils\FillFromArray;
use SergeR\Webasyst\CdekSDK\Utils\XmlFriendlyArray;

class OrderItem implements XmlFriendlyArray
{
    use FillFromArray;

    /** @var int */
    protected $Amount;

    /** @var string */
    protected $WareKey;

    /** @var float */
    protected $Cost;

    /** @var float */
    protected $Payment;

    /** @var string */
    protected $PaymentVATRate;

    /** @var float */
    protected $PaymentVATSum;

    /** @var Mass */
    protected $Weight;

    /** @var string */
    protected $Comment;

    /**
     * @return int
     */
    public function getAmount()
    {
        return (int)$this->Amount;
    }

    /**
     * @param int $Amount
     * @return OrderItem
     */
    public function setAmount($Amount)
    {
        $this->Amount = $Amount;
        return $this;
    }

    /**
     * @return string
     */
    public function getWareKey()
    {
        return (string)$this->WareKey;
    }

    /**
     * @param string $WareKey
     * @return OrderItem
     */
    public function setWareKey($WareKey)
    {
        $this->WareKey = $WareKey;
        return $this;
    }

    /**
     * @return float
     */
    public function getCost()
    {
        return $this->Cost;
    }

    /**
     * @param float $Cost
     * @return OrderItem
     */
    public function setCost($Cost)
    {
        $this->Cost = $Cost;
        return $this;
    }

    /**
     * @return float
     */
    public function getPayment()
    {
        return $this->Payment;
    }

    /**
     * @param float $Payment
     * @return OrderItem
     */
    public function setPayment($Payment)
    {
        $this->Payment = $Payment;
        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentVATRate()
    {
        return $this->PaymentVATRate;
    }

    /**
     * @param string $PaymentVATRate
     * @return OrderItem
     */
    public function setPaymentVATRate($PaymentVATRate)
    {
        $this->PaymentVATRate = $PaymentVATRate;
        return $this;
    }

    /**
     * @return float
     */
    public function getPaymentVATSum()
    {
        return $this->PaymentVATSum;
    }

    /**
     * @param float $PaymentVATSum
     * @return OrderItem
     */
    public function setPaymentVATSum($PaymentVATSum)
    {
        $this->PaymentVATSum = $PaymentVATSum;
        return $this;
    }

    /**
     * @return Mass
     */
    public function getWeight()
    {
        return clone $this->Weight;
    }

    /**
     * @param Mass $Weight
     * @return OrderItem
     */
    public function setWeight(Mass $Weight)
    {
        $this->Weight = $Weight;
        return $this;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->Comment;
    }

    /**
     * @param string $Comment
     * @return OrderItem
     */
    public function setComment($Comment)
    {
        $this->Comment = $Comment;
        return $this;
    }

    public function toXmlArray(array $options = array())
    {
        $options = array_merge(['tag' => 'Item'], $options);
        $tag = $options['tag'];

        $arr = array(
            '@Amount'  => $this->getAmount(),
            '@WareKey' => $this->getWareKey(),
            '@Cost'    => $this->getCost(),
            '@Payment' => $this->getPayment(),
            '@Weight'  => $this->getWeight()->toUnit('g'),
            '@Comment' => $this->getComment()
        );

        if (($arr['@Payment'] > 0) && ($vat = $this->getPaymentVATRate())) {
            $arr['@PaymentVATRate'] = $vat;
            if ($vat !== 'VATX') {
                $arr['@PaymentVATSum'] = $this->getPaymentVATSum();
            }
        }

        if ($tag !== null) {
            $arr = [$tag => $arr];
        }

        return $arr;
    }
}
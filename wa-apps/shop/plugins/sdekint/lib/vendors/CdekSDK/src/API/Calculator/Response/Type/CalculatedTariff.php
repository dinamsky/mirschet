<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2019
 * @license MIT
 */

namespace SergeR\Webasyst\CdekSDK\API\Calculator\Response\Type;

use SergeR\CakeUtility\Hash;

/**
 * Class CalculatedTariff
 * @package SergeR\Webasyst\CdekSDK\API\Calculator\Response\Type
 */
class CalculatedTariff
{
    /** @var int */
    protected $tariffId;

    /** @var bool */
    protected $status;

    protected $price;

    protected $deliveryPeriodMin;

    protected $deliveryPeriodMax;

    protected $priceByCurrency;

    /** @var float|null */
    protected $cashOnDelivery;

    protected $currency;

    protected $percentVAT;

    /** @var CalculatedService[] */
    protected $services = [];

    protected $error = ['code' => 0, 'message' => ''];

    public function __construct(array $tariff = [])
    {
        $this->tariffId = (int)Hash::get($tariff, 'tariffId');
        $this->status = (bool)Hash::get($tariff, 'status');
        if (!$this->status) {
            $this->error = [
                'code'    => (int)Hash::get($tariff, 'result.errors.code'),
                'message' => (string)Hash::get($tariff, 'result.errors.text')
            ];
            return;
        }

        $this->price = Hash::get($tariff, 'result.price', '0');
        $this->deliveryPeriodMin = (int)Hash::get($tariff, 'result.deliveryPeriodMin');
        $this->deliveryPeriodMax = (int)Hash::get($tariff, 'result.deliveryPeriodMax');
        $this->priceByCurrency = (float)Hash::get($tariff, 'result.priceByCurrency');
        $this->currency = (string)Hash::get($tariff, 'result.currency');
        $this->percentVAT = Hash::get($tariff, 'result.percentVAT');
        $this->cashOnDelivery = Hash::get($tariff, 'result.cashOnDelivery');
        foreach ((array)Hash::get($tariff, 'result.services') as $service) {
            $this->services[] = new CalculatedService($service);
        }
    }

    /**
     * @return bool
     */
    public function isStatus()
    {
        return $this->status;
    }

    /**
     * @return int
     */
    public function getTariffId()
    {
        return $this->tariffId;
    }

    /**
     * @return bool
     */
    public function isError()
    {
        return !$this->status;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @return int
     */
    public function getDeliveryPeriodMin()
    {
        return $this->deliveryPeriodMin;
    }

    /**
     * @return int
     */
    public function getDeliveryPeriodMax()
    {
        return $this->deliveryPeriodMax;
    }

    /**
     * @return float
     */
    public function getPriceByCurrency()
    {
        return $this->priceByCurrency;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @return mixed
     */
    public function getPercentVAT()
    {
        return $this->percentVAT;
    }

    /**
     * @return float|null
     */
    public function getCashOnDelivery()
    {
        return $this->cashOnDelivery;
    }


    /**
     * @return CalculatedService[]
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * @return array
     */
    public function getError()
    {
        return $this->error;
    }

    public function toArray()
    {
        if (!$this->isError()) {
            return array(
                'price'             => $this->getPrice(),
                'deliveryPeriodMin' => $this->getDeliveryPeriodMin(),
                'deliveryPeriodMax' => $this->getDeliveryPeriodMax(),
                'tariffId'          => $this->getTariffId(),
                'cashOnDelivery'    => $this->getCashOnDelivery(),
                'priceByCurrency'   => $this->getPriceByCurrency(),
                'currency'          => $this->getCurrency(),
                'percentVAT'        => $this->getPercentVAT(),
                'services'          => array_map(function (CalculatedService $s) {
                    return $s->toArray();
                }, $this->getServices())
            );
        }
        return [];
    }
}
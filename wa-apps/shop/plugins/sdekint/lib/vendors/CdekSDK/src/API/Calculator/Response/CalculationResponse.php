<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license MIT
 */

namespace SergeR\Webasyst\CdekSDK\API\Calculator\Response;

use DateTimeImmutable;
use Exception;
use SergeR\CakeUtility\Hash;

/**
 * Class CalculationResponse
 * @package SergeR\Webasyst\CdekSDK\API\Calculator\Response
 */
class CalculationResponse
{
    protected $errors = [];

    protected $price;

    protected $deliveryPeriodMin;

    protected $deliveryPeriodMax;

    /** @var DateTimeImmutable|null */
    protected $deliveryDateMin;

    /** @var DateTimeImmutable|null */
    protected $deliveryDateMax;

    protected $tariffId;

    protected $cashOnDelivery;

    protected $percentVAT;

    protected $priceByCurrency;

    protected $currency;

    protected $services = [];

    /**
     * @param array $response
     * @return CalculationResponse
     */
    public static function fromArray(array $response)
    {
        $self = new self();
        $self->errors = (array)Hash::get($response, 'error');
        if (!empty($self->errors)) {
            return $self;
        }

        $self->price = (float)Hash::get($response, 'result.price');
        $self->deliveryPeriodMin = (int)Hash::get($response, 'result.deliveryPeriodMin');
        $self->deliveryPeriodMax = (int)Hash::get($response, 'result.deliveryPeriodMax');

        try {
            $date = Hash::get($response, 'result.deliveryDateMin');
            if ($date) {
                $self->deliveryDateMin = new DateTimeImmutable($date);
            }
        } catch (Exception $e) {
            $self->deliveryDateMin = null;
        }
        try {
            $date = Hash::get($response, 'result.deliveryDateMax');
            if ($date) {
                $self->deliveryDateMax = new DateTimeImmutable($date);
            }
        } catch (Exception $e) {
            $self->deliveryDateMax = null;
        }

        $self->tariffId = (int)Hash::get($response, 'result.tariffId');
        $cod = Hash::get($response, 'result.cashOnDelivery');
        if (($cod !== null) && ($cod !== '')) {
            $self->cashOnDelivery = (float)$cod;
        } else {
            $self->cashOnDelivery = null;
        }
        $percent = Hash::get($response, 'result.percentVAT');
        if (($percent !== null) && ($percent !== '')) {
            $self->percentVAT = (float)$percent;
        } else {
            $self->percentVAT = null;
        }
        $price = Hash::get($response, 'result.priceByCurrency');
        if (($price !== null) && ($price !== '')) {
            $self->priceByCurrency = (float)$price;
        } else {
            $self->priceByCurrency = null;
        }
        $self->currency = (string)Hash::get($response, 'result.currency');
        $self->services = (array)Hash::get($response, 'result.services');

        return $self;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @return mixed
     */
    public function getDeliveryPeriodMin()
    {
        return $this->deliveryPeriodMin;
    }

    /**
     * @return mixed
     */
    public function getDeliveryPeriodMax()
    {
        return $this->deliveryPeriodMax;
    }

    /**
     * @return null|DateTimeImmutable
     */
    public function getDeliveryDateMin()
    {
        return $this->deliveryDateMin;
    }

    /**
     * @return null|DateTimeImmutable
     */
    public function getDeliveryDateMax()
    {
        return $this->deliveryDateMax;
    }

    /**
     * @return mixed
     */
    public function getTariffId()
    {
        return $this->tariffId;
    }

    /**
     * @return mixed
     */
    public function getCashOnDelivery()
    {
        return $this->cashOnDelivery;
    }

    /**
     * @return mixed
     */
    public function getPercentVAT()
    {
        return $this->percentVAT;
    }

    /**
     * @return mixed
     */
    public function getPriceByCurrency()
    {
        return $this->priceByCurrency;
    }

    /**
     * @return mixed
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @return array
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return bool
     */
    public function isError()
    {
        return !empty($this->errors);
    }

    public function toArray()
    {
        if (!$this->isError()) {
            return array(
                'price'             => $this->getPrice(),
                'deliveryPeriodMin' => $this->getDeliveryPeriodMin(),
                'deliveryPeriodMax' => $this->getDeliveryPeriodMax(),
                'deliveryDateMin'   => ($this->getDeliveryDateMin() === null ? null : $this->getDeliveryDateMin()->format('Y-m-d')),
                'deliveryDateMax'   => ($this->getDeliveryDateMax() === null ? null : $this->getDeliveryDateMax()->format('Y-m-d')),
                'tariffId'          => $this->getTariffId(),
                'cashOnDelivery'    => $this->getCashOnDelivery(),
                'priceByCurrency'   => $this->getPriceByCurrency(),
                'currency'          => $this->getCurrency(),
                'percentVAT'        => $this->getPercentVAT(),
                'services'          => $this->getServices()
            );
        }
        return [];
    }
}
<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2019
 * @license MIT
 */

namespace SergeR\Webasyst\CdekSDK\API\Calculator\Response\Type;

use SergeR\CakeUtility\Hash;

/**
 * Class CalculatedService
 * @package SergeR\Webasyst\CdekSDK\API\Calculator\Response\Type
 */
class CalculatedService
{
    protected $id;

    protected $title;

    protected $price;

    protected $rate;

    public function __construct(array $service = [])
    {
        $this->id = (int)Hash::get($service, 'id');
        $this->title = (string)Hash::get($service, 'title');
        $this->price = (float)Hash::get($service, 'price');
        $this->rate = (float)Hash::get($service, 'rate');
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @return float
     */
    public function getRate()
    {
        return $this->rate;
    }

    public function toArray()
    {
        return [
            'id'    => $this->getId(),
            'price' => $this->getPrice(),
            'title' => $this->getTitle(),
            'rate'  => $this->getRate()
        ];
    }
}
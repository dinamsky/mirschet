<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license
 */

namespace SergeR\Webasyst\CdekSDK\Type;

use SergeR\Webasyst\CdekSDK\Utils\FillFromArray;
use SergeR\Webasyst\CdekSDK\Utils\XmlFriendlyArray;
use SergeR\CakeUtility\Hash;

class OrderSender implements XmlFriendlyArray
{
    use FillFromArray;

    /** @var string */
    protected $Company;

    /** @var string */
    protected $Name;

    /** @var StreetAddress */
    protected $Address;

    /** @var string */
    protected $Phone;

    public function __construct()
    {
        $this->Address = new StreetAddress();
    }

    /**
     * @return string
     */
    public function getCompany()
    {
        return $this->Company;
    }

    /**
     * @param string $Company
     * @return OrderSender
     */
    public function setCompany($Company)
    {
        $this->Company = $Company;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->Name;
    }

    /**
     * @param string $Name
     * @return OrderSender
     */
    public function setName($Name)
    {
        $this->Name = $Name;
        return $this;
    }

    /**
     * @return StreetAddress
     */
    public function getAddress()
    {
        return $this->Address;
    }

    /**
     * @param StreetAddress $Address
     * @return OrderSender
     */
    public function setAddress(StreetAddress $Address)
    {
        $this->Address = clone $Address;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->Phone;
    }

    /**
     * @param string $Phone
     * @return OrderSender
     */
    public function setPhone($Phone)
    {
        $this->Phone = $Phone;
        return $this;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return !$this->Company && !$this->Name && !$this->Phone && $this->Address->isEmpty();
    }

    /**
     * @param array $options
     * @return array
     */
    public function toXmlArray(array $options = [])
    {
        $options = array_merge(['tag' => 'Sender', 'address_tag' => 'Address'], $options);
        $tag = Hash::get($options, 'tag');
        $_address_options = ['tag' => $options['address_tag']];

        $arr = array_filter(array(
            '@Company' => $this->getCompany(),
            '@Name'    => $this->getName(),
            '@Phone'   => $this->getPhone()
        ));

        if (!$this->Address->isEmpty()) {
            $arr += $this->Address->toXmlArray($_address_options);
        }

        if ($tag !== null) {
            $arr = [$tag => $arr];
        }

        return $arr;
    }
}
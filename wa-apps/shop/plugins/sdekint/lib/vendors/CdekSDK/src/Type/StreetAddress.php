<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license MIT
 */

namespace SergeR\Webasyst\CdekSDK\Type;

use SergeR\Webasyst\CdekSDK\Utils\FillFromArray;
use SergeR\Webasyst\CdekSDK\Utils\XmlFriendlyArray;
use SergeR\CakeUtility\Hash;

class StreetAddress implements XmlFriendlyArray
{
    use FillFromArray;

    /** @var string */
    protected $Street;

    /** @var string */
    protected $House;

    /** @var string */
    protected $Flat;

    /**
     * @return string
     */
    public function getStreet()
    {
        return $this->Street;
    }

    /**
     * @param string $Street
     * @return StreetAddress
     */
    public function setStreet($Street)
    {
        $this->Street = $Street;
        return $this;
    }

    /**
     * @return string
     */
    public function getHouse()
    {
        return $this->House;
    }

    /**
     * @param string $House
     * @return StreetAddress
     */
    public function setHouse($House)
    {
        $this->House = $House;
        return $this;
    }

    /**
     * @return string
     */
    public function getFlat()
    {
        return $this->Flat;
    }

    /**
     * @param string $Flat
     * @return StreetAddress
     */
    public function setFlat($Flat)
    {
        $this->Flat = $Flat;
        return $this;
    }

    public function isEmpty()
    {
        return empty($this->Street) && empty($this->House) && empty($this->Flat);
    }

    public function toXmlArray(array $options = [])
    {
        $options = array_merge(['tag' => 'Address'], $options);
        $tag = Hash::get($options, 'tag');
        $arr = array_filter(array('@Street' => $this->Street, '@House' => $this->House));

        if ($this->Flat) {
            $arr['@Flat'] = $this->Flat;
        }

        if ($tag !== null) {
            $arr = [$tag => $arr];
        }

        return $arr;
    }
}
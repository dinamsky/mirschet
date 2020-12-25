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

class CdekOrder implements XmlFriendlyArray
{
    use FillFromArray;

    /** @var string|null */
    protected $DispatchNumber;

    /** @var string|null */
    protected $Number;

    /** @var \DateTimeImmutable|null */
    protected $Date;

    /**
     * @return string|null
     */
    public function getDispatchNumber()
    {
        return $this->DispatchNumber;
    }

    /**
     * @param string $DispatchNumber
     * @return CdekOrder
     */
    public function setDispatchNumber($DispatchNumber)
    {
        $this->DispatchNumber = $DispatchNumber;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNumber()
    {
        return $this->Number;
    }

    /**
     * @param string $Number
     * @return CdekOrder
     */
    public function setNumber($Number)
    {
        $this->Number = $Number;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getDate()
    {
        return $this->Date;
    }

    /**
     * @param \DateTimeImmutable|string|null $Date
     * @return CdekOrder
     */
    public function setDate($Date)
    {
        if (!($Date instanceof \DateTimeImmutable)) {
            try {
                $Date = new \DateTimeImmutable($Date === null ? 'now' : (string)$Date);
            } catch (\Exception $e) {
                $Date = null;
            }
        }
        $this->Date = $Date;
        return $this;
    }

    public function isValid()
    {
        return ($this->DispatchNumber !== null) || (($this->Number !== null) && ($this->Date instanceof \DateTimeImmutable));
    }

    /**
     * @param array $options
     * @return array
     */
    public function toXmlArray(array $options = [])
    {
        $options = array_merge(['tag' => 'Order'], $options);

        if ($this->getDispatchNumber() !== null) {
            $arr = ['@DispatchNumber' => $this->getDispatchNumber()];
        } else {
            if (($this->getNumber() === null) || ($this->getDate() === null)) {
                throw new \InvalidArgumentException('DispatchNumber or Number with Date required');
            }
            $arr = ['@Number' => $this->getNumber(), '@Date' => $this->getDate()->format('Y-m-d')];
        }

        $tag = Hash::get($options, 'tag');
        if ($tag !== null) {
            $arr = [$tag => $arr];
        }

        return $arr;
    }
}
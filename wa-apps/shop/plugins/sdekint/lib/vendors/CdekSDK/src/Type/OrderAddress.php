<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license MIT
 */

namespace SergeR\Webasyst\CdekSDK\Type;

use SergeR\CakeUtility\Hash;

class OrderAddress extends StreetAddress
{
    /** @var string */
    protected $PvzCode;

    /**
     * @return string
     */
    public function getPvzCode()
    {
        return $this->PvzCode;
    }

    /**
     * @param string $PvzCode
     * @return OrderAddress
     */
    public function setPvzCode($PvzCode)
    {
        $this->PvzCode = $PvzCode;
        return $this;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return parent::isEmpty() && empty($this->PvzCode);
    }

    /**
     * @param array $options
     * @return array
     */
    public function toXmlArray(array $options=[])
    {
        if($this->PvzCode) {
            $options = array_merge(['tag'=>'Address'], $options);
            $tag = Hash::get($options, 'tag');
            $arr = ['@PvzCode' => $this->PvzCode];
            if($tag !== null) {
                $arr = [$tag => $arr];
            }
            return $arr;
        }

        return parent::toXmlArray($options);
    }


}
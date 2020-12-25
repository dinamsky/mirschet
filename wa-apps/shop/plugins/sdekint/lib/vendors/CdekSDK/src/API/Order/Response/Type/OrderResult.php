<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license MIT
 */

namespace SergeR\Webasyst\CdekSDK\API\Order\Response\Type;

use SergeR\CakeUtility\Hash;

/**
 * Class OrderResult
 * @package SergeR\Webasyst\CdekSDK\API\Order\Response\Type
 */
class OrderResult extends CommonOrderResult
{

    /** @var string|null */
    protected $DispatchNumber;

    /**
     * @return string|null
     */
    public function getDispatchNumber()
    {
        return $this->DispatchNumber;
    }

    /**
     * @param array $data
     */
    protected function _loadFromArray(array $data)
    {
        parent::_loadFromArray($data);
        $this->DispatchNumber = Hash::get($data, '@DispatchNumber');
        if ($this->isError()) {
            $this->ErrorMsg = $this->Msg;
        }
    }

    /**
     * @param array $xml_array
     * @return OrderResult
     */
    public static function fromXmlArray(array $xml_array)
    {
        $obj = new self();
        $obj->_loadFromArray($xml_array);
        return $obj;
    }
}
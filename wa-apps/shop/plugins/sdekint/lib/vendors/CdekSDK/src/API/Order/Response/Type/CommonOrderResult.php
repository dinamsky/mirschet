<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license MIT
 */

namespace SergeR\Webasyst\CdekSDK\API\Order\Response\Type;


use SergeR\Webasyst\CdekSDK\API\Order\Contracts\ErrorResponse;
use SergeR\Webasyst\CdekSDK\API\Order\Contracts\ErrorResponseTrait;
use SergeR\CakeUtility\Hash;

/**
 * Class CommonOrderResult
 * @package SergeR\Webasyst\CdekSDK\API\Order\Response\Type
 */
class CommonOrderResult implements ErrorResponse
{
    use ErrorResponseTrait;

    /** @var string|null */
    protected $Number;

    /** @var null|string */
    protected $Msg;

    /**
     * @return string|null
     */
    public function getNumber()
    {
        return $this->Number;
    }

    /**
     * @return string|null
     */
    public function getMsg()
    {
        return $this->Msg;
    }

    /**
     * @param array $data
     */
    protected function _loadFromArray(array $data)
    {
        $this->Number = Hash::get($data, '@Number');
        $this->ErrorCode = Hash::get($data, '@ErrorCode');
        $this->Msg = Hash::get($data, '@Msg');
        if ($this->isError()) {
            $this->ErrorMsg = $this->Msg;
        }
    }

    /**
     * @param array $xml_array
     * @return CommonOrderResult
     */
    public static function fromXmlArray(array $xml_array)
    {
        $obj = new self();
        $obj->_loadFromArray($xml_array);

        return $obj;
    }
}
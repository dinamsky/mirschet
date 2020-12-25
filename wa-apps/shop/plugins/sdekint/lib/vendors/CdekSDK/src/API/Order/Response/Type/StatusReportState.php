<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license MIT
 */

namespace SergeR\Webasyst\CdekSDK\API\Order\Response\Type;

use Exception;
use SergeR\CakeUtility\Hash;

class StatusReportState extends CommonStatusReportState
{
    /** @var int */
    protected $CityCode;

    /** @var string */
    protected $CityName;

    /**
     * @return int
     */
    public function getCityCode()
    {
        return $this->CityCode;
    }

    /**
     * @return string
     */
    public function getCityName()
    {
        return $this->CityName;
    }

    /**
     * @param array $values
     * @throws Exception
     */
    protected function _loadArray(array $values)
    {
        parent::_loadArray($values);
        $this->CityCode = (int)Hash::get($values, '@CityCode');
        $this->CityName = (string)Hash::get($values, '@CityName');
    }
    /**
     * @param array $xml_array
     * @return StatusReportState
     * @throws Exception
     */
    public static function fromArray(array $xml_array)
    {
        $self = new self();
        $self->_loadArray($xml_array);

        return $self;
    }
}
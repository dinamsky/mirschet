<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license MIT
 */

namespace SergeR\Webasyst\CdekSDK\API\Order\Response\Type;


use DateTimeImmutable;
use Exception;
use SergeR\CakeUtility\Hash;

class StatusReportCallFail extends AbstractStatusReportEvent
{
    /** @var int */
    protected $ReasonCode;

    /** @var string */
    protected $ReasonDescription;

    /**
     * @return int
     */
    public function getReasonCode()
    {
        return $this->ReasonCode;
    }

    /**
     * @return string
     */
    public function getReasonDescription()
    {
        return $this->ReasonDescription;
    }

    /**
     * @param array $xml_array
     * @return StatusReportCallFail
     * @throws Exception
     */
    public static function fromArray(array $xml_array)
    {
        $self = new self();
        $date = Hash::get($xml_array, '@Date');

        if(!$date) {
            throw new Exception('Date is not set');
        }

        $self->Date = new DateTimeImmutable($date);
        $self->ReasonCode = (int)Hash::get($xml_array, '@ReasonCode');
        $self->ReasonDescription = (string)Hash::get($xml_array, '@ReasonDescription');

        return $self;
    }
}
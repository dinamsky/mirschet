<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license MIT
 */

namespace SergeR\Webasyst\CdekSDK\API\Order\Response\Type;

use SergeR\CakeUtility\Hash;

class StatusReportAttempt
{
    /** @var int */
    protected $ID;

    /** @var int */
    protected $ScheduleCode;

    /** @var string */
    protected $ScheduleDescription;

    /**
     * @return int
     */
    public function getID()
    {
        return $this->ID;
    }

    /**
     * @return int
     */
    public function getScheduleCode()
    {
        return $this->ScheduleCode;
    }

    /**
     * @return string
     */
    public function getScheduleDescription()
    {
        return $this->ScheduleDescription;
    }

    /**
     * @param array $xml_array
     * @return StatusReportAttempt
     */
    public static function fromXmlArray(array $xml_array)
    {
        $self = new self();
        $self->ID = (int)Hash::get($xml_array, '@ID');
        $self->ScheduleCode = (int)Hash::get($xml_array, '@ScheduleCode');
        $self->ScheduleDescription = (string)Hash::get($xml_array, '@ScheduleDescription');

        return $self;
    }
}
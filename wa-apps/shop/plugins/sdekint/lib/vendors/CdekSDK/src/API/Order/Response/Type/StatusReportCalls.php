<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license
 */

namespace SergeR\Webasyst\CdekSDK\API\Order\Response\Type;

use SergeR\CakeUtility\Hash;

/**
 * Class StatusReportCalls
 * @package SergeR\Webasyst\CdekSDK\API\Order\Response\Type
 */
class StatusReportCalls
{
    /** @var StatusReportCallGoodCollection */
    protected $Good;

    /** @var StatusReportCallFailCollection */
    protected $Fail;

    /** @var StatusReportCallFailCollection */
    protected $Delay;

    public function __construct()
    {
        $this->Good = new StatusReportCallGoodCollection;
        $this->Fail = new StatusReportCallFailCollection;
        $this->Delay = new StatusReportCallDelayCollection;
    }

    /**
     * @param array $xml_array
     * @return StatusReportCalls
     */
    public static function fromArray(array $xml_array)
    {
        $self = new self();
        $self->Good = StatusReportCallGoodCollection::fromArray((array)Hash::get($xml_array, 'CallGood'));
        $self->Fail = StatusReportCallFailCollection::fromArray((array)Hash::get($xml_array, 'CallFail'));
        $self->Delay = StatusReportCallDelayCollection::fromArray((array)Hash::get($xml_array, 'CallDelay'));

        return $self;
    }

    /**
     * @return StatusReportCallGoodCollection
     */
    public function getGood()
    {
        return $this->Good;
    }

    /**
     * @return StatusReportCallFailCollection
     */
    public function getFail()
    {
        return $this->Fail;
    }

    /**
     * @return StatusReportCallFailCollection
     */
    public function getDelay()
    {
        return $this->Delay;
    }
}
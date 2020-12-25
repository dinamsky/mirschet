<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license MIT
 */

namespace SergeR\Webasyst\CdekSDK\API\Order\Response\Type;

use Exception;
use SergeR\CakeUtility\Hash;

/**
 * Class StatusReportStatus
 * @package SergeR\Webasyst\CdekSDK\API\Order\Response\Type
 */
class StatusReportStatus extends StatusReportState
{
    /** @var StatusReportStatusStatesCollection */
    protected $State;

    /**
     * StatusReportStatus constructor.
     */
    public function __construct()
    {
        $this->State = new StatusReportStatusStatesCollection;
    }

    /**
     * @return StatusReportStatusStatesCollection
     */
    public function getState()
    {
        return $this->State;
    }

    /**
     * @param array $values
     * @throws Exception
     */
    protected function _loadArray(array $values)
    {
        parent::_loadArray($values);
        $this->State = StatusReportStatusStatesCollection::fromArray((array)Hash::get($values, 'State'));
    }

    /**
     * @param array $xml_array
     * @return StatusReportState|StatusReportStatus
     * @throws Exception
     */
    public static function fromArray(array $xml_array)
    {
        $self = new self();
        $self->_loadArray($xml_array);

        return $self;
    }
}
<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license
 */

namespace SergeR\Webasyst\CdekSDK\API\Order\Response\Type;

use Exception;
use SergeR\CakeUtility\Hash;

/**
 * Class StatusReportDelayReasonState
 * @package SergeR\Webasyst\CdekSDK\API\Order\Response\Type
 */
class StatusReportDelayReasonState extends CommonStatusReportState
{
    /** @var CommonStatusReportStatesCollection */
    protected $State;

    /**
     * StatusReportDelayReasonState constructor.
     */
    public function __construct()
    {
        $this->State = new CommonStatusReportStatesCollection();
    }

    /**
     * @return CommonStatusReportStatesCollection
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
        $this->State = CommonStatusReportStatesCollection::fromArray((array)Hash::get($values, 'State'));
    }

    /**
     * @param array $values
     * @return CommonStatusReportState|StatusReportDelayReasonState|null
     * @throws Exception
     */
    public static function fromArray(array $values)
    {
        $self = new self();
        $self->_loadArray($values);

        return $self;
    }
}
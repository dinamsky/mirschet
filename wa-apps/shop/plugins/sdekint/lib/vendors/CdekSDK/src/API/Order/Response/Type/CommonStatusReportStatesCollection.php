<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license
 */

namespace SergeR\Webasyst\CdekSDK\API\Order\Response\Type;

/**
 * Class CommonStatusReportStatesCollection
 * @package SergeR\Webasyst\CdekSDK\API\Order\Response\Type
 */
class CommonStatusReportStatesCollection extends AbstractStatusReportEventsCollection
{
    /** @var CommonStatusReportState[] */
    protected $_items;

    /**
     * CommonStatusReportStatesCollection constructor.
     * @param CommonStatusReportState ...$states
     */
    public function __construct(CommonStatusReportState ...$states)
    {
        $this->_items = $states;
    }

    /**
     * @param array $values
     * @return CommonStatusReportStatesCollection
     */
    public static function fromArray(array $values)
    {
        return new self(...array_filter(array_map(function ($v) {
            return CommonStatusReportState::fromArray($v);
        }, $values)));
    }

}
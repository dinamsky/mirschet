<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018-2019
 * @license
 */

namespace SergeR\Webasyst\CdekSDK\API\Order\Response\Type;

use SergeR\CakeUtility\Hash;

/**
 * Class StatusReportStatusStatesCollection
 * @package SergeR\Webasyst\CdekSDK\API\Order\Response\Type
 */
class StatusReportStatusStatesCollection extends AbstractStatusReportEventsCollection
{
    /** @var StatusReportState[] */
    protected $_items;

    /**
     * StatusReportStatusStatesCollection constructor.
     * @param StatusReportState[] $states
     */
    public function __construct(StatusReportState ...$states)
    {
        $this->_items = $states;
    }

    /**
     * @param array $items
     * @return StatusReportStatusStatesCollection
     */
    public static function fromArray(array $items)
    {
        if ($items && !Hash::numeric(array_keys($items))) {
            $items = [$items];
        }
        return new self(...array_filter(array_map(function ($item) {
            try {
                return $item && is_array($item) ? StatusReportState::fromArray($item) : null;
            } catch (\Exception $e) {
                return null;
            }
        }, $items)));
    }
}
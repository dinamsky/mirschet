<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license
 */

namespace SergeR\Webasyst\CdekSDK\API\Order\Response\Type;

use SergeR\CakeUtility\Hash;
use SergeR\Webasyst\CdekSDK\Utils\SimpleArrayIteratorAggregate;

/**
 * Class StatusReportCallFailCollection
 * @package SergeR\Webasyst\CdekSDK\API\Order\Response\Type
 */
class StatusReportCallFailCollection extends AbstractStatusReportEventsCollection
{
    use SimpleArrayIteratorAggregate;

    /** @var StatusReportCallFail[] */
    protected $_items;

    /**
     * StatusReportCallFailCollection constructor.
     * @param StatusReportCallFail ...$callFails
     */
    public function __construct(StatusReportCallFail ...$callFails)
    {
        $this->_items = $callFails;
    }

    /**
     * @param array $xml_array
     * @return StatusReportCallFailCollection
     */
    public static function fromArray(array $xml_array)
    {
        $obj = new self();
        $failed_calls = (array)Hash::get($xml_array, 'Fail');
        if(!Hash::numeric(array_keys($failed_calls))) {
            $failed_calls = [$failed_calls];
        }
        foreach ($failed_calls as $item) {
            try {
                $item = StatusReportCallFail::fromArray($item);
                $obj->_items[] = $item;
            } catch (\Exception $e) {
                continue;
            }
        }

        return $obj->sortByDate();
    }
}
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
 * Class StatusReportCallDelayCollection
 * @package SergeR\Webasyst\CdekSDK\API\Order\Response\Type
 */
class StatusReportCallDelayCollection extends AbstractStatusReportEventsCollection
{
    use SimpleArrayIteratorAggregate;

    /** @var StatusReportCallDelay[] */
    protected $_items;

    /**
     * StatusReportCallDelayCollection constructor.
     * @param StatusReportCallDelay ...$callDelays
     */
    public function __construct(StatusReportCallDelay ...$callDelays)
    {
        $this->_items = $callDelays;
    }

    /**
     * @param array $xml_array
     * @return StatusReportCallDelayCollection
     */
    public static function fromArray(array $xml_array)
    {
        $obj = new self();
        $calls = (array)Hash::get($xml_array, 'Delay');
        if ($calls && !Hash::numeric(array_keys($calls))) {
            $calls = [$calls];
        }
        foreach ($calls as $item) {
            try {
                $item = StatusReportCallDelay::fromArray($item);
                $obj->_items[] = $item;
            } catch (\Exception $e) {
                continue;
            }
        }

        return $obj->sortByDate();
    }

}
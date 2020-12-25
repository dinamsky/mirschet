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
 * Class StatusReportCallGoodCollection
 * @package SergeR\Webasyst\CdekSDK\API\Order\Response\Type
 */
class StatusReportCallGoodCollection extends AbstractStatusReportEventsCollection
{
    use SimpleArrayIteratorAggregate;

    /** @var StatusReportCallGood[] */
    protected $_items;

    /**
     * StatusReportCallGoodCollection constructor.
     * @param StatusReportCallGood ...$items
     */
    public function __construct(StatusReportCallGood ...$items)
    {
        $this->_items = $items;
    }

    /**
     * @param StatusReportCallGood $item
     * @return $this
     */
    public function add(StatusReportCallGood $item)
    {
        $this->_items[] = clone $item;
        return $this;
    }

    /**
     * @param array $xml_array
     * @return StatusReportCallGoodCollection
     */
    public static function fromArray(array $xml_array)
    {
        $obj = new self();
        $calls = (array)Hash::get($xml_array, 'Good');
        if($calls && !Hash::numeric(array_keys($calls))) {
            $calls = [$calls];
        }
        foreach ($calls as $item) {
            try {
                $item = StatusReportCallGood::fromArray($item);
                $obj->_items[] = $item;
            } catch (\Exception $e) {
                continue;
            }
        }

        return $obj->sortByDate();
    }
}
<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license
 */

namespace SergeR\Webasyst\CdekSDK\API\Order\Response\Type;

use Exception;
use SergeR\Webasyst\CdekSDK\Utils\SimpleArrayIteratorAggregate;

/**
 * Class StatusReportOrderCollection
 * @package SergeR\Webasyst\CdekSDK\API\Order\Response\Type
 */
class StatusReportOrderCollection
{
    use SimpleArrayIteratorAggregate;

    /** @var StatusReportOrder[] */
    protected $_items = [];

    /**
     * StatusReportOrderCollection constructor.
     * @param StatusReportOrder[] $items
     */
    public function __construct(StatusReportOrder ...$items)
    {
        $this->_items = $items;
    }

    /**
     * @param $dispatch_number
     * @return StatusReportOrder|null
     */
    public function findByDispatchNumber($dispatch_number)
    {
        foreach ($this->_items as $item) {
            if ($item->getDispatchNumber() === $dispatch_number) {
                return clone $item;
            }
        }

        return null;
    }

    /**
     * @param array $xml_array
     * @return StatusReportOrderCollection
     */
    public static function fromArray(array $xml_array)
    {
        return new self(...array_filter(array_map(function ($item) {
            // workaround https://bugs.php.net/bug.php?id=55416
            try {
                return is_array($item) ? StatusReportOrder::fromArray($item) : false;
            } catch (Exception $e) {
                //todo log?
                return false;
            }
        }, $xml_array)));
    }
}
<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license
 */

namespace SergeR\Webasyst\CdekSDK\API\Order\Response\Type;

use SergeR\Webasyst\CdekSDK\Utils\SimpleArrayIteratorAggregate;

/**
 * Class OrderResultCollection
 * @package SergeR\Webasyst\CdekSDK\API\Order\Response\Type
 */
class OrderResultCollection implements \IteratorAggregate
{
    use SimpleArrayIteratorAggregate;

    /** @var OrderResult[] */
    private $_items = [];

    /**
     * OrderResultCollection constructor.
     * @param OrderResult ...$results
     */
    public function __construct(OrderResult ...$results)
    {
        $this->_items = $results;
    }

    /**
     * @param $dispatch_number
     */
    public function findFirstByDispatchNumber($dispatch_number)
    {

    }

    /**
     * @param $number
     * @return OrderResult|null
     */
    public function findFirstByNumber($number)
    {
        $result = null;

        foreach ($this->_items as $r) {
            if ($number == $r->getNumber()) {
                $result = clone $r;
                break;
            }
        }

        return $result;
    }

    /**
     * @param array $items
     * @return OrderResultCollection
     */
    public static function fromXmlArray(array $items)
    {
        $items = array_map(function ($r) {
            return OrderResult::fromXmlArray($r);
        }, $items);

        return new self(...array_values($items));
    }
}
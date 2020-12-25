<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license
 */

namespace SergeR\Webasyst\CdekSDK\API\Order\Response\Type;

use SergeR\Webasyst\CdekSDK\Utils\SimpleArrayIteratorAggregate;

/**
 * Class CallResultCollection
 * @package SergeR\Webasyst\CdekSDK\API\Order\Response\Type
 */
class CommonOrdersResultCollection implements \IteratorAggregate, \Countable
{
    use SimpleArrayIteratorAggregate;

    /** @var CommonOrderResult[] */
    private $_items = [];

    /**
     * CallResultCollection constructor.
     * @param CommonOrderResult[] $results
     */
    public function __construct(CommonOrderResult ...$results)
    {
        $this->_items = $results;
    }

    /**
     * @param string $number
     * @return CommonOrderResult|null
     */
    public function findByNumber($number)
    {
        $order = null;
        /** @var CommonOrderResult $item */
        foreach ($this as $item) {
            if ($item->getNumber() == $number) {
                $order = $item;
                break;
            }
        }

        return $order;
    }

    /**
     * @param array $items
     * @return CommonOrdersResultCollection
     */
    public static function fromXmlArray(array $items)
    {
        $items = array_map(function ($r) {
            return CommonOrderResult::fromXmlArray($r);
        }, $items);

        return new self(...array_values($items));
    }
}
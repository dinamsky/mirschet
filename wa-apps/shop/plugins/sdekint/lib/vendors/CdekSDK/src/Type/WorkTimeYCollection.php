<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license MIT
 */

namespace SergeR\Webasyst\CdekSDK\Type;

use SergeR\Webasyst\CdekSDK\Utils\SimpleArrayIteratorAggregate;

/**
 * Class WorkTimeYCollection
 * @package SergeR\Webasyst\CdekSDK\Type
 */
class WorkTimeYCollection implements \IteratorAggregate
{
    use SimpleArrayIteratorAggregate;

    /** @var WorkTimeY[] */
    protected $_items = [];

    /**
     * WorkTimeYCollection constructor.
     * @param WorkTimeY[] $items
     */
    public function __construct(WorkTimeY ...$items)
    {
        $this->_items = array_map(function ($_item) {
            return clone $_item;
        }, $items);
    }

    /**
     * @param array $items
     * @return WorkTimeYCollection
     */
    public static function fromXmlArray(array $items)
    {
        $items = array_filter(array_map(function ($arr) {
            try {
                $item = WorkTimeY::fromArray($arr);
            } catch (\Exception $e) {
                $item = null;
            }
            return $item;
        }, $items));

        return new self(...$items);
    }
}
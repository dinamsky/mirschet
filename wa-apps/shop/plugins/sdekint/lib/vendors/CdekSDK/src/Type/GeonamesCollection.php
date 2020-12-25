<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2019
 * @license Webasyst
 */

namespace SergeR\Webasyst\CdekSDK\Type;

use SergeR\Webasyst\CdekSDK\Utils\SimpleArrayIteratorAggregate;

/**
 * Класс коллекции городов, возвращаемый по запросу автоподсказки города (api кальткулятора)
 *
 * Class GeonamesCollection
 * @package SergeR\Webasyst\CdekSDK\Type
 */
class GeonamesCollection implements \IteratorAggregate, \Countable
{
    use SimpleArrayIteratorAggregate;

    /** @var Geoname[] */
    protected $_items = [];

    /**
     * GeonamesCollection constructor.
     * @param Geoname[] $items
     */
    public function __construct(Geoname ...$items)
    {
        $this->_items = $items;
    }

    /**
     * @param Geoname $item
     */
    public function add(Geoname $item)
    {
        $this->_items[] = clone $item;
    }

    /**
     * @param callable|null $fn
     * @return array
     */
    public function getList(callable $fn = null)
    {
        if ($fn === null) {
            $fn = function (Geoname $item) {
                return $item->getId() ? [$item->getId() => $item->getName()] : null;
            };
        }

        $items = $this->_items;
        return array_map($fn, $items);
    }
}
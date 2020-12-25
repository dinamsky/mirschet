<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license MIT
 */

namespace SergeR\Webasyst\CdekSDK\Type;

use SergeR\Webasyst\CdekSDK\Utils\CollectionToXmlArray;
use SergeR\Webasyst\CdekSDK\Utils\FillFromArray;
use SergeR\Webasyst\CdekSDK\Utils\SimpleArrayIteratorAggregate;
use SergeR\Webasyst\CdekSDK\Utils\XmlFriendlyArray;

/**
 * Class CdekOrderCollection
 * @package SergeR\Webasyst\CdekSDK\Type
 */
class CdekOrderCollection implements XmlFriendlyArray, \IteratorAggregate
{
    use FillFromArray, SimpleArrayIteratorAggregate, CollectionToXmlArray;

    /** @var CdekOrder[] */
    protected $_items = [];

    /** @var string */
    protected $_xml_tag = 'Order';

    /**
     * CdekOrderCollection constructor.
     * @param CdekOrder[] $items
     */
    public function __construct(CdekOrder ...$items)
    {
        $this->_items = array_filter(array_map(function ($_item) {
            /** @var CdekOrder $_item */
            return $_item->isValid() ? clone $_item : null;
        }, $items));
    }

    /**
     * @param CdekOrder $item
     * @return $this
     */
    public function add(CdekOrder $item)
    {
        $this->_items[] = clone $item;
        return $this;
    }
}
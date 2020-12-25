<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license MIT
 */

namespace SergeR\Webasyst\CdekSDK\Type;

use SergeR\Webasyst\CdekSDK\Utils\CollectionToXmlArray;
use SergeR\Webasyst\CdekSDK\Utils\SimpleArrayIteratorAggregate;
use SergeR\Webasyst\CdekSDK\Utils\XmlFriendlyArray;

/**
 * Class OrderItemCollection
 * @package SergeR\Webasyst\CdekSDK\Type
 */
class OrderItemCollection implements XmlFriendlyArray, \IteratorAggregate
{
    use SimpleArrayIteratorAggregate, CollectionToXmlArray;

    /** @var OrderItem[] */
    protected $_items = [];

    /**
     * @var string
     */
    protected $_xml_tag = 'Item';

    /**
     * OrderItemCollection constructor.
     * @param OrderItem[] $elements
     */
    public function __construct(OrderItem ...$elements)
    {
        $this->_items = array_map(function ($_i) {
            return clone $_i;
        }, $elements);
    }

    /**
     * @param $element
     * @return OrderItemCollection
     */
    public function add(OrderItem $element)
    {
        $this->_items[] = clone $element;
        return $this;
    }
}
<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018-2019
 * @license MIT
 */

namespace SergeR\Webasyst\CdekSDK\Type;

use SergeR\Webasyst\CdekSDK\Utils\CollectionToXmlArray;
use SergeR\Webasyst\CdekSDK\Utils\SimpleArrayIteratorAggregate;
use SergeR\Webasyst\CdekSDK\Utils\XmlFriendlyArray;

/**
 * Class ShopOrderCollection
 * @package SergeR\Webasyst\CdekSDK\Type
 */
class ShopOrderCollection implements \IteratorAggregate, XmlFriendlyArray, \Countable
{
    use SimpleArrayIteratorAggregate, CollectionToXmlArray;

    /**
     * @var string
     */
    protected $_xml_tag = 'ShopOrder';

    /** @var ShopOrder[] */
    protected $_items = [];

    /**
     * ShopOrderCollection constructor.
     * @param ShopOrder[] $orders
     */
    public function __construct(ShopOrder ...$orders)
    {
        $this->_items = $orders;
    }

    /**
     * @param ShopOrder $order
     */
    public function add(ShopOrder $order)
    {
        $this->_items[] = clone $order;
    }
}
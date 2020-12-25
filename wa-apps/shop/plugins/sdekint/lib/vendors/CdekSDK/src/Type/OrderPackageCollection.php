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

class OrderPackageCollection implements \IteratorAggregate, XmlFriendlyArray
{
    use SimpleArrayIteratorAggregate, CollectionToXmlArray;

    /** @var OrderPackage[] */
    protected $_items = [];

    protected $_xml_tag = 'Package';

    /**
     * OrderPackageCollection constructor.
     * @param OrderPackage[] $items
     */
    public function __construct(OrderPackage ...$items)
    {
        $this->_items = $items;
    }

    /**
     * @param OrderPackage $element
     */
    public function add(OrderPackage $element)
    {
        $this->_items[] = $element;
    }
}
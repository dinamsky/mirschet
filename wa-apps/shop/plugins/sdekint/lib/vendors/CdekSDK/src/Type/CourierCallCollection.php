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
 * Class CourierCallCollection
 * @package SergeR\Webasyst\CdekSDK\Type
 */
class CourierCallCollection implements \IteratorAggregate, XmlFriendlyArray, \Countable
{
    use SimpleArrayIteratorAggregate, CollectionToXmlArray;

    /** @var CourierCall[] */
    protected $_items = [];

    protected $_xml_tag = 'Call';

    /**
     * CourierCallCollection constructor.
     * @param CourierCall[] $calls
     */
    public function __construct(CourierCall ...$calls)
    {
        $this->_items = $calls;
    }

    /**
     * @param CourierCall $element
     * @return bool
     */
    public function add(CourierCall $element)
    {
        $this->_items[] = clone $element;
    }
}
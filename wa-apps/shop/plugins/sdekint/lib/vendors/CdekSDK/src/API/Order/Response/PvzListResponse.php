<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license MIT
 */

namespace SergeR\Webasyst\CdekSDK\API\Order\Response;

use Countable;
use IteratorAggregate;
use SergeR\CakeUtility\Exception\XmlException;
use SergeR\Webasyst\CdekSDK\Type\Pvz;
use SergeR\CakeUtility\Hash;
use SergeR\CakeUtility\Xml;
use SimpleXMLElement;
use Traversable;

class PvzListResponse implements IteratorAggregate, Countable
{
    /** @var SimpleXMLElement */
    protected $pvzlist;

    /**
     * PvzListResponse constructor.
     * @param SimpleXMLElement $xml
     */
    public function __construct(SimpleXMLElement $xml)
    {
        $this->pvzlist = clone $xml;
    }

    public function count()
    {
        return count($this->pvzlist->Pvz);
    }

    /**
     * Retrieve an external iterator
     * @return Traversable An instance of an object implementing Iterator or Traversable
     * @throws XmlException
     */
    public function getIterator()
    {
        if ($this->pvzlist instanceof SimpleXMLElement) {
            foreach ($this->pvzlist->Pvz as $item) {
                $item = Xml::toArray($item);
                $item = (array)Hash::get($item, 'Pvz');
                yield Pvz::fromArray($item);
            }
        }
    }
}
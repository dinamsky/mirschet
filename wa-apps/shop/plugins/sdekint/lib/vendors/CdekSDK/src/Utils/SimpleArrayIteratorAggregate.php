<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license
 */

namespace SergeR\Webasyst\CdekSDK\Utils;

use Traversable;

/**
 * Trait SimpleArrayIteratorAggregate
 *
 * @package SergeR\Webasyst\CdekSDK\Utils
 */
trait SimpleArrayIteratorAggregate
{
    /**
     * Retrieve an external iterator
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->_items);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->_items);
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->_items);
    }
}
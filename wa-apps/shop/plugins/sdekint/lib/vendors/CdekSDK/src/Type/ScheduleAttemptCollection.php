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
 * Class ScheduleAttemptCollection
 * @package SergeR\Webasyst\CdekSDK\Type
 */
class ScheduleAttemptCollection implements \IteratorAggregate, XmlFriendlyArray
{
    use SimpleArrayIteratorAggregate, CollectionToXmlArray;

    /** @var ScheduleAttempt[] */
    protected $_items = [];

    /**
     * @var string
     */
    protected $_xml_tag = 'Schedule';

    /**
     * ScheduleAttemptCollection constructor.
     * @param ScheduleAttempt[] $attempts
     */
    public function __construct(ScheduleAttempt ...$attempts)
    {
        $this->_items = $attempts;
    }

    /**
     * @param $element
     */
    public function add(ScheduleAttempt $element)
    {
        $this->_items[] = clone $element;
    }
}
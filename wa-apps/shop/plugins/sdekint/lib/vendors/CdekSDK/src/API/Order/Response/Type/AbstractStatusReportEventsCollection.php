<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license
 */

namespace SergeR\Webasyst\CdekSDK\API\Order\Response\Type;

use SergeR\Webasyst\CdekSDK\Utils\SimpleArrayIteratorAggregate;

/**
 * Class AbstractStatusReportEventsCollection
 * @package SergeR\Webasyst\CdekSDK\API\Order\Response\Type
 */
class AbstractStatusReportEventsCollection
{
    use SimpleArrayIteratorAggregate;

    /** @var array */
    protected $_items;

    /**
     * @return $this
     */
    public function sortByDate()
    {
        usort($this->_items, function (AbstractStatusReportEvent $a, AbstractStatusReportEvent $b) {
            if ($a->getDate() === $b->getDate()) {
                return 0;
            }

            return $a->getDate() > $b->getDate() ? 1 : -1;
        });

        return $this;
    }
}
<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license
 */

namespace SergeR\Webasyst\CdekSDK\API\Order\Response\Type;

use SergeR\CakeUtility\Hash;
use SergeR\Webasyst\CdekSDK\Utils\SimpleArrayIteratorAggregate;

class StatusReportAttemptCollection
{
    use SimpleArrayIteratorAggregate;

    /** @var StatusReportAttempt[] */
    protected $_items;

    public function __construct(StatusReportAttempt ...$attempts)
    {
        $this->_items = $attempts;
    }

    /**
     * @param array $xml_array
     * @return StatusReportAttemptCollection
     */
    public static function fromXmlArray(array $xml_array)
    {
        $self = new self();

        if (!Hash::numeric(array_keys($xml_array))) {
            $xml_array = [$xml_array];
        }

        $self->_items = array_map(function ($item) {
            return StatusReportAttempt::fromXmlArray($item);
        }, $xml_array);

        return $self;
    }

}
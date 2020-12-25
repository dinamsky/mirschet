<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license
 */

namespace SergeR\Webasyst\CdekSDK\API\Order\Response\Type;

use SergeR\CakeUtility\Hash;
use SergeR\Webasyst\CdekSDK\Utils\SimpleArrayIteratorAggregate;

/**
 * Class StatusReportPackageItemCollection
 * @package SergeR\Webasyst\CdekSDK\API\Order\Response\Type
 */
class StatusReportPackageItemCollection
{
    use SimpleArrayIteratorAggregate;

    /** @var StatusReportPackageItem[] */
    protected $_items;

    /**
     * StatusReportPackageItemCollection constructor.
     * @param StatusReportPackageItem[] $packageItems
     */
    public function __construct(StatusReportPackageItem ...$packageItems)
    {
        $this->_items = $packageItems;
    }

    /**
     * @param array $xml_array
     * @return StatusReportPackageItemCollection
     */
    public static function fromArray(array $xml_array)
    {
        if(!Hash::numeric(array_keys($xml_array))) {
            $xml_array = [$xml_array];
        }
        return new self(...array_filter(array_map(function ($item) {
            if (empty($item) || !is_array($item)) {
                return null;
            }
            return StatusReportPackageItem::fromArray($item);
        }, $xml_array)));
    }
}
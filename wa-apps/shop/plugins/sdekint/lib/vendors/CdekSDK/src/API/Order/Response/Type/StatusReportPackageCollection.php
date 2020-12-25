<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018-2019
 * @license MIT
 */

namespace SergeR\Webasyst\CdekSDK\API\Order\Response\Type;

use SergeR\CakeUtility\Hash;
use SergeR\Webasyst\CdekSDK\Utils\SimpleArrayIteratorAggregate;

/**
 * Class StatusReportPackageCollection
 * @package SergeR\Webasyst\CdekSDK\API\Order\Response\Type
 */
class StatusReportPackageCollection
{
    use SimpleArrayIteratorAggregate;

    /** @var StatusReportPackage[] */
    protected $_items;

    /**
     * StatusReportPackageCollection constructor.
     * @param StatusReportPackage[] $packages
     */
    public function __construct(StatusReportPackage ...$packages)
    {
        $this->_items = $packages;
    }

    /**
     * @param array $values
     * @return StatusReportPackageCollection
     */
    public static function fromArray(array $values)
    {
        if (!Hash::numeric(array_keys($values))) {
            $values = [$values];
        }
        return new self(...array_filter(array_map(function ($item) {
            return empty($item) || !is_array($item) ? false : StatusReportPackage::fromArray($item);
        }, $values)));
    }
}
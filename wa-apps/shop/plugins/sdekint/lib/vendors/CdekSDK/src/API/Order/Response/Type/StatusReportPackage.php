<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license MIT
 */

namespace SergeR\Webasyst\CdekSDK\API\Order\Response\Type;

use SergeR\CakeUtility\Hash;

/**
 * Class StatusReportPackage
 * @package SergeR\Webasyst\CdekSDK\API\Order\Response\Type
 */
class StatusReportPackage
{
    /** @var string */
    protected $Number;

    /** @var StatusReportPackageItemCollection */
    protected $Item;

    /**
     * StatusReportPackage constructor.
     */
    public function __construct()
    {
        $this->Item = new StatusReportPackageItemCollection();
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->Number;
    }

    /**
     * @return StatusReportPackageItemCollection
     */
    public function getItem()
    {
        return $this->Item;
    }

    /**
     * @param array $items
     * @return StatusReportPackage
     */
    public static function fromArray(array $items)
    {
        $self = new self();
        $self->Number = (string)Hash::get($items, '@Number');
        $self->Item = StatusReportPackageItemCollection::fromArray((array)Hash::get($items, 'Item'));

        return $self;
    }
}
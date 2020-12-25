<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license MIT
 */

namespace SergeR\Webasyst\CdekSDK\API\Order\Response\Type;

use Exception;
use SergeR\CakeUtility\Hash;

class StatusReportCallGood extends AbstractStatusReportEvent
{
    /** @var \DateTimeImmutable */
    protected $DateDeliv;

    /**
     * @param array $xml_array
     * @return StatusReportCallGood
     * @throws Exception
     */
    public static function fromArray(array $xml_array)
    {
        $self = new self();
        $date = Hash::get($xml_array, '@Date');
        $date_deliv = Hash::get($xml_array, '@DateDeliv');

        if (!$date || !$date_deliv) {
            throw new Exception('Invalid Date or DateDeliv');
        }

        $self->Date = new \DateTimeImmutable($date);

        $self->DateDeliv = new \DateTimeImmutable($date_deliv);

        return $self;
    }
}
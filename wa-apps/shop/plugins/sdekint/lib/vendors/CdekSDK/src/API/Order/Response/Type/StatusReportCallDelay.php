<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license MIT
 */

namespace SergeR\Webasyst\CdekSDK\API\Order\Response\Type;

use DateTimeImmutable;
use Exception;
use SergeR\CakeUtility\Hash;

/**
 * Class StatusReportCallDelay
 * @package SergeR\Webasyst\CdekSDK\API\Order\Response\Type
 */
class StatusReportCallDelay extends AbstractStatusReportEvent
{
    /** @var DateTimeImmutable */
    protected $DateNext;

    /**
     * @return DateTimeImmutable
     */
    public function getDateNext()
    {
        return $this->DateNext;
    }

    /**
     * @param array $xml_array
     * @return StatusReportCallDelay
     * @throws Exception
     */
    public static function fromArray(array $xml_array)
    {
        $self = new self();
        $self->_loadArray($xml_array);

        $date_next = Hash::get($xml_array, '@DateNext');

        if ($date_next) {
            throw new Exception('Invalid DateNext');
        }

        $self->DateNext = new DateTimeImmutable($date_next);

        return $self;
    }
}
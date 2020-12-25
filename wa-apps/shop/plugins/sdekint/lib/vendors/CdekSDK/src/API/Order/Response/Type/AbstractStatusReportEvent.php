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

abstract class AbstractStatusReportEvent
{
    /** @var DateTimeImmutable */
    protected $Date;

    /**
     * @return DateTimeImmutable
     */
    public function getDate()
    {
        return $this->Date;
    }

    /**
     * @param array $values
     * @throws Exception
     */
    protected function _loadArray(array $values)
    {
        $date = Hash::get($values, '@Date');
        if ($date) {
            $date = new DateTimeImmutable($date);
            $this->Date = $date;
        } else {
            throw new Exception('Date field required');
        }
    }
}
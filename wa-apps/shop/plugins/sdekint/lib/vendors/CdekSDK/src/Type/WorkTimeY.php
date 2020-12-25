<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license
 */

namespace SergeR\Webasyst\CdekSDK\Type;

use SergeR\Webasyst\CdekSDK\Utils\FillFromArray;

class WorkTimeY
{
    use FillFromArray;

    /** @var int */
    protected $day;

    /** @var string|null */
    protected $periods;

    /**
     * @return int
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * @param int $day
     * @return WorkTimeY
     * @throws \InvalidArgumentException
     */
    public function setDay($day)
    {
        $day = (int)$day;
        if (($day < 1) || ($day > 7)) {
            throw new \InvalidArgumentException('Day of week value must be between 1 and 7');
        }
        $this->day = $day;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPeriods()
    {
        return $this->periods;
    }

    /**
     * @param string|null $periods
     * @return WorkTimeY
     */
    public function setPeriods($periods = null)
    {
        $this->periods = $periods;
        return $this;
    }
}
<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license
 */

namespace SergeR\Webasyst\CdekSDK\API\Order\Contracts;

trait ErrorResponseTrait
{
    /** @var null|string|int */
    protected $ErrorCode = null;

    /** @var null|string */
    protected $ErrorMsg = null;

    /**
     * @return bool
     */
    public function isError()
    {
        return $this->ErrorCode !== null;
    }

    /**
     * @return null|string|int
     */
    public function getErrorCode()
    {
        return $this->ErrorCode;
    }

    /**
     * @return null|string
     */
    public function getErrorMessage()
    {
        return $this->ErrorMsg;
    }
}
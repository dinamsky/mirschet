<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license
 */

namespace SergeR\Webasyst\CdekSDK\API\Order\Response;

use SergeR\Webasyst\CdekSDK\API\Order\Contracts\ErrorResponse;
use SergeR\Webasyst\CdekSDK\API\Order\Contracts\ErrorResponseTrait;

class PrintformResponse implements ErrorResponse
{
    use ErrorResponseTrait;

    protected $data;

    /**
     * @param $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    /**
     * @param $code
     * @return $this
     */
    public function setErrorCode($code)
    {
        $this->ErrorCode=$code;
        return $this;
    }

    /**
     * @param $msg
     * @return $this
     */
    public function setErrorMessage($msg)
    {
        $this->ErrorMsg=$msg;
        return $this;
    }
}
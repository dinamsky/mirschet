<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2019
 * @license MIT
 */

namespace SergeR\Webasyst\CdekSDK\API\Calculator\Response;

use SergeR\CakeUtility\Hash;
use SergeR\Webasyst\CdekSDK\API\Calculator\Response\Type\CalculatedTariff;

/**
 * Class CalculationTariffListResponse
 * @package SergeR\Webasyst\CdekSDK\API\Calculator\Response
 */
class CalculationTariffListResponse
{
    /** @var CalculatedTariff[] */
    protected $results=[];

    protected $errors=[];

    public function __construct(array $results=[])
    {
        if(isset($results['error'])) {
            foreach ( ($errors = (array)$results['error']) as $err) {
                $err = (array)$err;
                $this->errors[] = ['code'=>(int)Hash::get($err, 'code'), 'message'=>(string)Hash::get($err, 'text')];
            };
            return;
        }

        foreach ((array)Hash::get($results, 'result') as $result) {
            $this->results[] = new CalculatedTariff($result);
        }
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return bool
     */
    public function isError()
    {
        return !empty($this->errors);
    }

    /**
     * @return CalculatedTariff[]
     */
    public function getResults()
    {
        return $this->results;
    }
}
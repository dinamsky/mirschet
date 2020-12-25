<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2019
 * @license MIT
 */

namespace SergeR\Webasyst\CdekSDK\API\Calculator\Request;

/**
 * Class CalculationTariffListRequest
 * @package SergeR\Webasyst\CdekSDK\API\Calculator\Request
 */
class CalculationTariffListRequest extends CalculationRequest
{
    public function toArray()
    {
        $arr = parent::toArray();
        unset($arr['tariffId']);
        $arr['tariffList'] = array_map(function ($t) {
            return ['id' => $t];
        }, (array)$this->getTariff());

        return $arr;
    }
}
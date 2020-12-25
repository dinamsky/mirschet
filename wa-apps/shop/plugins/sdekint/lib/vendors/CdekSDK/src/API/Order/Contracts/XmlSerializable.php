<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license
 */

namespace SergeR\Webasyst\CdekSDK\API\Order\Contracts;


interface XmlSerializable
{
    /**
     * @return \SimpleXMLElement
     */
    public function asXMLElement();
}
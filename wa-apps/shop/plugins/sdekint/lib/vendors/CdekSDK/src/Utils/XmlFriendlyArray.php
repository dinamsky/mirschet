<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license
 */

namespace SergeR\Webasyst\CdekSDK\Utils;

interface XmlFriendlyArray
{
    /**
     * @param array $options
     * @return array
     */
    public function toXmlArray(array $options=[]);
}
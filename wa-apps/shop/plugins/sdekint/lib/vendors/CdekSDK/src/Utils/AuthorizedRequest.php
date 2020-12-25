<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license
 */

namespace SergeR\Webasyst\CdekSDK\Utils;


interface AuthorizedRequest
{
    /**
     * @param string $account
     * @param string $secure
     * @return void
     */
    public function setCredentials($account, $secure);
}
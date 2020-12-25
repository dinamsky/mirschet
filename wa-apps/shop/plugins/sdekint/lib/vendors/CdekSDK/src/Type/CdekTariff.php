<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license
 */

namespace SergeR\Webasyst\CdekSDK\Type;


use SergeR\Webasyst\CdekSDK\Utils\FillFromArray;

/**
 * Class CdekTariff
 * @package SergeR\Webasyst\CdekSDK\Type
 */
class CdekTariff
{
    use FillFromArray;

    const DOOR = 'door';
    const STOCK = 'stock';
    const POSTOMAT = 'postomat';

    const DOOR_DOOR = 1;
    const DOOR_STOCK = 2;
    const STOCK_DOOR = 3;
    const STOCK_STOCK = 4;

    /** @var int */
    protected $Id;

    /**
     * @var string
     */
    protected $Name = '';

    /**
     * @var string
     */
    protected $From = '';

    /**
     * @var string
     */
    protected $To = '';

    /**
     * @var string
     */
    protected $Group = '';

    /**
     * @var string
     */
    protected $Description = '';

    /**
     * @var int
     */
    protected $MinWeight = 0;

    /**
     * @var null
     */
    protected $MaxWeight = null;

    /**
     * @var bool
     */
    protected $Hidden = false;

    /**
     * @var bool
     */
    protected $Deprecated = false;

}
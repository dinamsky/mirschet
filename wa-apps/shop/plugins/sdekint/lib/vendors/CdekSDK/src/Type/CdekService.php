<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license
 */

namespace SergeR\Webasyst\CdekSDK\Type;

use SergeR\Webasyst\CdekSDK\Utils\FillFromArray;

/**
 * Class CdekService
 * @package SergeR\Webasyst\CdekSDK\Type
 */
class CdekService
{
    use FillFromArray;

    /**
     * @var int
     */
    protected $Code;

    /**
     * @var string;
     */
    protected $Name = '';

    /**
     * @var string
     */
    protected $Description = '';

    /** @var bool */
    protected $CalcRequiresParam=false;

    /** @var mixed */
    protected $_param;

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->Code;
    }

    /**
     * @param int $Code
     * @return CdekService
     */
    public function setCode($Code)
    {
        $this->Code = $Code;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->Name;
    }

    /**
     * @param string $Name
     * @return CdekService
     */
    public function setName($Name)
    {
        $this->Name = $Name;
        return $this;
    }


    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->Description;
    }

    /**
     * @param string $Description
     * @return CdekService
     */
    public function setDescription($Description)
    {
        $this->Description = $Description;
        return $this;
    }

    /**
     * @return bool
     */
    public function isCalcRequiresParam()
    {
        return $this->CalcRequiresParam;
    }

    /**
     * @param bool $CalcRequiresParam
     * @return CdekService
     */
    public function setCalcRequiresParam($CalcRequiresParam)
    {
        $this->CalcRequiresParam = $CalcRequiresParam;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getParam()
    {
        return $this->_param;
    }

    /**
     * @param mixed $param
     * @return CdekService
     */
    public function setParam($param)
    {
        $this->_param = $param;
        return $this;
    }


}
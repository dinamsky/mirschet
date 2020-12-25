<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license MIT
 */

namespace SergeR\Webasyst\CdekSDK\API\Order\Response\Type;

use Exception;
use SergeR\CakeUtility\Hash;

class CommonStatusReportState extends AbstractStatusReportEvent
{
    /** @var int|null */
    protected $Code;

    /** @var string */
    protected $Description;

    /**
     * @return int|null
     */
    public function getCode()
    {
        return $this->Code;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->Description;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->Code) && empty($this->Description);
    }

    /**
     * @param array $values
     * @throws Exception
     */
    protected function _loadArray(array $values)
    {
        parent::_loadArray($values);
        $code = Hash::get($values, '@Code');
        $this->Code = $code === null ? null : (int)$code;
        $this->Description = (string)Hash::get($values, '@Description');
    }

    /**
     * @param array $values
     * @return CommonStatusReportState|null
     */
    public static function fromArray(array $values)
    {
        $self = new self();
        try {
            $self->_loadArray($values);
        } catch (Exception $e) {
            return null;
        }
        return $self;
    }
}
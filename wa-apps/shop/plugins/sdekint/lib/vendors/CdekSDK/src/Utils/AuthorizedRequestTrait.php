<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license MIT
 */

namespace SergeR\Webasyst\CdekSDK\Utils;

/**
 * Trait AuthorizedRequestTrait
 * @package SergeR\Webasyst\CdekSDK\API\Utils
 * @property string $_date_format
 */
trait AuthorizedRequestTrait
{
    /** @var string */
    protected $_account;

    /** @var string */
    protected $_secure_password;

    /** @var \DateTimeImmutable */
    protected $Date;

    /**
     * @param string $account
     * @param string $secure_password
     * @return void
     */
    public function setCredentials($account, $secure_password)
    {
        $this->_account = $account;
        $this->_secure_password = $secure_password;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getDate()
    {
        return $this->Date;
    }

    /**
     * @param string $format
     * @return string
     */
    public function getFormattedDate($format = 'Y-m-d')
    {
        if (property_exists($this, '_date_format') && ($format === null)) {
            if (($_format = (string)$this->_date_format)) {
                $format = $_format;
            } else {
                $format = 'Y-m-d';
            }
        }
        return $this->getDate()->format($format);
    }

    /**
     * @param \DateTimeImmutable $Date
     * @return $this
     */
    public function setDate(\DateTimeImmutable $Date)
    {
        $this->Date = $Date;
        return $this;
    }

    /**
     * @return string
     */
    public function getAccount()
    {
        return $this->_account;
    }

    /**
     * @return string
     */
    public function getSecure()
    {
        return md5($this->getFormattedDate() . '&' . $this->_secure_password);
    }

}
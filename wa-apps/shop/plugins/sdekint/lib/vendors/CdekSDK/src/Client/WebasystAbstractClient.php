<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license
 */

namespace SergeR\Webasyst\CdekSDK\Client;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

abstract class WebasystAbstractClient
{
    /** @var string|null */
    protected $account;

    /** @var string|null */
    protected $secure_password;

    /** @var LoggerInterface */
    protected $logger;

    /** @var array */
    protected $default_wanet_options = [];

    /** @var array */
    protected $default_wanet_headers = [];

    protected $_log_level = null;

    /**
     * WebasystClient constructor.
     * @param string|null $account
     * @param string| $secure_password
     */
    public function __construct($account = null, $secure_password = null)
    {
        $this->account = $account;
        $this->secure_password = $secure_password;
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param null|string $log_level
     * @return $this
     */
    public function setLogLevel($log_level = null)
    {
        $this->_log_level = $log_level;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getLogLevel()
    {
        return $this->_log_level;
    }

    /**
     * @param $level
     * @param $message
     * @param array $context
     */
    protected function _log($level, $message, $context = [])
    {
        if (!$this->logger || !$level) {
            return;
        }

        $this->logger->log($level, $message, $context);
    }

    /**
     * @param \Exception $e
     */
    protected function _logException(\Exception $e)
    {
        $this->_log($this->getLogLevel(), "Got Exception. Code={code}, Message='{message}'", ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        return;
    }

    /**
     * @return array
     */
    public function getDefaultWanetOptions()
    {
        return $this->default_wanet_options;
    }

    /**
     * @param array $default_wanet_options
     * @return WebasystAbstractClient
     */
    public function setDefaultWanetOptions(array $default_wanet_options)
    {
        $this->default_wanet_options = $default_wanet_options;
        return $this;
    }

    /**
     * @return array
     */
    public function getDefaultWanetHeaders()
    {
        return $this->default_wanet_headers;
    }

    /**
     * @param array $default_wenet_headers
     * @return WebasystAbstractClient
     */
    public function setDefaultWanetHeaders(array $default_wanet_headers)
    {
        $this->default_wanet_headers = $default_wanet_headers;
        return $this;
    }

}
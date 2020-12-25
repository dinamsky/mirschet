<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license Webasyst
 */

namespace Syrnik;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

class WaLogger extends AbstractLogger
{
    protected $log_level;
    protected $all_logs_levels = [LogLevel::EMERGENCY, LogLevel::ALERT, LogLevel::CRITICAL, LogLevel::ERROR, LogLevel::WARNING, LogLevel::NOTICE, LogLevel::INFO, LogLevel::DEBUG];
    protected $log_levels = [];
    protected $file;

    public function __construct($file, $log_level = LogLevel::CRITICAL)
    {
        $this->logLevel($log_level);
        $this->file = $file;
    }

    public function logLevel($log_level = null)
    {
        if ($log_level) {
            $idx = array_search($log_level, $this->all_logs_levels);
            $this->log_levels = array_slice($this->all_logs_levels, 0, $idx + 1);
            $this->log_level = $log_level;
        }
        return $this->log_level;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function log($level, $message, array $context = array())
    {
        if (!in_array($level, $this->log_levels)) {
            return;
        }

        if (isset($context['dump'])) {
            $context['dump'] = var_export($context['dump'], true);
        }

        $message = $this->interpolate($message, $context);

        \waLog::log($message, $this->file);
    }

    /**
     * Interpolates context values into the message placeholders.
     */
    protected function interpolate($message, array $context = array())
    {
        // build a replacement array with braces around the context keys
        $replace = array();
        foreach ($context as $key => $val) {
            // check that the value can be casted to string
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }

        // interpolate replacement values into the message and return
        return strtr($message, $replace);
    }
}

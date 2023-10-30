<?php

/**
 * Log a message if debug mode is enabled.
 *
 * @param string $level
 * 'critical': Critical conditions.
 * 'error': Error conditions.
 * 'debug': Debug-level messages.
 * @param string $message Message to log.
 */
const SIMPL_LOG_NAME = 'simpl-logs';

class Simpl_Logger {
    private static $instance;
    
    /**
	 * The logger instance.
	 *
	 * @var WC_Logger|null
	 */
	private $logger;

    private function __construct() {
        $this->logger = wc_get_logger();
    }

    static function get_instance() {
        if (Simpl_Logger::$instance == null) {
            Simpl_Logger::$instance = new Simpl_Logger();
        }
         
        return Simpl_Logger::$instance;
    }


    /**
     * Adds a critical level message if simpl debug mode is enabled.
     *
     * Critical conditions.
     * Example: Application component unavailable, unexpected exception.
     *
     * @see WC_Logger::log
     *
     * @param string $message Message to log.
     */
    public function critical($message)
    {
        $this->simpl_log('critical', $message);
    }

    /**
     * Adds an error level message if simpl debug mode is enabled.
     *
     * Runtime errors that do not require immediate action but should typically be logged
     * and monitored.
     *
     * @see WC_Logger::log
     *
     * @param string $message Message to log.
     */
    public function error($message)
    {
        $this->simpl_log('error', $message);
    }


    /**
     * Adds a debug level message if simpl debug mode is enabled
     * Detailed debug information
     * @see WC_Logger::log
     * @param string $message Message to log
     */
    public function debug($message)
    {
        $is_debug_mode_enabled = $this->is_debug_mode_enabled();
        if (!$is_debug_mode_enabled) {
            return;
        }

        $this->simpl_log('debug', $message);
    }

    function is_debug_mode_enabled()
    {
        return (
            'yes' == get_option('wc_settings_tab_simpl_debug_logs')
        );
    }


    function simpl_log($level, $message)
    {
        $this->logger->log($level, $message, array('source' => SIMPL_LOG_NAME));
    }
}

function get_simpl_logger() {
    $simpl_logger = Simpl_Logger::get_instance();
    return $simpl_logger;
}
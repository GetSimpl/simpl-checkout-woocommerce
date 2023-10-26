<?php

/**
 * Log a message if debug mode is enabled.
 *
 * @param string $level
 * 'emergency': System is unusable.
 *  'alert': Action must be taken immediately.
 * 'critical': Critical conditions.
 * 'error': Error conditions.
 * 'warning': Warning conditions.
 * 'notice': Normal but significant condition.
 * 'info': Informational messages.
 * 'debug': Debug-level messages.
 * @param string $message Message to log.
 */
define('SIMPL_LOG_NAME', 'simpl-logs');

class Simpl_Logger {
    /**
	 * The logger instance.
	 *
	 * @var WC_Logger|null
	 */
	protected static $logger;

    final function __construct() {
        if ( is_null( self::$logger ) ) {
			self::$logger = wc_get_logger();
		}

		return self::$logger;
    }
    
    /**
     * Adds an emergency level message if simpl debug mode is enabled
     *
     * System is unusable.
     *
     * @see WC_Logger::log
     *
     * @param string $message Message to log.
     */
    public function emergency($message)
    {
        $this->simpl_log('emergency', $message);
    }

    /**
     * Adds an alert level message if simpl debug mode is enabled.
     *
     * Action must be taken immediately.
     * Example: Entire website down, database unavailable, etc.
     *
     * @see WC_Logger::log
     *
     * @param string $message Message to log.
     */
    public function alert($message)
    {
        $this->simpl_log('alert', $message);
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
     * Adds a warning level message if simpl debug mode is enabled.
     *
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things that are not
     * necessarily wrong.
     *
     * @see WC_Logger::log
     *
     * @param string $message Message to log.
     */
    public function warning($message)
    {
        $this->simpl_log('warning', $message);
    }

    /**
     * Adds a notice level message if simpl debug mode is enabled.
     *
     * Normal but significant events.
     *
     * @see WC_Logger::log
     *
     * @param string $message Message to log.
     */
    public function notice($message)
    {
        $this->simpl_log('notice', $message);
    }

    /**
     * Adds a info level message if simpl debug mode is enabled
     *
     * Interesting events.
     * Example: User logs in, SQL logs
     *
     * @see WC_Logger::log
     *
     * @param string $message Message to log.
     */
    public function info($message)
    {
        $this->simpl_log('info', $message);
    }

    /**
     * Adds a debug level message if simpl debug mode is enabled
     * Detailed debug information
     * @see WC_Logger::log
     * @param string $message Message to log
     */
    public function debug($message)
    {
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
        if ($this->is_debug_mode_enabled()) {
            self::$logger->log($level, $message, array('source' => SIMPL_LOG_NAME));
        }
    }
}

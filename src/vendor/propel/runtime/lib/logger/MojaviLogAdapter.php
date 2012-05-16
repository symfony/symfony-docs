<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * Mojavi logging adapter for propel
 *
 * @author     Brandon Keepers <brandon@opensoul.org>
 * @version    $Revision: 1612 $
 * @package    propel.runtime.logger
 */
class MojaviLogAdapter implements BasicLogger
{

	/**
	 * Instance of mojavi logger
	 */
	private $logger = null;

	/**
	 * constructor for setting up Mojavi log adapter
	 *
	 * @param      ErrorLog   $logger   instance of Mojavi error log obtained by
	 *                               calling LogManager::getLogger();
	 */
	public function __construct($logger = null)
	{
		$this->logger = $logger;
	}

	/**
	 * A convenience function for logging an alert event.
	 *
	 * @param      mixed   $message    String or Exception object containing the message
	 *                              to log.
	 */
	public function alert($message)
	{
		$this->log($message, 'alert');
	}

	/**
	 * A convenience function for logging a critical event.
	 *
	 * @param      mixed   $message    String or Exception object containing the message
	 *                              to log.
	 */
	public function crit($message)
	{
		$this->log($message, 'crit');
	}

	/**
	 * A convenience function for logging an error event.
	 *
	 * @param      mixed   $message    String or Exception object containing the message
	 *                              to log.
	 */
	public function err($message)
	{
		$this->log($message, 'err');
	}

	/**
	 * A convenience function for logging a warning event.
	 *
	 * @param      mixed   $message    String or Exception object containing the message
	 *                              to log.
	 */
	public function warning($message)
	{
		$this->log($message, 'warning');
	}


	/**
	 * A convenience function for logging an critical event.
	 *
	 * @param      mixed   $message    String or Exception object containing the message
	 *                              to log.
	 */
	public function notice($message)
	{
		$this->log($message, 'notice');
	}
	/**
	 * A convenience function for logging an critical event.
	 *
	 * @param      mixed   $message    String or Exception object containing the message
	 *                              to log.
	 */
	public function info($message)
	{
		$this->log($message, 'info');
	}

	/**
	 * A convenience function for logging a debug event.
	 *
	 * @param      mixed   $message    String or Exception object containing the message
	 *                              to log.
	 */
	public function debug($message)
	{
		$this->log($message, 'debug');
	}

	/**
	 * Primary method to handle logging.
	 *
	 * @param      mixed   $message    String or Exception object containing the message
	 *                              to log.
	 * @param      int     $severity   The numeric severity.  Defaults to null so that no
	 *                              assumptions are made about the logging backend.
	 */
	public function log($message, $severity = null)
	{
		if (is_null($this->logger))
			$this->logger = LogManager::getLogger('propel');

		switch($severity)
		{
			case 'crit':
				$method = 'fatal';
				break;
			case 'err':
				$method = 'error';
				break;
			case 'alert':
			case 'warning':
				$method = 'warning';
				break;
			case 'notice':
			case 'info':
				$method = 'info';
				break;
			case 'debug':
			default:
				$method = 'debug';
		}

		// get a backtrace to pass class, function, file, & line to Mojavi logger
		$trace = debug_backtrace();

		// call the appropriate Mojavi logger method
		$this->logger->{$method} (
			$message,
			$trace[2]['class'],
			$trace[2]['function'],
			$trace[1]['file'],
			$trace[1]['line']
			);
	}
}

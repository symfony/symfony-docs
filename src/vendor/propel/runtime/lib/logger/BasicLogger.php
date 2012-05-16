<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * This is a minimalistic interface that any logging class must implement for Propel.
 *
 * The API for this interface is based on the PEAR::Log interface.  It provides a simple
 * API that can be used by Propel independently of Log backend.
 *
 * PEAR::Log and perhaps the Log API was developed by Chuck Hagenbuch <chuck@horde.org>
 * and Jon Parise <jon@php.net>.
 *
 * @author     Hans Lellelid <hans@xmpl.org>
 * @version    $Revision: 1612 $
 * @package    propel.runtime.logger
 */
interface BasicLogger
{

	/**
	 * A convenience function for logging an alert event.
	 *
	 * @param      mixed   $message    String or Exception object containing the message
	 *                              to log.
	 */
	public function alert($message);

	/**
	 * A convenience function for logging a critical event.
	 *
	 * @param      mixed   $message    String or Exception object containing the message
	 *                              to log.
	 */
	public function crit($message);

	/**
	 * A convenience function for logging an error event.
	 *
	 * @param      mixed   $message    String or Exception object containing the message
	 *                              to log.
	 */
	public function err($message);

	/**
	 * A convenience function for logging a warning event.
	 *
	 * @param      mixed   $message    String or Exception object containing the message
	 *                              to log.
	 */
	public function warning($message);
	/**
	 * A convenience function for logging an critical event.
	 *
	 * @param      mixed   $message    String or Exception object containing the message
	 *                              to log.
	 */
	public function notice($message);
	/**
	 * A convenience function for logging an critical event.
	 *
	 * @param      mixed   $message    String or Exception object containing the message
	 *                              to log.
	 */
	public function info($message);

	/**
	 * A convenience function for logging a debug event.
	 *
	 * @param      mixed   $message    String or Exception object containing the message
	 *                              to log.
	 */
	public function debug($message);

	/**
	 * Primary method to handle logging.
	 *
	 * @param      mixed   $message    String or Exception object containing the message
	 *                              to log.
	 * @param      int     $severity   The numeric severity.  Defaults to null so that no
	 *                              assumptions are made about the logging backend.
	 */
	public function log($message, $severity = null);

}

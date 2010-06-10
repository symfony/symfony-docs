<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * DateTime subclass which supports serialization.
 *
 * Currently Propel is not using this for storing date/time objects
 * within model objeects; however, we are keeping it in the repository
 * because it is useful if you want to store a DateTime object in a session.
 *
 * @author     Alan Pinstein
 * @author     Soenke Ruempler
 * @author     Hans Lellelid
 * @package    propel.runtime.util
 */
class PropelDateTime extends DateTime
{

	/**
	 * A string representation of the date, for serialization.
	 * @var        string
	 */
	private $dateString;

	/**
	 * A string representation of the time zone, for serialization.
	 * @var        string
	 */
	private $tzString;

	/**
	 * Convenience method to enable a more fluent API.
	 * @param      string $date Date/time value.
	 * @param      DateTimeZone $tz (optional) timezone
	 */
	public static function newInstance($date, DateTimeZone $tz = null)
	{
		if ($tz) {
			return new DateTime($date, $tz);
		} else {
			return new DateTime($date);
		}
	}

	/**
	 * PHP "magic" function called when object is serialized.
	 * Sets an internal property with the date string and returns properties
	 * of class that should be serialized.
	 * @return     array string[]
	 */
	function __sleep()
	{
		// We need to use a string without a time zone, due to
		// PHP bug: http://bugs.php.net/bug.php?id=40743
		$this->dateString = $this->format('Y-m-d H:i:s');
		$this->tzString = $this->getTimeZone()->getName();
		return array('dateString', 'tzString');
	}

	/**
	 * PHP "magic" function called when object is restored from serialized state.
	 * Calls DateTime constructor with previously stored string value of date.
	 */
	function __wakeup()
	{
		parent::__construct($this->dateString, new DateTimeZone($this->tzString));
	}

}

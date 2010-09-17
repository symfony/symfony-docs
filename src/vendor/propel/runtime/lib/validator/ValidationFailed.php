<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */


/**
 * Simple class that serves as a container for any information about a failed validation.
 *
 * Currently this class stores the qualified column name (e.g. tablename.COLUMN_NAME) and
 * the message that should be displayed to the user.
 *
 * An array of these objects will be returned by BasePeer::doValidate() if validation
 * failed.
 *
 * @author     Hans Lellelid <hans@xmpl.org>
 * @version    $Revision: 1612 $
 * @package    propel.runtime.validator
 * @see        BasePeer::doValidate()
 */
class ValidationFailed {

	/** Column name in tablename.COLUMN_NAME format */
	private $colname;

	/** Message to display to user. */
	private $message;

	/** Validator object that caused this to fail. */
	private $validator;

	/**
	 * Construct a new ValidationFailed object.
	 * @param      string $colname Column name.
	 * @param      string $message Message to display to user.
	 * @param      object $validator The Validator that caused this column to fail.
	 */
	public function __construct($colname, $message, $validator = null)
	{
		$this->colname = $colname;
		$this->message = $message;
		$this->validator = $validator;
	}

	/**
	 * Set the column name.
	 * @param      string $v
	 */
	public function setColumn($v)
	{
		$this->colname = $v;
	}

	/**
	 * Gets the column name.
	 * @return     string Qualified column name (tablename.COLUMN_NAME)
	 */
	public function getColumn()
	{
		return $this->colname;
	}

	/**
	 * Set the message for the validation failure.
	 * @param      string $v
	 */
	public function setMessage($v)
	{
		$this->message = $v;
	}

	/**
	 * Gets the message for the validation failure.
	 * @return     string
	 */
	public function getMessage()
	{
		return $this->message;
	}

	/**
	 * Set the validator object that caused this to fail.
	 * @param      object $v
	 */
	public function setValidator($v)
	{
		$this->validator = $v;
	}

	/**
	 * Gets the validator object that caused this to fail.
	 * @return     object
	 */
	public function getValidator()
	{
		return $this->validator;
	}

	/**
	 * "magic" method to get string represenation of object.
	 * Maybe someday PHP5 will support the invoking this method automatically
	 * on (string) cast.  Until then it's pretty useless.
	 * @return     string
	 */
	public function __toString()
	{
		return $this->getMessage();
	}

}

<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

require_once 'platform/DefaultPlatform.php';
require_once 'model/Domain.php';

/**
 * MS SQL Platform implementation.
 *
 * @author     Hans Lellelid <hans@xmpl.org> (Propel)
 * @author     Martin Poeschl <mpoeschl@marmot.at> (Torque)
 * @version    $Revision: 1612 $
 * @package    propel.generator.platform
 */
class MssqlPlatform extends DefaultPlatform
{

	/**
	 * Initializes db specific domain mapping.
	 */
	protected function initialize()
	{
		parent::initialize();
		$this->setSchemaDomainMapping(new Domain(PropelTypes::INTEGER, "INT"));
		$this->setSchemaDomainMapping(new Domain(PropelTypes::BOOLEAN, "INT"));
		$this->setSchemaDomainMapping(new Domain(PropelTypes::DOUBLE, "FLOAT"));
		$this->setSchemaDomainMapping(new Domain(PropelTypes::LONGVARCHAR, "TEXT"));
		$this->setSchemaDomainMapping(new Domain(PropelTypes::CLOB, "TEXT"));
		$this->setSchemaDomainMapping(new Domain(PropelTypes::DATE, "DATETIME"));
		$this->setSchemaDomainMapping(new Domain(PropelTypes::BU_DATE, "DATETIME"));
		$this->setSchemaDomainMapping(new Domain(PropelTypes::TIME, "DATETIME"));
		$this->setSchemaDomainMapping(new Domain(PropelTypes::TIMESTAMP, "DATETIME"));
		$this->setSchemaDomainMapping(new Domain(PropelTypes::BU_TIMESTAMP, "DATETIME"));
		$this->setSchemaDomainMapping(new Domain(PropelTypes::BINARY, "BINARY(7132)"));
		$this->setSchemaDomainMapping(new Domain(PropelTypes::VARBINARY, "IMAGE"));
		$this->setSchemaDomainMapping(new Domain(PropelTypes::LONGVARBINARY, "IMAGE"));
		$this->setSchemaDomainMapping(new Domain(PropelTypes::BLOB, "IMAGE"));
	}

	/**
	 * @see        Platform#getMaxColumnNameLength()
	 */
	public function getMaxColumnNameLength()
	{
		return 128;
	}

	/**
	 * @return     Explicitly returns <code>NULL</code> if null values are
	 * allowed (as recomended by Microsoft).
	 * @see        Platform#getNullString(boolean)
	 */
	public function getNullString($notNull)
	{
		return ($notNull ? "NOT NULL" : "NULL");
	}

	/**
	 * @see        Platform::supportsNativeDeleteTrigger()
	 */
	public function supportsNativeDeleteTrigger()
	{
		return true;
	}

	/**
	 * @see        Platform::supportsInsertNullPk()
	 */
	public function supportsInsertNullPk()
	{
		return false;
	}
	
	/**
	 * @see        Platform::hasSize(String)
	 */
	public function hasSize($sqlType)
	{
		return !("INT" == $sqlType || "TEXT" == $sqlType);
	}

	/**
	 * @see        Platform::quoteIdentifier()
	 */
	public function quoteIdentifier($text)
	{
		return '[' . $text . ']';
	}

   /**
   * Gets the preferred timestamp formatter for setting date/time values.
   * @return     string
   */
  public function getTimestampFormatter()
  {
    return 'Y-m-d H:i:s';
  }


}

<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

require_once 'platform/DefaultPlatform.php';

/**
 * Oracle Platform implementation.
 *
 * @author     Hans Lellelid <hans@xmpl.org> (Propel)
 * @author     Martin Poeschl <mpoeschl@marmot.at> (Torque)
 * @version    $Revision: 1612 $
 * @package    propel.generator.platform
 */
class OraclePlatform extends DefaultPlatform
{

	/**
	 * Initializes db specific domain mapping.
	 */
	protected function initialize()
	{
		parent::initialize();
		$this->schemaDomainMap[PropelTypes::BOOLEAN] = new Domain(PropelTypes::BOOLEAN_EMU, "NUMBER", "1", "0");
		$this->schemaDomainMap[PropelTypes::CLOB] = new Domain(PropelTypes::CLOB_EMU, "CLOB");
		$this->schemaDomainMap[PropelTypes::CLOB_EMU] = $this->schemaDomainMap[PropelTypes::CLOB];
		$this->setSchemaDomainMapping(new Domain(PropelTypes::TINYINT, "NUMBER", "3", "0"));
		$this->setSchemaDomainMapping(new Domain(PropelTypes::SMALLINT, "NUMBER", "5", "0"));
		$this->setSchemaDomainMapping(new Domain(PropelTypes::INTEGER, "NUMBER"));
		$this->setSchemaDomainMapping(new Domain(PropelTypes::BIGINT, "NUMBER", "20", "0"));
		$this->setSchemaDomainMapping(new Domain(PropelTypes::REAL, "NUMBER"));
		$this->setSchemaDomainMapping(new Domain(PropelTypes::DOUBLE, "FLOAT"));
		$this->setSchemaDomainMapping(new Domain(PropelTypes::DECIMAL, "NUMBER"));
		$this->setSchemaDomainMapping(new Domain(PropelTypes::NUMERIC, "NUMBER"));
		$this->setSchemaDomainMapping(new Domain(PropelTypes::VARCHAR, "NVARCHAR2"));
		$this->setSchemaDomainMapping(new Domain(PropelTypes::LONGVARCHAR, "NVARCHAR2", "2000")); 
		$this->setSchemaDomainMapping(new Domain(PropelTypes::TIME, "DATE")); 
		$this->setSchemaDomainMapping(new Domain(PropelTypes::DATE, "DATE")); 
		$this->setSchemaDomainMapping(new Domain(PropelTypes::TIMESTAMP, "TIMESTAMP")); 
		$this->setSchemaDomainMapping(new Domain(PropelTypes::BINARY, "LONG RAW"));
		$this->setSchemaDomainMapping(new Domain(PropelTypes::VARBINARY, "BLOB"));
		$this->setSchemaDomainMapping(new Domain(PropelTypes::LONGVARBINARY, "LONG RAW"));
	}

	/**
	 * @see        Platform#getMaxColumnNameLength()
	 */
	public function getMaxColumnNameLength()
	{
		return 30;
	}

	/**
	 * @see        Platform#getNativeIdMethod()
	 */
	public function getNativeIdMethod()
	{
		return Platform::SEQUENCE;
	}

	/**
	 * @see        Platform#getAutoIncrement()
	 */
	public function getAutoIncrement()
	{
		return "";
	}

	/**
	 * @see        Platform::supportsNativeDeleteTrigger()
	 */
	public function supportsNativeDeleteTrigger()
	{
		return true;
	}

	/**
	 * Whether the underlying PDO driver for this platform returns BLOB columns as streams (instead of strings).
	 * @return     boolean
	 */
	public function hasStreamBlobImpl()
	{
		return true;
	}
	
	/**
	 * Quotes identifiers used in database SQL.
	 * @see        Platform::quoteIdentifier()
	 * @param      string $text
	 * @return     string Quoted identifier.
	 */
	public function quoteIdentifier($text)
	{
		return $text;
	}

	/**
	 * Gets the preferred timestamp formatter for setting date/time values.
	 * @see        Platform::getTimestampFormatter()
	 * @return     string 
	 */
	public function getTimestampFormatter()
	{
		return 'Y-m-d H:i:s';
	}

}

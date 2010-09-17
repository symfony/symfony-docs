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
 * Postgresql Platform implementation.
 *
 * @author     Hans Lellelid <hans@xmpl.org> (Propel)
 * @author     Martin Poeschl <mpoeschl@marmot.at> (Torque)
 * @version    $Revision: 1612 $
 * @package    propel.generator.platform
 */
class PgsqlPlatform extends DefaultPlatform
{

	/**
	 * Initializes db specific domain mapping.
	 */
	protected function initialize()
	{
		parent::initialize();
		$this->setSchemaDomainMapping(new Domain(PropelTypes::BOOLEAN, "BOOLEAN"));
		$this->setSchemaDomainMapping(new Domain(PropelTypes::TINYINT, "INT2"));
		$this->setSchemaDomainMapping(new Domain(PropelTypes::SMALLINT, "INT2"));
		$this->setSchemaDomainMapping(new Domain(PropelTypes::BIGINT, "INT8"));
		$this->setSchemaDomainMapping(new Domain(PropelTypes::REAL, "FLOAT"));
		$this->setSchemaDomainMapping(new Domain(PropelTypes::DOUBLE, "DOUBLE PRECISION"));
		$this->setSchemaDomainMapping(new Domain(PropelTypes::LONGVARCHAR, "TEXT"));
		$this->setSchemaDomainMapping(new Domain(PropelTypes::BINARY, "BYTEA"));
		$this->setSchemaDomainMapping(new Domain(PropelTypes::VARBINARY, "BYTEA"));
		$this->setSchemaDomainMapping(new Domain(PropelTypes::LONGVARBINARY, "BYTEA"));
		$this->setSchemaDomainMapping(new Domain(PropelTypes::BLOB, "BYTEA"));
		$this->setSchemaDomainMapping(new Domain(PropelTypes::CLOB, "TEXT"));
	}

	/**
	 * @see        Platform#getNativeIdMethod()
	 */
	public function getNativeIdMethod()
	{
		return Platform::SERIAL;
	}

	/**
	 * @see        Platform#getAutoIncrement()
	 */
	public function getAutoIncrement()
	{
		return "";
	}

	/**
	 * @see        Platform#getMaxColumnNameLength()
	 */
	public function getMaxColumnNameLength()
	{
		return 32;
	}

	/**
	 * Escape the string for RDBMS.
	 * @param      string $text
	 * @return     string
	 */
	public function disconnectedEscapeText($text)
	{
		if (function_exists('pg_escape_string')) {
			return pg_escape_string($text);
		} else {
			return parent::disconnectedEscapeText($text);
		}
	}

	/**
	 * @see        Platform::getBooleanString()
	 */
	public function getBooleanString($b)
	{
		// parent method does the checking for allowes tring
		// representations & returns integer
		$b = parent::getBooleanString($b);
		return ($b ? "'t'" : "'f'");
	}

	/**
	 * @see        Platform::supportsNativeDeleteTrigger()
	 */
	public function supportsNativeDeleteTrigger()
	{
		return true;
	}

	/**
	 * @see        Platform::hasSize(String)
	 * TODO collect info for all platforms
	 */
	public function hasSize($sqlType)
	{
		return !("BYTEA" == $sqlType || "TEXT" == $sqlType);
	}

	/**
	 * Whether the underlying PDO driver for this platform returns BLOB columns as streams (instead of strings).
	 * @return     boolean
	 */
	public function hasStreamBlobImpl()
	{
		return true;
	}
}

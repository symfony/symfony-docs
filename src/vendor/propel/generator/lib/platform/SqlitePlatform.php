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
 * SQLite Platform implementation.
 *
 * @author     Hans Lellelid <hans@xmpl.org>
 * @version    $Revision: 1612 $
 * @package    propel.generator.platform
 */
class SqlitePlatform extends DefaultPlatform
{

	/**
	 * Initializes db specific domain mapping.
	 */
	protected function initialize()
	{
		parent::initialize();
		$this->setSchemaDomainMapping(new Domain(PropelTypes::NUMERIC, "DECIMAL"));
		$this->setSchemaDomainMapping(new Domain(PropelTypes::LONGVARCHAR, "MEDIUMTEXT"));
		$this->setSchemaDomainMapping(new Domain(PropelTypes::DATE, "DATETIME"));
		$this->setSchemaDomainMapping(new Domain(PropelTypes::BINARY, "BLOB"));
		$this->setSchemaDomainMapping(new Domain(PropelTypes::VARBINARY, "MEDIUMBLOB"));
		$this->setSchemaDomainMapping(new Domain(PropelTypes::LONGVARBINARY, "LONGBLOB"));
		$this->setSchemaDomainMapping(new Domain(PropelTypes::BLOB, "LONGBLOB"));
		$this->setSchemaDomainMapping(new Domain(PropelTypes::CLOB, "LONGTEXT"));
	}

	/**
	 * @see        Platform#getAutoIncrement()
	 * @link       http://www.sqlite.org/autoinc.html
	 */
	public function getAutoIncrement()
	{

		return "PRIMARY KEY";
	}

	/**
	 * @see        Platform#getMaxColumnNameLength()
	 */
	public function getMaxColumnNameLength()
	{
		return 1024;
	}

	/**
	 * @see        Platform#hasSize(String)
	 */
	public function hasSize($sqlType) {
		return !("MEDIUMTEXT" == $sqlType || "LONGTEXT" == $sqlType
				|| "BLOB" == $sqlType || "MEDIUMBLOB" == $sqlType
				|| "LONGBLOB" == $sqlType);
	}

	/**
	 * Escape the string for RDBMS.
	 * @param      string $text
	 * @return     string
	 */
	public function disconnectedEscapeText($text)
	{
		if (function_exists('sqlite_escape_string')) {
			return sqlite_escape_string($text);
		} else {
			return parent::disconnectedEscapeText($text);
		}
	}

	/**
	 * @see        Platform::quoteIdentifier()
	 */
	public function quoteIdentifier($text)
	{
		return '[' . $text . ']';
	}
}

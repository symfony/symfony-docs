<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

require_once 'builder/sql/DataSQLBuilder.php';

/**
 * SQLite class for building data dump SQL.
 *
 * @author     Hans Lellelid <hans@xmpl.org>
 * @package    propel.generator.builder.sql.sqlite
 */
class SqliteDataSQLBuilder extends DataSQLBuilder
{

	/**
	 * Returns string processed by sqlite_udf_encode_binary() to ensure that binary contents will be handled correctly by sqlite.
	 * @param      mixed $blob Blob or string
	 * @return     string encoded text
	 */
	protected function getBlobSql($blob)
	{
		// they took magic __toString() out of PHP5.0.0; this sucks
		if (is_object($blob)) {
			$blob = $blob->__toString();
		}
		return "'" . sqlite_udf_encode_binary($blob) . "'";
	}

}

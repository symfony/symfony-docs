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
 * MS SQL Server class for building data dump SQL.
 *
 * @author     Hans Lellelid <hans@xmpl.org>
 * @package    propel.generator.builder.sql.mssql
 */
class MssqlDataSQLBuilder extends DataSQLBuilder
{

	/**
	 *
	 * @param      mixed $blob Blob object or string containing data.
	 * @return     string
	 */
	protected function getBlobSql($blob)
	{
		// they took magic __toString() out of PHP5.0.0; this sucks
		if (is_object($blob)) {
			$blob = $blob->__toString();
		}
		$data = unpack("H*hex", $blob);
		return '0x'.$data['hex']; // no surrounding quotes!
	}

}

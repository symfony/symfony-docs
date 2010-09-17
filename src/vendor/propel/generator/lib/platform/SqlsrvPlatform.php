<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

require_once dirname(__FILE__) . '/MssqlPlatform.php';

/**
 * MS SQL Server using pdo_sqlsrv implementation.
 *
 * @author     Benjamin Runnels
 * @version    $Revision$
 * @package    propel.generator.platform
 */
class SqlsrvPlatform extends MssqlPlatform
{
	/**
	 * @see        Platform#getMaxColumnNameLength()
	 */
	public function getMaxColumnNameLength()
	{
		return 128;
	}
}

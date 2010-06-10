<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * dblib doesn't support transactions so we need to add a workaround for transactions, last insert ID, and quoting
 *
 * @package    propel.runtime.adapter.MSSQL
 */
class MssqlDebugPDO extends MssqlPropelPDO
{
	public $useDebug = true;
}

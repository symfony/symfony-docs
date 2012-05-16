<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * statement formatter for Propel query
 * format() returns a PDO statement
 *
 * @author     Francois Zaninotto
 * @version    $Revision: 1796 $
 * @package    propel.runtime.formatter
 */
class PropelStatementFormatter extends PropelFormatter
{
	public function format(PDOStatement $stmt)
	{
		return $stmt;
	}
	
	public function formatOne(PDOStatement $stmt)
	{
		if ($stmt->rowCount() == 0) {
			return null;
		} else {
			return $stmt;
		}
	}
	
	public function formatRecord($record = null)
	{
		throw new PropelException('The Statement formatter cannot transform a record into a statement');
	}

	public function isObjectFormatter()
	{
		return false;
	}

}
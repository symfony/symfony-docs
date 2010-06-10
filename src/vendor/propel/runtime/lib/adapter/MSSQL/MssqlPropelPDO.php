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
class MssqlPropelPDO extends PropelPDO
{
	/**
	 * Begin a transaction.
	 *
	 * It is necessary to override the abstract PDO transaction functions here, as
	 * the PDO driver for MSSQL does not support transactions.
	 */
	public function beginTransaction()
	{
		$return = true;
		$opcount = $this->getNestedTransactionCount();
		if ( $opcount === 0 ) {
			$return = self::exec('BEGIN TRANSACTION');
			if ($this->useDebug) {
				$this->log('Begin transaction', null, __METHOD__);
			}
			$this->isUncommitable = false;
		}
		$this->nestedTransactionCount++;
		return $return;
	}
	
	/**
	 * Commit a transaction.
	 *
	 * It is necessary to override the abstract PDO transaction functions here, as
	 * the PDO driver for MSSQL does not support transactions.
	 */
	public function commit()
	{
		$return = true;
		$opcount = $this->getNestedTransactionCount();
		if ($opcount > 0) {
			if ($opcount === 1) {
				if ($this->isUncommitable) {
					throw new PropelException('Cannot commit because a nested transaction was rolled back');
				} else {
					$return = self::exec('COMMIT TRANSACTION');
					if ($this->useDebug) {		
				  	$this->log('Commit transaction', null, __METHOD__);
					}

				}
			}
			$this->nestedTransactionCount--;
		}
		return $return;
	}

	/**
	 * Roll-back a transaction.
	 *
	 * It is necessary to override the abstract PDO transaction functions here, as
	 * the PDO driver for MSSQL does not support transactions.
	 */
	public function rollBack()
	{
		$return = true;
		$opcount = $this->getNestedTransactionCount();
		if ($opcount > 0) {
			if ($opcount === 1) {
				$return = self::exec('ROLLBACK TRANSACTION'); 
				if ($this->useDebug) {		
					$this->log('Rollback transaction', null, __METHOD__);
				}
			} else {
				$this->isUncommitable = true;
			}
			$this->nestedTransactionCount--; 
		}
		return $return;
	}

	/**
	 * Rollback the whole transaction, even if this is a nested rollback
	 * and reset the nested transaction count to 0.
	 *
	 * It is necessary to override the abstract PDO transaction functions here, as
	 * the PDO driver for MSSQL does not support transactions.
	 */
	public function forceRollBack()
	{
		$return = true;
		$opcount = $this->getNestedTransactionCount();
		if ($opcount > 0) {
			// If we're in a transaction, always roll it back
			// regardless of nesting level.
			$return = self::exec('ROLLBACK TRANSACTION');

			// reset nested transaction count to 0 so that we don't
			// try to commit (or rollback) the transaction outside this scope.
			$this->nestedTransactionCount = 0;

			if ($this->useDebug) {
				$this->log('Rollback transaction', null, __METHOD__);
			}
		}
		return $return;
	}

	public function lastInsertId($seqname = null)
	{
		$result = self::query('SELECT SCOPE_IDENTITY()');
		return (int) $result->fetchColumn();
	}
	
	public function quoteIdentifier($text)
	{
		return '[' . $text . ']';
	}
	
	public function useQuoteIdentifier()
	{
		return true;
	}
}

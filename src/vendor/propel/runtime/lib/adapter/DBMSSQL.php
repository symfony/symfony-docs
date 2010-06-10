<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * This is used to connect to a MSSQL database.
 *
 * @author     Hans Lellelid <hans@xmpl.org> (Propel)
 * @version    $Revision: 1700 $
 * @package    propel.runtime.adapter
 */
class DBMSSQL extends DBAdapter
{

	/**
	 * This method is used to ignore case.
	 *
	 * @param      in The string to transform to upper case.
	 * @return     The upper case string.
	 */
	public function toUpperCase($in)
	{
		return $this->ignoreCase($in);
	}

	/**
	 * This method is used to ignore case.
	 *
	 * @param      in The string whose case to ignore.
	 * @return     The string in a case that can be ignored.
	 */
	public function ignoreCase($in)
	{
		return 'UPPER(' . $in . ')';
	}

	/**
	 * Returns SQL which concatenates the second string to the first.
	 *
	 * @param      string String to concatenate.
	 * @param      string String to append.
	 * @return     string
	 */
	public function concatString($s1, $s2)
	{
		return '(' . $s1 . ' + ' . $s2 . ')';
	}

	/**
	 * Returns SQL which extracts a substring.
	 *
	 * @param      string String to extract from.
	 * @param      int Offset to start from.
	 * @param      int Number of characters to extract.
	 * @return     string
	 */
	public function subString($s, $pos, $len)
	{
		return 'SUBSTRING(' . $s . ', ' . $pos . ', ' . $len . ')';
	}

	/**
	 * Returns SQL which calculates the length (in chars) of a string.
	 *
	 * @param      string String to calculate length of.
	 * @return     string
	 */
	public function strLength($s)
	{
		return 'LEN(' . $s . ')';
	}

	/**
	 * @see        DBAdapter::quoteIdentifier()
	 */
	public function quoteIdentifier($text)
	{
		return '[' . $text . ']';
	}

	/**
	 * @see        DBAdapter::random()
	 */
	public function random($seed = null)
	{
		return 'RAND(' . ((int)$seed) . ')';
	}

	/**
	 * Simulated Limit/Offset
	 * This rewrites the $sql query to apply the offset and limit.
	 * some of the ORDER BY logic borrowed from Doctrine MsSqlPlatform
	 * @see        DBAdapter::applyLimit()
	 * @author     Benjamin Runnels <kraven@kraven.org>
	 */
	public function applyLimit(&$sql, $offset, $limit)
	{
		// make sure offset and limit are numeric
		if(! is_numeric($offset) || ! is_numeric($limit))
		{
			throw new PropelException('DBMSSQL::applyLimit() expects a number for argument 2 and 3');
		}

		//split the select and from clauses out of the original query
		$selectSegment = array();

		$selectText = 'SELECT ';

		if (preg_match('/\Aselect(\s+)distinct/i', $sql)) {
			$selectText .= 'DISTINCT ';
		}

		preg_match('/\Aselect(.*)from(.*)/si', $sql, $selectSegment);
		if(count($selectSegment) == 3) {
			$selectStatement = trim($selectSegment[1]);
			$fromStatement = trim($selectSegment[2]);
		} else {
			throw new Exception('DBMSSQL::applyLimit() could not locate the select statement at the start of the query.');
		}

		// if we're starting at offset 0 then theres no need to simulate limit,
		// just grab the top $limit number of rows
		if($offset == 0) {
			$sql = $selectText . 'TOP ' . $limit . ' ' . $selectStatement . ' FROM ' . $fromStatement;
			return;
		}

		//get the ORDER BY clause if present
		$orderStatement = stristr($fromStatement, 'ORDER BY');
		$orders = '';

		if($orderStatement !== false) {
			//remove order statement from the from statement
			$fromStatement = trim(str_replace($orderStatement, '', $fromStatement));

			$order = str_ireplace('ORDER BY', '', $orderStatement);
			$orders = explode(',', $order);

			for($i = 0; $i < count($orders); $i ++) {
				$orderArr[trim(preg_replace('/\s+(ASC|DESC)$/i', '', $orders[$i]))] = array(
					'sort' => (stripos($orders[$i], ' DESC') !== false) ? 'DESC' : 'ASC',
					'key' => $i
				);
			}
		}

		//setup inner and outer select selects
		$innerSelect = '';
		$outerSelect = '';
		foreach(explode(', ', $selectStatement) as $selCol) {
			$selColArr = explode(' ', $selCol);
			$selColCount = count($selColArr) - 1;

			//make sure the current column isn't * or an aggregate
			if($selColArr[0] != '*' && ! strstr($selColArr[0], '(')) {
				if(isset($orderArr[$selColArr[0]])) {
					$orders[$orderArr[$selColArr[0]]['key']] = $selColArr[0] . ' ' . $orderArr[$selColArr[0]]['sort'];
				}

				//use the alias if one was present otherwise use the column name
				$alias = (! stristr($selCol, ' AS ')) ? $this->quoteIdentifier($selColArr[0]) : $this->quoteIdentifier($selColArr[$selColCount]);

				//save the first non-aggregate column for use in ROW_NUMBER() if required
				if(! isset($firstColumnOrderStatement)) {
					$firstColumnOrderStatement = 'ORDER BY ' . $selColArr[0];
				}

				//add an alias to the inner select so all columns will be unique
				$innerSelect .= $selColArr[0] . ' AS ' . $alias . ', ';
				$outerSelect .= $alias . ', ';
			} else {
				//agregate columns must always have an alias clause
				if(! stristr($selCol, ' AS ')) {
					throw new Exception('DBMSSQL::applyLimit() requires aggregate columns to have an Alias clause');
				}

				//aggregate column alias can't be used as the count column you must use the entire aggregate statement
				if(isset($orderArr[$selColArr[$selColCount]])) {
					$orders[$orderArr[$selColArr[$selColCount]]['key']] = str_replace($selColArr[$selColCount - 1] . ' ' . $selColArr[$selColCount], '', $selCol) . $orderArr[$selColArr[$selColCount]]['sort'];
				}

				//quote the alias
				$alias = $this->quoteIdentifier($selColArr[$selColCount]);
				$innerSelect .= str_replace($selColArr[$selColCount], $alias, $selCol) . ', ';
				$outerSelect .= $alias . ', ';
			}
		}

		if(is_array($orders)) {
			$orderStatement = 'ORDER BY ' . implode(', ', $orders);
		} else {
			//use the first non aggregate column in our select statement if no ORDER BY clause present
			if(isset($firstColumnOrderStatement)) {
				$orderStatement = $firstColumnOrderStatement;
			} else {
				throw new Exception('DBMSSQL::applyLimit() unable to find column to use with ROW_NUMBER()');
			}
		}

		//substring the select strings to get rid of the last comma and add our FROM and SELECT clauses
		$innerSelect = $selectText . 'ROW_NUMBER() OVER(' . $orderStatement . ') AS RowNumber, ' . substr($innerSelect, 0, - 2) . ' FROM';
		//outer select can't use * because of the RowNumber column
		$outerSelect = 'SELECT ' . substr($outerSelect, 0, - 2) . ' FROM';

		//ROW_NUMBER() starts at 1 not 0
		$sql = $outerSelect . ' (' . $innerSelect . ' ' . $fromStatement . ') AS derivedb WHERE RowNumber BETWEEN ' . ($offset + 1) . ' AND ' . ($limit + $offset);
		return;
	}
}

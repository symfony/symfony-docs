<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * This is used in order to connect to a MySQL database.
 *
 * @author     Hans Lellelid <hans@xmpl.org> (Propel)
 * @author     Jon S. Stevens <jon@clearink.com> (Torque)
 * @author     Brett McLaughlin <bmclaugh@algx.net> (Torque)
 * @author     Daniel Rall <dlr@finemaltcoding.com> (Torque)
 * @version    $Revision: 1612 $
 * @package    propel.runtime.adapter
 */
class DBMySQL extends DBAdapter 
{

	/**
	 * This method is used to ignore case.
	 *
	 * @param      in The string to transform to upper case.
	 * @return     The upper case string.
	 */
	public function toUpperCase($in)
	{
		return "UPPER(" . $in . ")";
	}

	/**
	 * This method is used to ignore case.
	 *
	 * @param      in The string whose case to ignore.
	 * @return     The string in a case that can be ignored.
	 */
	public function ignoreCase($in)
	{
		return "UPPER(" . $in . ")";
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
		return "CONCAT($s1, $s2)";
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
		return "SUBSTRING($s, $pos, $len)";
	}

	/**
	 * Returns SQL which calculates the length (in chars) of a string.
	 *
	 * @param      string String to calculate length of.
	 * @return     string
	 */
	public function strLength($s)
	{
		return "CHAR_LENGTH($s)";
	}


	/**
	 * Locks the specified table.
	 *
	 * @param      Connection $con The Propel connection to use.
	 * @param      string $table The name of the table to lock.
	 * @throws     PDOException No Statement could be created or
	 * executed.
	 */
	public function lockTable(PDO $con, $table)
	{
		$con->exec("LOCK TABLE " . $table . " WRITE");
	}

	/**
	 * Unlocks the specified table.
	 *
	 * @param      PDO $con The PDO connection to use.
	 * @param      string $table The name of the table to unlock.
	 * @throws     PDOException No Statement could be created or
	 * executed.
	 */
	public function unlockTable(PDO $con, $table)
	{
		$statement = $con->exec("UNLOCK TABLES");
	}

	/**
	 * @see        DBAdapter::quoteIdentifier()
	 */
	public function quoteIdentifier($text)
	{
		return '`' . $text . '`';
	}

	/**
	 * @see        DBAdapter::useQuoteIdentifier()
	 */
	public function useQuoteIdentifier()
	{
		return true;
	}

	/**
	 * @see        DBAdapter::applyLimit()
	 */
	public function applyLimit(&$sql, $offset, $limit)
	{
		if ( $limit > 0 ) {
			$sql .= " LIMIT " . ($offset > 0 ? $offset . ", " : "") . $limit;
		} else if ( $offset > 0 ) {
			$sql .= " LIMIT " . $offset . ", 18446744073709551615";
		}
	}

	/**
	 * @see        DBAdapter::random()
	 */
	public function random($seed = null)
	{
		return 'rand('.((int) $seed).')';
	}

}

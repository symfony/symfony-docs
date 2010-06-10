<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * Oracle adapter.
 *
 * @author     David Giffin <david@giffin.org> (Propel)
 * @author     Hans Lellelid <hans@xmpl.org> (Propel)
 * @author     Jon S. Stevens <jon@clearink.com> (Torque)
 * @author     Brett McLaughlin <bmclaugh@algx.net> (Torque)
 * @author     Bill Schneider <bschneider@vecna.com> (Torque)
 * @author     Daniel Rall <dlr@finemaltcoding.com> (Torque)
 * @version    $Revision: 1669 $
 * @package    propel.runtime.adapter
 */
class DBOracle extends DBAdapter
{
	/**
	 * This method is called after a connection was created to run necessary
	 * post-initialization queries or code.
	 * Removes the charset query and adds the date queries
	 *
	 * @param      PDO   A PDO connection instance.
	 * @see        parent::initConnection()
	 */
	public function initConnection(PDO $con, array $settings)
	{
		$con->exec("ALTER SESSION SET NLS_DATE_FORMAT='YYYY-MM-DD'");
		$con->exec("ALTER SESSION SET NLS_TIMESTAMP_FORMAT='YYYY-MM-DD HH24:MI:SS'");
		if (isset($settings['queries']) && is_array($settings['queries'])) {
			foreach ($settings['queries'] as $queries) {
				foreach ((array)$queries as $query) {
					$con->exec($query);
				}
			}
		}
	}
	
	/**
	 * This method is used to ignore case.
	 *
	 * @param      string $in The string to transform to upper case.
	 * @return     string The upper case string.
	 */
	public function toUpperCase($in)
	{
		return "UPPER(" . $in . ")";
	}

	/**
	 * This method is used to ignore case.
	 *
	 * @param      string $in The string whose case to ignore.
	 * @return     string The string in a case that can be ignored.
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
		return "SUBSTR($s, $pos, $len)";
	}

	/**
	 * Returns SQL which calculates the length (in chars) of a string.
	 *
	 * @param      string String to calculate length of.
	 * @return     string
	 */
	public function strLength($s)
	{
		return "LENGTH($s)";
	}

	/**
	 * @see        DBAdapter::applyLimit()
	 */
	public function applyLimit(&$sql, $offset, $limit, $criteria = null)
	{
		if (BasePeer::needsSelectAliases($criteria)) {
			$selectSql = BasePeer::createSelectSqlPart($criteria, $params, true);
			$sql = $selectSql . substr($sql, strpos('FROM', $sql));
		}
		$sql = 'SELECT B.* FROM ('
			. 'SELECT A.*, rownum AS PROPEL_ROWNUM FROM (' . $sql . ') A '
			. ') B WHERE ';

		if ( $offset > 0 ) {
			$sql .= ' B.PROPEL_ROWNUM > ' . $offset;
			if ( $limit > 0 ) {
				$sql .= ' AND B.PROPEL_ROWNUM <= ' . ( $offset + $limit );
			}
		} else {
			$sql .= ' B.PROPEL_ROWNUM <= ' . $limit;
		}
	}

	protected function getIdMethod()
	{
		return DBAdapter::ID_METHOD_SEQUENCE;
	}

	public function getId(PDO $con, $name = null)
	{
		if ($name === null) {
			throw new PropelException("Unable to fetch next sequence ID without sequence name.");
		}

		$stmt = $con->query("SELECT " . $name . ".nextval FROM dual");
		$row = $stmt->fetch(PDO::FETCH_NUM);

		return $row[0];
	}

	public function random($seed=NULL)
	{
		return 'dbms_random.value';
	}


}

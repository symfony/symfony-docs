<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * DBAdapter</code> defines the interface for a Propel database adapter.
 *
 * <p>Support for new databases is added by subclassing
 * <code>DBAdapter</code> and implementing its abstract interface, and by
 * registering the new database adapter and corresponding Propel
 * driver in the private adapters map (array) in this class.</p>
 *
 * <p>The Propel database adapters exist to present a uniform
 * interface to database access across all available databases.  Once
 * the necessary adapters have been written and configured,
 * transparent swapping of databases is theoretically supported with
 * <i>zero code change</i> and minimal configuration file
 * modifications.</p>
 *
 * @author     Hans Lellelid <hans@xmpl.org> (Propel)
 * @author     Jon S. Stevens <jon@latchkey.com> (Torque)
 * @author     Brett McLaughlin <bmclaugh@algx.net> (Torque)
 * @author     Daniel Rall <dlr@finemaltcoding.com> (Torque)
 * @version    $Revision: 1612 $
 * @package    propel.runtime.adapter
 */
abstract class DBAdapter
{

	const ID_METHOD_NONE = 0;
	const ID_METHOD_AUTOINCREMENT = 1;
	const ID_METHOD_SEQUENCE = 2;

	/**
	 * Propel driver to Propel adapter map.
	 * @var        array
	 */
	private static $adapters = array(
		'mysql'  => 'DBMySQL',
		'mysqli' => 'DBMySQLi',
		'mssql'  => 'DBMSSQL',
		'dblib'  => 'DBMSSQL',
		'sybase' => 'DBSybase',
		'oracle' => 'DBOracle',
		'oci'    => 'DBOracle',
		'pgsql'  => 'DBPostgres',
		'sqlite' => 'DBSQLite',
		''       => 'DBNone',
	);

	/**
	 * Creates a new instance of the database adapter associated
	 * with the specified Propel driver.
	 *
	 * @param      string $driver The name of the Propel driver to
	 * create a new adapter instance for or a shorter form adapter key.
	 * @return     DBAdapter An instance of a Propel database adapter.
	 * @throws     PropelException if the adapter could not be instantiated.
	 */
	public static function factory($driver) {
		$adapterClass = isset(self::$adapters[$driver]) ? self::$adapters[$driver] : null;
		if ($adapterClass !== null) {
			$a = new $adapterClass();
			return $a;
		} else {
			throw new PropelException("Unsupported Propel driver: " . $driver . ": Check your configuration file");
		}
	}

	/**
	 * This method is called after a connection was created to run necessary
	 * post-initialization queries or code.
	 *
	 * If a charset was specified, this will be set before any other queries
	 * are executed.
	 *
	 * This base method runs queries specified using the "query" setting.
	 *
	 * @param      PDO   A PDO connection instance.
	 * @param      array An array of settings.
	 * @see        setCharset()
	 */
	public function initConnection(PDO $con, array $settings)
	{
		if (isset($settings['charset']['value'])) {
			$this->setCharset($con, $settings['charset']['value']);
		}
		if (isset($settings['queries']) && is_array($settings['queries'])) {
			foreach ($settings['queries'] as $queries) {
				foreach ((array)$queries as $query) {
					$con->exec($query);
				}
			}
		}
	}

	/**
	 * Sets the character encoding using SQL standard SET NAMES statement.
	 *
	 * This method is invoked from the default initConnection() method and must
	 * be overridden for an RDMBS which does _not_ support this SQL standard.
	 *
	 * @param      PDO   A PDO connection instance.
	 * @param      string The charset encoding.
	 * @see        initConnection()
	 */
	public function setCharset(PDO $con, $charset)
	{
		$con->exec("SET NAMES '" . $charset . "'");
	}

	/**
	 * This method is used to ignore case.
	 *
	 * @param      string The string to transform to upper case.
	 * @return     string The upper case string.
	 */
	public abstract function toUpperCase($in);

	/**
	 * Returns the character used to indicate the beginning and end of
	 * a piece of text used in a SQL statement (generally a single
	 * quote).
	 *
	 * @return     string The text delimeter.
	 */
	public function getStringDelimiter()
	{
		return '\'';
	}

	/**
	 * This method is used to ignore case.
	 *
	 * @param      string $in The string whose case to ignore.
	 * @return     string The string in a case that can be ignored.
	 */
	public abstract function ignoreCase($in);

	/**
	 * This method is used to ignore case in an ORDER BY clause.
	 * Usually it is the same as ignoreCase, but some databases
	 * (Interbase for example) does not use the same SQL in ORDER BY
	 * and other clauses.
	 *
	 * @param      string $in The string whose case to ignore.
	 * @return     string The string in a case that can be ignored.
	 */
	public function ignoreCaseInOrderBy($in)
	{
		return $this->ignoreCase($in);
	}

	/**
	 * Returns SQL which concatenates the second string to the first.
	 *
	 * @param      string String to concatenate.
	 * @param      string String to append.
	 * @return     string
	 */
	public abstract function concatString($s1, $s2);

	/**
	 * Returns SQL which extracts a substring.
	 *
	 * @param      string String to extract from.
	 * @param      int Offset to start from.
	 * @param      int Number of characters to extract.
	 * @return     string
	 */
	public abstract function subString($s, $pos, $len);

	/**
	 * Returns SQL which calculates the length (in chars) of a string.
	 *
	 * @param      string String to calculate length of.
	 * @return     string
	 */
	public abstract function strLength($s);


	/**
	 * Quotes database objec identifiers (table names, col names, sequences, etc.).
	 * @param      string $text The identifier to quote.
	 * @return     string The quoted identifier.
	 */
	public function quoteIdentifier($text)
	{
		return '"' . $text . '"';
	}

	/**
	 * Quotes a database table which could have space seperating it from an alias, both should be identified seperately
	 * @param      string $table The table name to quo
	 * @return     string The quoted table name
	 **/
	public function quoteIdentifierTable($table) {
		return implode(" ", array_map(array($this, "quoteIdentifier"), explode(" ", $table) ) );
	}

	/**
	 * Returns the native ID method for this RDBMS.
	 * @return     int one of DBAdapter:ID_METHOD_SEQUENCE, DBAdapter::ID_METHOD_AUTOINCREMENT.
	 */
	protected function getIdMethod()
	{
		return DBAdapter::ID_METHOD_AUTOINCREMENT;
	}

	/**
	 * Whether this adapter uses an ID generation system that requires getting ID _before_ performing INSERT.
	 * @return     boolean
	 */
	public function isGetIdBeforeInsert()
	{
		return ($this->getIdMethod() === DBAdapter::ID_METHOD_SEQUENCE);
	}

	/**
	 * Whether this adapter uses an ID generation system that requires getting ID _before_ performing INSERT.
	 * @return     boolean
	 */
	public function isGetIdAfterInsert()
	{
		return ($this->getIdMethod() === DBAdapter::ID_METHOD_AUTOINCREMENT);
	}

	/**
	 * Gets the generated ID (either last ID for autoincrement or next sequence ID).
	 * @return     mixed
	 */
	public function getId(PDO $con, $name = null)
	{
		return $con->lastInsertId($name);
	}

	/**
	 * Returns timestamp formatter string for use in date() function.
	 * @return     string
	 */
	public function getTimestampFormatter()
	{
		return "Y-m-d H:i:s";
	}

	/**
	 * Returns date formatter string for use in date() function.
	 * @return     string
	 */
	public function getDateFormatter()
	{
		return "Y-m-d";
	}

	/**
	 * Returns time formatter string for use in date() function.
	 * @return     string
	 */
	public function getTimeFormatter()
	{
		return "H:i:s";
	}

	/**
	 * Should Column-Names get identifiers for inserts or updates.
	 * By default false is returned -> backwards compability.
	 *
	 * it`s a workaround...!!!
	 *
	 * @todo       should be abstract
	 * @return     boolean
	 * @deprecated
	 */
	public function useQuoteIdentifier()
	{
		return false;
	}

	/**
	 * Modifies the passed-in SQL to add LIMIT and/or OFFSET.
	 */
	public abstract function applyLimit(&$sql, $offset, $limit);

	/**
	 * Gets the SQL string that this adapter uses for getting a random number.
	 *
	 * @param      mixed $seed (optional) seed value for databases that support this
	 */
	public abstract function random($seed = null);

}

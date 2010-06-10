<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * This is an "inner" class that describes an object in the criteria.
 *
 * In Torque this is an inner class of the Criteria class.
 *
 * @author     Hans Lellelid <hans@xmpl.org> (Propel)
 * @version    $Revision: 1740 $
 * @package    propel.runtime.query
 */
class Criterion
{

	const UND = " AND ";
	const ODER = " OR ";

	/** Value of the CO. */
	protected $value;

	/** Comparison value.
	 * @var        SqlEnum
	 */
	protected $comparison;

	/** Table name. */
	protected $table;

	/** Real table name */
	protected $realtable;

	/** Column name. */
	protected $column;

	/** flag to ignore case in comparison */
	protected $ignoreStringCase = false;

	/**
	 * The DBAdaptor which might be used to get db specific
	 * variations of sql.
	 */
	protected $db;

	/**
	 * other connected criteria and their conjunctions.
	 */
	protected $clauses = array();
	protected $conjunctions = array();

	/** "Parent" Criteria class */
	protected $parent;

	/**
	 * Create a new instance.
	 *
	 * @param      Criteria $parent The outer class (this is an "inner" class).
	 * @param      string $column TABLE.COLUMN format.
	 * @param      mixed $value
	 * @param      string $comparison
	 */
	public function __construct(Criteria $outer, $column, $value, $comparison = null)
	{
		$this->value = $value;
		$dotPos = strrpos($column, '.');
		if ($dotPos === false) {
			// no dot => aliased column
			$this->table = null;
			$this->column = $column;
		} else {
			$this->table = substr($column, 0, $dotPos); 
			$this->column = substr($column, $dotPos + 1);
		}
		$this->comparison = ($comparison === null) ? Criteria::EQUAL : $comparison;
		$this->init($outer);
	}

	/**
	* Init some properties with the help of outer class
	* @param      Criteria $criteria The outer class
	*/
	public function init(Criteria $criteria)
	{
		// init $this->db
		try {
			$db = Propel::getDB($criteria->getDbName());
			$this->setDB($db);
		} catch (Exception $e) {
			// we are only doing this to allow easier debugging, so
			// no need to throw up the exception, just make note of it.
			Propel::log("Could not get a DBAdapter, sql may be wrong", Propel::LOG_ERR);
		}

		// init $this->realtable
		$realtable = $criteria->getTableForAlias($this->table);
		$this->realtable = $realtable ? $realtable : $this->table;

	}

	/**
	 * Get the column name.
	 *
	 * @return     string A String with the column name.
	 */
	public function getColumn()
	{
		return $this->column;
	}

	/**
	 * Set the table name.
	 *
	 * @param      name A String with the table name.
	 * @return     void
	 */
	public function setTable($name)
	{
		$this->table = $name;
	}

	/**
	 * Get the table name.
	 *
	 * @return     string A String with the table name.
	 */
	public function getTable()
	{
		return $this->table;
	}

	/**
	 * Get the comparison.
	 *
	 * @return     string A String with the comparison.
	 */
	public function getComparison()
	{
		return $this->comparison;
	}

	/**
	 * Get the value.
	 *
	 * @return     mixed An Object with the value.
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * Get the value of db.
	 * The DBAdapter which might be used to get db specific
	 * variations of sql.
	 * @return     DBAdapter value of db.
	 */
	public function getDB()
	{
		return $this->db;
	}

	/**
	 * Set the value of db.
	 * The DBAdapter might be used to get db specific variations of sql.
	 * @param      DBAdapter $v Value to assign to db.
	 * @return     void
	 */
	public function setDB(DBAdapter $v)
	{
		$this->db = $v;
		foreach ( $this->clauses as $clause ) {
			$clause->setDB($v);
		}
	}

	/**
	 * Sets ignore case.
	 *
	 * @param      boolean $b True if case should be ignored.
	 * @return     Criterion A modified Criterion object.
	 */
	public function setIgnoreCase($b)
	{
		$this->ignoreStringCase = (boolean) $b;
		return $this;
	}

	/**
	 * Is ignore case on or off?
	 *
	 * @return     boolean True if case is ignored.
	 */
	 public function isIgnoreCase()
	 {
		 return $this->ignoreStringCase;
	 }

	/**
	 * Get the list of clauses in this Criterion.
	 * @return     array
	 */
	private function getClauses()
	{
		return $this->clauses;
	}

	/**
	 * Get the list of conjunctions in this Criterion
	 * @return     array
	 */
	public function getConjunctions()
	{
		return $this->conjunctions;
	}

	/**
	 * Append an AND Criterion onto this Criterion's list.
	 */
	public function addAnd(Criterion $criterion)
	{
		$this->clauses[] = $criterion;
		$this->conjunctions[] = self::UND;
		return $this;
	}

	/**
	 * Append an OR Criterion onto this Criterion's list.
	 * @return     Criterion
	 */
	public function addOr(Criterion $criterion)
	{
		$this->clauses[] = $criterion;
		$this->conjunctions[] = self::ODER;
		return $this;
	}

	/**
	 * Appends a Prepared Statement representation of the Criterion
	 * onto the buffer.
	 *
	 * @param      string &$sb The string that will receive the Prepared Statement
	 * @param      array $params A list to which Prepared Statement parameters will be appended
	 * @return     void
	 * @throws     PropelException - if the expression builder cannot figure out how to turn a specified
	 *                           expression into proper SQL.
	 */
	public function appendPsTo(&$sb, array &$params)
	{
		$sb .= str_repeat ( '(', count($this->clauses) );
		
		$this->dispatchPsHandling($sb, $params);

		foreach ($this->clauses as $key => $clause) {
			$sb .= $this->conjunctions[$key];
			$clause->appendPsTo($sb, $params);
			$sb .= ')';
		}
	}
	
	/**
	 * Figure out which Criterion method to use 
	 * to build the prepared statement and parameters using to the Criterion comparison
	 * and call it to append the prepared statement and the parameters of the current clause
	 *
	 * @param      string &$sb The string that will receive the Prepared Statement
	 * @param      array $params A list to which Prepared Statement parameters will be appended
	 */
	protected function dispatchPsHandling(&$sb, array &$params)
	{
		switch ($this->comparison) {
			case Criteria::CUSTOM:
				// custom expression with no parameter binding
				$this->appendCustomToPs($sb, $params);
				break;
			case Criteria::IN:
			case Criteria::NOT_IN:
				// table.column IN (?, ?) or table.column NOT IN (?, ?)
				$this->appendInToPs($sb, $params);
				break;
			case Criteria::LIKE:
			case Criteria::NOT_LIKE:
			case Criteria::ILIKE:
			case Criteria::NOT_ILIKE:
				// table.column LIKE ? or table.column NOT LIKE ?  (or ILIKE for Postgres)
				$this->appendLikeToPs($sb, $params);
				break;							
			default:
				// table.column = ? or table.column >= ? etc. (traditional expressions, the default)
				$this->appendBasicToPs($sb, $params);
		}
	}
	
	/**
	 * Appends a Prepared Statement representation of the Criterion onto the buffer
	 * For custom expressions with no binding, e.g. 'NOW() = 1'
	 *
	 * @param      string &$sb The string that will receive the Prepared Statement
	 * @param      array $params A list to which Prepared Statement parameters will be appended
	 */
	protected function appendCustomToPs(&$sb, array &$params)
	{
		if ($this->value !== "") {
			$sb .= (string) $this->value;
		}
	}

	/**
	 * Appends a Prepared Statement representation of the Criterion onto the buffer
	 * For IN expressions, e.g. table.column IN (?, ?) or table.column NOT IN (?, ?)
	 *
	 * @param      string &$sb The string that will receive the Prepared Statement
	 * @param      array $params A list to which Prepared Statement parameters will be appended
	 */
	protected function appendInToPs(&$sb, array &$params)
	{
		if ($this->value !== "") {
			$bindParams = array();
			$index = count($params); // to avoid counting the number of parameters for each element in the array
			foreach ((array) $this->value as $value) {
				$params[] = array('table' => $this->realtable, 'column' => $this->column, 'value' => $value);
				$index++; // increment this first to correct for wanting bind params to start with :p1
				$bindParams[] = ':p' . $index;
			}
			if (count($bindParams)) {
				$field = ($this->table === null) ? $this->column : $this->table . '.' . $this->column;
				$sb .= $field . $this->comparison . '(' . implode(',', $bindParams) . ')';
			} else {
				$sb .= ($this->comparison === Criteria::IN) ? "1<>1" : "1=1";
			}
		}
	}

	/**
	 * Appends a Prepared Statement representation of the Criterion onto the buffer
	 * For LIKE expressions, e.g. table.column LIKE ? or table.column NOT LIKE ?  (or ILIKE for Postgres)
	 *
	 * @param      string &$sb The string that will receive the Prepared Statement
	 * @param      array $params A list to which Prepared Statement parameters will be appended
	 */
	protected function appendLikeToPs(&$sb, array &$params)
	{
		$field = ($this->table === null) ? $this->column : $this->table . '.' . $this->column;
		$db = $this->getDb();
		// If selection is case insensitive use ILIKE for PostgreSQL or SQL
		// UPPER() function on column name for other databases.
		if ($this->ignoreStringCase) {
			if ($db instanceof DBPostgres) {
				if ($this->comparison === Criteria::LIKE) {
					$this->comparison = Criteria::ILIKE;
				} elseif ($this->comparison === Criteria::NOT_LIKE) {
					$this->comparison = Criteria::NOT_ILIKE;
				}
			} else {
				$field = $db->ignoreCase($field);
			}
		}
		
		$params[] = array('table' => $this->realtable, 'column' => $this->column, 'value' => $this->value);
		
		$sb .= $field . $this->comparison;

		// If selection is case insensitive use SQL UPPER() function
		// on criteria or, if Postgres we are using ILIKE, so not necessary.
		if ($this->ignoreStringCase && !($db instanceof DBPostgres)) {
			$sb .= $db->ignoreCase(':p'.count($params));
		} else {
			$sb .= ':p'.count($params);
		}
	}

	/**
	 * Appends a Prepared Statement representation of the Criterion onto the buffer
	 * For traditional expressions, e.g. table.column = ? or table.column >= ? etc. 
	 *
	 * @param      string &$sb The string that will receive the Prepared Statement
	 * @param      array $params A list to which Prepared Statement parameters will be appended
	 */
	protected function appendBasicToPs(&$sb, array &$params)
	{
		$field = ($this->table === null) ? $this->column : $this->table . '.' . $this->column;
		// NULL VALUES need special treatment because the SQL syntax is different
		// i.e. table.column IS NULL rather than table.column = null
		if ($this->value !== null) {

			// ANSI SQL functions get inserted right into SQL (not escaped, etc.)
			if ($this->value === Criteria::CURRENT_DATE || $this->value === Criteria::CURRENT_TIME || $this->value === Criteria::CURRENT_TIMESTAMP) {
				$sb .= $field . $this->comparison . $this->value;
			} else {
				
				$params[] = array('table' => $this->realtable, 'column' => $this->column, 'value' => $this->value);
				
				// default case, it is a normal col = value expression; value
				// will be replaced w/ '?' and will be inserted later using PDO bindValue()
				if ($this->ignoreStringCase) {
					$sb .= $this->getDb()->ignoreCase($field) . $this->comparison . $this->getDb()->ignoreCase(':p'.count($params));
				} else {
					$sb .= $field . $this->comparison . ':p'.count($params);
				}
				
			}
		} else {

			// value is null, which means it was either not specified or specifically
			// set to null.
			if ($this->comparison === Criteria::EQUAL || $this->comparison === Criteria::ISNULL) {
				$sb .= $field . Criteria::ISNULL;
			} elseif ($this->comparison === Criteria::NOT_EQUAL || $this->comparison === Criteria::ISNOTNULL) {
				$sb .= $field . Criteria::ISNOTNULL;
			} else {
				// for now throw an exception, because not sure how to interpret this
				throw new PropelException("Could not build SQL for expression: $field " . $this->comparison . " NULL");
			}

		}
	}
				
	/**
	 * This method checks another Criteria to see if they contain
	 * the same attributes and hashtable entries.
	 * @return     boolean
	 */
	public function equals($obj)
	{
		// TODO: optimize me with early outs
		if ($this === $obj) {
			return true;
		}

		if (($obj === null) || !($obj instanceof Criterion)) {
			return false;
		}

		$crit = $obj;

		$isEquiv = ( ( ($this->table === null && $crit->getTable() === null)
			|| ( $this->table !== null && $this->table === $crit->getTable() )
						  )
			&& $this->column === $crit->getColumn()
			&& $this->comparison === $crit->getComparison());

		// check chained criterion

		$clausesLength = count($this->clauses);
		$isEquiv &= (count($crit->getClauses()) == $clausesLength);
		$critConjunctions = $crit->getConjunctions();
		$critClauses = $crit->getClauses();
		for ($i=0; $i < $clausesLength && $isEquiv; $i++) {
			$isEquiv &= ($this->conjunctions[$i] === $critConjunctions[$i]);
			$isEquiv &= ($this->clauses[$i] === $critClauses[$i]);
		}

		if ($isEquiv) {
			$isEquiv &= $this->value === $crit->getValue();
		}

		return $isEquiv;
	}

	/**
	 * Returns a hash code value for the object.
	 */
	public function hashCode()
	{
		$h = crc32(serialize($this->value)) ^ crc32($this->comparison);

		if ($this->table !== null) {
			$h ^= crc32($this->table);
		}

		if ($this->column !== null) {
			$h ^= crc32($this->column);
		}

		foreach ( $this->clauses as $clause ) {
			// TODO: i KNOW there is a php incompatibility with the following line
			// but i dont remember what it is, someone care to look it up and
			// replace it if it doesnt bother us?
			// $clause->appendPsTo($sb='',$params=array());
			$sb = '';
			$params = array();
			$clause->appendPsTo($sb,$params);
			$h ^= crc32(serialize(array($sb,$params)));
			unset ( $sb, $params );
		}

		return $h;
	}

	/**
	 * Get all tables from nested criterion objects
	 * @return     array
	 */
	public function getAllTables()
	{
		$tables = array();
		$this->addCriterionTable($this, $tables);
		return $tables;
	}

	/**
	 * method supporting recursion through all criterions to give
	 * us a string array of tables from each criterion
	 * @return     void
	 */
	private function addCriterionTable(Criterion $c, array &$s)
	{
		$s[] = $c->getTable();
		foreach ( $c->getClauses() as $clause ) {
			$this->addCriterionTable($clause, $s);
		}
	}
	
	/**
	 * get an array of all criterion attached to this
	 * recursing through all sub criterion
	 * @return     array Criterion[]
	 */
	public function getAttachedCriterion()
	{
		$criterions = array($this);
		foreach ($this->getClauses() as $criterion) {
			$criterions = array_merge($criterions, $criterion->getAttachedCriterion());
		}
		return $criterions;
	}
	
	/**
	 * Ensures deep cloning of attached objects
	 */
	public function __clone()
	{
		foreach ($this->clauses as $key => $criterion) {
			$this->clauses[$key] = clone $criterion;
		}
	}
}
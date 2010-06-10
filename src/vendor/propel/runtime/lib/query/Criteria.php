<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * This is a utility class for holding criteria information for a query.
 *
 * BasePeer constructs SQL statements based on the values in this class.
 *
 * @author     Hans Lellelid <hans@xmpl.org> (Propel)
 * @author     Kaspars Jaudzems <kaspars.jaudzems@inbox.lv> (Propel)
 * @author     Frank Y. Kim <frank.kim@clearink.com> (Torque)
 * @author     John D. McNally <jmcnally@collab.net> (Torque)
 * @author     Brett McLaughlin <bmclaugh@algx.net> (Torque)
 * @author     Eric Dobbs <eric@dobbse.net> (Torque)
 * @author     Henning P. Schmiedehausen <hps@intermeta.de> (Torque)
 * @author     Sam Joseph <sam@neurogrid.com> (Torque)
 * @version    $Revision: 1765 $
 * @package    propel.runtime.query
 */
class Criteria implements IteratorAggregate
{

	/** Comparison type. */
	const EQUAL = "=";

	/** Comparison type. */
	const NOT_EQUAL = "<>";

	/** Comparison type. */
	const ALT_NOT_EQUAL = "!=";

	/** Comparison type. */
	const GREATER_THAN = ">";

	/** Comparison type. */
	const LESS_THAN = "<";

	/** Comparison type. */
	const GREATER_EQUAL = ">=";

	/** Comparison type. */
	const LESS_EQUAL = "<=";

	/** Comparison type. */
	const LIKE = " LIKE ";

	/** Comparison type. */
	const NOT_LIKE = " NOT LIKE ";

	/** PostgreSQL comparison type */
	const ILIKE = " ILIKE ";

	/** PostgreSQL comparison type */
	const NOT_ILIKE = " NOT ILIKE ";

	/** Comparison type. */
	const CUSTOM = "CUSTOM";

	/** Comparison type for update */
	const CUSTOM_EQUAL = "CUSTOM_EQUAL";

	/** Comparison type. */
	const DISTINCT = "DISTINCT";

	/** Comparison type. */
	const IN = " IN ";

	/** Comparison type. */
	const NOT_IN = " NOT IN ";

	/** Comparison type. */
	const ALL = "ALL";

	/** Comparison type. */
	const JOIN = "JOIN";

	/** Binary math operator: AND */
	const BINARY_AND = "&";

	/** Binary math operator: OR */
	const BINARY_OR = "|";

	/** "Order by" qualifier - ascending */
	const ASC = "ASC";

	/** "Order by" qualifier - descending */
	const DESC = "DESC";

	/** "IS NULL" null comparison */
	const ISNULL = " IS NULL ";

	/** "IS NOT NULL" null comparison */
	const ISNOTNULL = " IS NOT NULL ";

	/** "CURRENT_DATE" ANSI SQL function */
	const CURRENT_DATE = "CURRENT_DATE";

	/** "CURRENT_TIME" ANSI SQL function */
	const CURRENT_TIME = "CURRENT_TIME";

	/** "CURRENT_TIMESTAMP" ANSI SQL function */
	const CURRENT_TIMESTAMP = "CURRENT_TIMESTAMP";

	/** "LEFT JOIN" SQL statement */
	const LEFT_JOIN = "LEFT JOIN";

	/** "RIGHT JOIN" SQL statement */
	const RIGHT_JOIN = "RIGHT JOIN";

	/** "INNER JOIN" SQL statement */
	const INNER_JOIN = "INNER JOIN";

	/** logical OR operator */
	const LOGICAL_OR = "OR";
	
	/** logical AND operator */
	const LOGICAL_AND = "AND";
	
	protected $ignoreCase = false;
	protected $singleRecord = false;
	
	/**
	 * Storage of select data. Collection of column names.
	 * @var        array
	 */
	protected $selectColumns = array();
	
	/**
	 * Storage of aliased select data. Collection of column names.
	 * @var        array
	 */
	protected $asColumns = array();
	
	/**
	 * Storage of select modifiers data. Collection of modifier names.
	 * @var        array
	 */
	protected $selectModifiers = array();
		
	/**
	 * Storage of conditions data. Collection of Criterion objects.
	 * @var        array
	 */
	protected $map = array();
	
	/**
	 * Storage of ordering data. Collection of column names.
	 * @var        array
	 */
	protected $orderByColumns = array();
	
	/**
	 * Storage of grouping data. Collection of column names.
	 * @var        array
	 */
	protected $groupByColumns = array();
	
	/**
	 * Storage of having data.
	 * @var        Criterion
	 */
	protected $having = null;
	
	/**
	 * Storage of join data. colleciton of Join objects.
	 * @var        array
	 */
	protected $joins = array();

	/**
	 * The name of the database.
	 * @var        string
	 */
	protected $dbName;

	/**
	 * The primary table for this Criteria.
	 * Useful in cases where there are no select or where
	 * columns.
	 * @var        string
	 */
	protected $primaryTableName;

	/** The name of the database as given in the contructor. */
	protected $originalDbName;

	/**
	 * To limit the number of rows to return.  <code>0</code> means return all
	 * rows.
	 */
	protected $limit = 0;

	/** To start the results at a row other than the first one. */
	protected $offset = 0;

  /**
   * Comment to add to the SQL query
	 * @var        string
   */
  protected $queryComment;
  
	// flag to note that the criteria involves a blob.
	protected $blobFlag = null;

	protected $aliases = array();

	protected $useTransaction = false;
	
	/**
	 * Storage for Criterions expected to be combined
	 * @var        array
	 */
	protected $namedCriterions = array();
	
	/**
	 * Creates a new instance with the default capacity which corresponds to
	 * the specified database.
	 *
	 * @param      dbName The dabase name.
	 */
	public function __construct($dbName = null)
	{
		$this->setDbName($dbName);
		$this->originalDbName = $dbName;
	}

	/**
	 * Implementing SPL IteratorAggregate interface.  This allows
	 * you to foreach () over a Criteria object.
	 */
	public function getIterator()
	{
		return new CriterionIterator($this);
	}

	/**
	 * Get the criteria map, i.e. the array of Criterions
	 * @return     array
	 */
	public function getMap()
	{
		return $this->map;
	}

	/**
	 * Brings this criteria back to its initial state, so that it
	 * can be reused as if it was new. Except if the criteria has grown in
	 * capacity, it is left at the current capacity.
	 * @return     void
	 */
	public function clear()
	{
		$this->map = array();
		$this->namedCriterions = array();
		$this->ignoreCase = false;
		$this->singleRecord = false;
		$this->selectModifiers = array();
		$this->selectColumns = array();
		$this->orderByColumns = array();
		$this->groupByColumns = array();
		$this->having = null;
		$this->asColumns = array();
		$this->joins = array();
		$this->dbName = $this->originalDbName;
		$this->offset = 0;
		$this->limit = -1;
		$this->blobFlag = null;
		$this->aliases = array();
		$this->useTransaction = false;
	}

	/**
	 * Add an AS clause to the select columns. Usage:
	 *
	 * <code>
	 * Criteria myCrit = new Criteria();
	 * myCrit->addAsColumn("alias", "ALIAS(".MyPeer::ID.")");
	 * </code>
	 *
	 * @param      string $name Wanted Name of the column (alias).
	 * @param      string $clause SQL clause to select from the table
	 *
	 * If the name already exists, it is replaced by the new clause.
	 *
	 * @return     Criteria A modified Criteria object.
	 */
	public function addAsColumn($name, $clause)
	{
		$this->asColumns[$name] = $clause;
		return $this;
	}

	/**
	 * Get the column aliases.
	 *
	 * @return     array An assoc array which map the column alias names
	 * to the alias clauses.
	 */
	public function getAsColumns()
	{
		return $this->asColumns;
	}

		/**
	 * Returns the column name associated with an alias (AS-column).
	 *
	 * @param      string $alias
	 * @return     string $string
	 */
	public function getColumnForAs($as)
	{
		if (isset($this->asColumns[$as])) {
			return $this->asColumns[$as];
		}
	}

	/**
	 * Allows one to specify an alias for a table that can
	 * be used in various parts of the SQL.
	 *
	 * @param      string $alias
	 * @param      string $table
	 *
	 * @return     Criteria A modified Criteria object.
	 */
	public function addAlias($alias, $table)
	{
		$this->aliases[$alias] = $table;
		
		return $this;
	}

	/**
	 * Remove an alias for a table (useful when merging Criterias).
	 *
	 * @param      string $alias
	 *
	 * @return     Criteria A modified Criteria object.
	 */
	public function removeAlias($alias)
	{
		unset($this->aliases[$alias]);
		
		return $this;
	}
	
	/**
	 * Returns the aliases for this Criteria
	 *
	 * @return     array
	 */
	public function getAliases()
	{
		return $this->aliases;
	}

	/**
	 * Returns the table name associated with an alias.
	 *
	 * @param      string $alias
	 * @return     string $string
	 */
	public function getTableForAlias($alias)
	{
		if (isset($this->aliases[$alias])) {
			return $this->aliases[$alias];
		}
	}

	/**
	 * Get the keys of the criteria map, i.e. the list of columns bearing a condition
	 * <code>
	 * print_r($c->keys());
	 *  => array('book.price', 'book.title', 'author.first_name')
	 * </code>
	 *
	 * @return     array
	 */
	public function keys()
	{
		return array_keys($this->map);
	}

	/**
	 * Does this Criteria object contain the specified key?
	 *
	 * @param      string $column [table.]column
	 * @return     boolean True if this Criteria object contain the specified key.
	 */
	public function containsKey($column)
	{
		// must use array_key_exists() because the key could
		// exist but have a NULL value (that'd be valid).
		return array_key_exists($column, $this->map);
	}

	/**
	 * Does this Criteria object contain the specified key and does it have a value set for the key 
	 *
	 * @param      string $column [table.]column
	 * @return     boolean True if this Criteria object contain the specified key and a value for that key
	 */
	public function keyContainsValue($column)
	{
		// must use array_key_exists() because the key could
		// exist but have a NULL value (that'd be valid).
		return (array_key_exists($column, $this->map) && ($this->map[$column]->getValue() !== null) );
	}
	
	/**
	 * Whether this Criteria has any where columns.
	 * 
	 * This counts conditions added with the add() method.
	 *
	 * @return     boolean
	 * @see        add()
	 */
	public function hasWhereClause()
	{
		return !empty($this->map);
	}

	/**
	 * Will force the sql represented by this criteria to be executed within
	 * a transaction.  This is here primarily to support the oid type in
	 * postgresql.  Though it can be used to require any single sql statement
	 * to use a transaction.
	 * @return     void
	 */
	public function setUseTransaction($v)
	{
		$this->useTransaction = (boolean) $v;
	}

	/**
	 * Whether the sql command specified by this criteria must be wrapped
	 * in a transaction.
	 *
	 * @return     boolean
	 */
	public function isUseTransaction()
	{
		return $this->useTransaction;
	}

	/**
	 * Method to return criteria related to columns in a table.
	 *
	 * Make sure you call containsKey($column) prior to calling this method,
	 * since no check on the existence of the $column is made in this method.
	 *
	 * @param      string $column Column name.
	 * @return     Criterion A Criterion object.
	 */
	public function getCriterion($column)
	{
		return $this->map[$column];
	}
	
	/**
	 * Method to return the latest Criterion in a table.
	 *
	 * @return     Criterion A Criterion or null no Criterion is added.
	 */
	public function getLastCriterion()
	{
		if($cnt = count($this->map)) {
			$map = array_values($this->map);
			return $map[$cnt - 1];
		}
		return null;
	}

	/**
	 * Method to return criterion that is not added automatically
	 * to this Criteria.  This can be used to chain the
	 * Criterions to form a more complex where clause.
	 *
	 * @param      string $column Full name of column (for example TABLE.COLUMN).
	 * @param      mixed $value
	 * @param      string $comparison
	 * @return     Criterion
	 */
	public function getNewCriterion($column, $value = null, $comparison = self::EQUAL)
	{
		return new Criterion($this, $column, $value, $comparison);
	}

	/**
	 * Method to return a String table name.
	 *
	 * @param      string $name Name of the key.
	 * @return     string The value of the object at key.
	 */
	public function getColumnName($name)
	{
		if (isset($this->map[$name])) {
			return $this->map[$name]->getColumn();
		}
		return null;
	}

	/**
	 * Shortcut method to get an array of columns indexed by table.
	 * <code>
	 * print_r($c->getTablesColumns());
	 *  => array(
	 *       'book'   => array('book.price', 'book.title'), 
	 *       'author' => array('author.first_name')
	 *     )
	 * </code>
	 *
	 * @return     array array(table => array(table.column1, table.column2))
	 */
	public function getTablesColumns()
	{
		$tables = array();
		foreach ($this->keys() as $key) {
			$tableName = substr($key, 0, strrpos($key, '.' ));
			$tables[$tableName][] = $key;
		}
		return $tables;
	}

	/**
	 * Method to return a comparison String.
	 *
	 * @param      string $key String name of the key.
	 * @return     string A String with the value of the object at key.
	 */
	public function getComparison($key)
	{
		if ( isset ( $this->map[$key] ) ) {
			return $this->map[$key]->getComparison();
		}
		return null;
	}

	/**
	 * Get the Database(Map) name.
	 *
	 * @return     string A String with the Database(Map) name.
	 */
	public function getDbName()
	{
		return $this->dbName;
	}

	/**
	 * Set the DatabaseMap name.  If <code>null</code> is supplied, uses value
	 * provided by <code>Propel::getDefaultDB()</code>.
	 *
	 * @param      string $dbName The Database (Map) name.
	 * @return     void
	 */
	public function setDbName($dbName = null)
	{
		$this->dbName = ($dbName === null ? Propel::getDefaultDB() : $dbName);
	}

	/**
	 * Get the primary table for this Criteria.
	 *
	 * This is useful for cases where a Criteria may not contain
	 * any SELECT columns or WHERE columns.  This must be explicitly
	 * set, of course, in order to be useful.
	 *
	 * @return     string
	 */
	public function getPrimaryTableName()
	{
		return $this->primaryTableName;
	}

	/**
	 * Sets the primary table for this Criteria.
	 *
	 * This is useful for cases where a Criteria may not contain
	 * any SELECT columns or WHERE columns.  This must be explicitly
	 * set, of course, in order to be useful.
	 *
	 * @param      string $v
	 */
	public function setPrimaryTableName($tableName)
	{
		$this->primaryTableName = $tableName;
	}

	/**
	 * Method to return a String table name.
	 *
	 * @param      string $name The name of the key.
	 * @return     string The value of table for criterion at key.
	 */
	public function getTableName($name)
	{
		if (isset($this->map[$name])) {
			return $this->map[$name]->getTable();
		}
		return null;
	}

	/**
	 * Method to return the value that was added to Criteria.
	 *
	 * @param      string $name A String with the name of the key.
	 * @return     mixed The value of object at key.
	 */
	public function getValue($name)
	{
		if (isset($this->map[$name])) {
			return $this->map[$name]->getValue();
		}
		return null;
	}

	/**
	 * An alias to getValue() -- exposing a Hashtable-like interface.
	 *
	 * @param      string $key An Object.
	 * @return     mixed The value within the Criterion (not the Criterion object).
	 */
	public function get($key)
	{
		return $this->getValue($key);
	}

	/**
	 * Overrides Hashtable put, so that this object is returned
	 * instead of the value previously in the Criteria object.
	 * The reason is so that it more closely matches the behavior
	 * of the add() methods. If you want to get the previous value
	 * then you should first Criteria.get() it yourself. Note, if
	 * you attempt to pass in an Object that is not a String, it will
	 * throw a NPE. The reason for this is that none of the add()
	 * methods support adding anything other than a String as a key.
	 *
	 * @param      string $key
	 * @param      mixed $value
	 * @return     Instance of self.
	 */
	public function put($key, $value)
	{
		return $this->add($key, $value);
	}

	/**
	 * Copies all of the mappings from the specified Map to this Criteria
	 * These mappings will replace any mappings that this Criteria had for any
	 * of the keys currently in the specified Map.
	 *
	 * if the map was another Criteria, its attributes are copied to this
	 * Criteria, overwriting previous settings.
	 *
	 * @param      mixed $t Mappings to be stored in this map.
	 */
	public function putAll($t)
	{
		if (is_array($t)) {
			foreach ($t as $key=>$value) {
				if ($value instanceof Criterion) {
					$this->map[$key] = $value;
				} else {
					$this->put($key, $value);
				}
			}
		} elseif ($t instanceof Criteria) {
			$this->joins = $t->joins;
		}
	}

	/**
	 * This method adds a new criterion to the list of criterias.
	 * If a criterion for the requested column already exists, it is
	 * replaced. If is used as follow:
	 *
	 * <code>
	 * $crit = new Criteria();
	 * $crit->add($column, $value, Criteria::GREATER_THAN);
	 * </code>
	 *
	 * Any comparison can be used.
	 *
	 * The name of the table must be used implicitly in the column name,
	 * so the Column name must be something like 'TABLE.id'.
	 *
	 * @param      string $critOrColumn The column to run the comparison on, or Criterion object.
	 * @param      mixed $value
	 * @param      string $comparison A String.
	 *
	 * @return     A modified Criteria object.
	 */
	public function add($p1, $value = null, $comparison = null)
	{
		if ($p1 instanceof Criterion) {
			$this->map[$p1->getTable() . '.' . $p1->getColumn()] = $p1;
		} else {
			$criterion = new Criterion($this, $p1, $value, $comparison);
			$this->map[$p1] = $criterion;
		}
		return $this;
	}
	
	/**
	 * This method creates a new criterion but keeps it for later use with combine()
	 * Until combine() is called, the condition is not added to the query
	 *
	 * <code>
	 * $crit = new Criteria();
	 * $crit->addCond('cond1', $column1, $value1, Criteria::GREATER_THAN);
	 * $crit->addCond('cond2', $column2, $value2, Criteria::EQUAL);
	 * $crit->combine(array('cond1', 'cond2'), Criteria::LOGICAL_OR);
	 * </code>
	 *
	 * Any comparison can be used.
	 *
	 * The name of the table must be used implicitly in the column name,
	 * so the Column name must be something like 'TABLE.id'.
	 *
	 * @param      string $name name to combine the criterion later
	 * @param      string $p1 The column to run the comparison on, or Criterion object.
	 * @param      mixed $value
	 * @param      string $comparison A String.
	 *
	 * @return     A modified Criteria object.
	 */
	public function addCond($name, $p1, $value = null, $comparison = null)
	{
		if ($p1 instanceof Criterion) {
			$this->namedCriterions[$name] = $p1;
		} else {
			$criterion = new Criterion($this, $p1, $value, $comparison);
			$this->namedCriterions[$name] = $criterion;
		}
		return $this;
	}
	
	/**
	 * Combine several named criterions with a logical operator
	 * 
	 * @param      array $criterions array of the name of the criterions to combine
	 * @param      string $operator logical operator, either Criteria::LOGICAL_AND, or Criteria::LOGICAL_OR
	 * @param      string $name optional name to combine the criterion later
	 */
	public function combine($criterions = array(), $operator = self::LOGICAL_AND, $name = null)
	{
		$operatorMethod = (strtoupper($operator) == self::LOGICAL_AND) ? 'addAnd' : 'addOr';
		$namedCriterions = array();
		foreach ($criterions as $key) {
			if (array_key_exists($key, $this->namedCriterions)) {
				$namedCriterions[]= $this->namedCriterions[$key];
				unset($this->namedCriterions[$key]);
			} else {
				throw new PropelException('Cannot combine unknown condition ' . $key);
			}
		}
		$firstCriterion = array_shift($namedCriterions);
		foreach ($namedCriterions as $criterion) {
			$firstCriterion->$operatorMethod($criterion);
		}
		if ($name === null) {
			$this->add($firstCriterion, null, null);
		} else {
			$this->addCond($name, $firstCriterion, null, null);
		}
		
		return $this;
	}

	/**
	 * This is the way that you should add a join of two tables. 
	 * Example usage:
	 * <code>
	 * $c->addJoin(ProjectPeer::ID, FooPeer::PROJECT_ID, Criteria::LEFT_JOIN);
	 * // LEFT JOIN FOO ON PROJECT.ID = FOO.PROJECT_ID
	 * </code>
	 *
	 * @param      mixed $left A String with the left side of the join.
	 * @param      mixed $right A String with the right side of the join.
	 * @param      mixed $operator A String with the join operator
	 *                             among Criteria::INNER_JOIN, Criteria::LEFT_JOIN,
	 *                             and Criteria::RIGHT_JOIN
   *
	 * @return     Criteria A modified Criteria object.
	 */
	public function addJoin($left, $right, $operator = null)
	{
		$join = new Join();
    if (!is_array($left)) {
      // simple join
      $join->addCondition($left, $right);
    } else {
      // join with multiple conditions
      // deprecated: use addMultipleJoin() instead
      foreach ($left as $key => $value)
      {
        $join->addCondition($value, $right[$key]);
      }
    }
		$join->setJoinType($operator);
		
		return $this->addJoinObject($join);
	}

	/**
	 * Add a join with multiple conditions
	 * @see http://propel.phpdb.org/trac/ticket/167, http://propel.phpdb.org/trac/ticket/606
	 * 
	 * Example usage:
	 * $c->addMultipleJoin(array(
	 *     array(LeftPeer::LEFT_COLUMN, RightPeer::RIGHT_COLUMN),  // if no third argument, defaults to Criteria::EQUAL
	 *     array(FoldersPeer::alias( 'fo', FoldersPeer::LFT ), FoldersPeer::alias( 'parent', FoldersPeer::RGT ), Criteria::LESS_EQUAL )
	 *   ),
	 *   Criteria::LEFT_JOIN
 	 * );
	 * 
	 * @see        addJoin()
	 * @param      array $conditions An array of conditions, each condition being an array (left, right, operator)
	 * @param      string $joinType  A String with the join operator. Defaults to an implicit join.
	 *
	 * @return     Criteria A modified Criteria object.
	 */
	public function addMultipleJoin($conditions, $joinType = null) 
  {
		$join = new Join();
		foreach ($conditions as $condition) {
		  $join->addCondition($condition[0], $condition[1], isset($condition[2]) ? $condition[2] : Criteria::EQUAL);
		}
		$join->setJoinType($joinType);
		
		return $this->addJoinObject($join);
	}
	
	/**
	 * Add a join object to the Criteria
	 *
	 * @param Join $join A join object
	 *
	 * @return Criteria A modified Criteria object
	 */
	public function addJoinObject(Join $join)
	{
	  if (!in_array($join, $this->joins)) { // compare equality, NOT identity
			$this->joins[] = $join;
		}
		return $this;
	}


	/**
	 * Get the array of Joins.
	 * @return     array Join[]
	 */
	public function getJoins()
	{
		return $this->joins;
	}

	/**
	 * Adds "ALL" modifier to the SQL statement.
	 * @return     Criteria Modified Criteria object (for fluent API)
	 */
	public function setAll()
	{
		$this->removeSelectModifier(self::DISTINCT);
		$this->addSelectModifier(self::ALL);
		
		return $this;
	}

	/**
	 * Adds "DISTINCT" modifier to the SQL statement.
	 * @return     Criteria Modified Criteria object (for fluent API)
	 */
	public function setDistinct()
	{
		$this->removeSelectModifier(self::ALL);
		$this->addSelectModifier(self::DISTINCT);
		
		return $this;
	}
	
	/**
	 * Adds a modifier to the SQL statement.
	 * e.g. self::ALL, self::DISTINCT, 'SQL_CALC_FOUND_ROWS', 'HIGH_PRIORITY', etc.
	 *
	 * @param      string $modifier The modifier to add 
	 *
	 * @return     Criteria Modified Criteria object (for fluent API)
	 */
	public function addSelectModifier($modifier)
	{
		//only allow the keyword once
		if (!$this->hasSelectModifier($modifier)) {
			$this->selectModifiers[] = $modifier;
		}
		
		return $this;
	}
	
	/**
	 * Removes a modifier to the SQL statement.
	 * Checks for existence before removal
	 *
	 * @param      string $modifier The modifier to add 
	 *
	 * @return     Criteria Modified Criteria object (for fluent API)
	 */
	public function removeSelectModifier($modifier)
	{
		$this->selectModifiers = array_values(array_diff($this->selectModifiers, array($modifier)));
		
		return $this;
	}
	
	/**
	 * Checks the existence of a SQL select modifier
	 *
	 * @param      string $modifier The modifier to add 
	 *
	 * @return     bool
	 */
	public function hasSelectModifier($modifier)
	{
		return in_array($modifier, $this->selectModifiers);
	}
	
	/**
	 * Sets ignore case.
	 *
	 * @param      boolean $b True if case should be ignored.
	 * @return     Criteria Modified Criteria object (for fluent API)
	 */
	public function setIgnoreCase($b)
	{
		$this->ignoreCase = (boolean) $b;
		return $this;
	}

	/**
	 * Is ignore case on or off?
	 *
	 * @return     boolean True if case is ignored.
	 */
	public function isIgnoreCase()
	{
		return $this->ignoreCase;
	}

	/**
	 * Set single record?  Set this to <code>true</code> if you expect the query
	 * to result in only a single result record (the default behaviour is to
	 * throw a PropelException if multiple records are returned when the query
	 * is executed).  This should be used in situations where returning multiple
	 * rows would indicate an error of some sort.  If your query might return
	 * multiple records but you are only interested in the first one then you
	 * should be using setLimit(1).
	 *
	 * @param      boolean $b Set to TRUE if you expect the query to select just one record.
	 * @return     Criteria Modified Criteria object (for fluent API)
	 */
	public function setSingleRecord($b)
	{
		$this->singleRecord = (boolean) $b;
		return $this;
	}

	/**
	 * Is single record?
	 *
	 * @return     boolean True if a single record is being returned.
	 */
	public function isSingleRecord()
	{
		return $this->singleRecord;
	}

	/**
	 * Set limit.
	 *
	 * @param      limit An int with the value for limit.
	 * @return     Criteria Modified Criteria object (for fluent API)
	 */
	public function setLimit($limit)
	{
		// TODO: do we enforce int here? 32bit issue if we do
		$this->limit = $limit;
		return $this;
	}

	/**
	 * Get limit.
	 *
	 * @return     int An int with the value for limit.
	 */
	public function getLimit()
	{
		return $this->limit;
	}

	/**
	 * Set offset.
	 *
	 * @param      int $offset An int with the value for offset.  (Note this values is
	 * 							cast to a 32bit integer and may result in truncatation)
	 * @return     Criteria Modified Criteria object (for fluent API)
	 */
	public function setOffset($offset)
	{
		$this->offset = (int) $offset;
		return $this;
	}

	/**
	 * Get offset.
	 *
	 * @return     An int with the value for offset.
	 */
	public function getOffset()
	{
		return $this->offset;
	}

	/**
	 * Add select column.
	 *
	 * @param      string $name Name of the select column.
	 * @return     Criteria Modified Criteria object (for fluent API)
	 */
	public function addSelectColumn($name)
	{
		$this->selectColumns[] = $name;
		return $this;
	}
	
	/**
	 * Set the query comment, that appears after the first verb in the SQL query
	 *
	 * @param      string $comment The comment to add to the query, without comment sign
	 * @return     Criteria Modified Criteria object (for fluent API)
	 */
	public function setComment($comment = null)
	{
		$this->queryComment = $comment;
		
		return $this;
	}
	
	/**
	 * Get the query comment, that appears after the first verb in the SQL query
	 *
	 * @return      string The comment to add to the query, without comment sign
	 */
	public function getComment()
	{
		return $this->queryComment;
	}
	
	/**
	 * Whether this Criteria has any select columns.
	 * 
	 * This will include columns added with addAsColumn() method.
	 *
	 * @return     boolean
	 * @see        addAsColumn()
	 * @see        addSelectColumn()
	 */
	public function hasSelectClause()
	{
		return (!empty($this->selectColumns) || !empty($this->asColumns));
	}
	
	/**
	 * Get select columns.
	 *
	 * @return     array An array with the name of the select columns.
	 */
	public function getSelectColumns()
	{
		return $this->selectColumns;
	}

	/**
	 * Clears current select columns.
	 *
	 * @return     Criteria Modified Criteria object (for fluent API)
	 */
	public function clearSelectColumns()
	{
		$this->selectColumns = $this->asColumns = array();
		return $this;
	}

	/**
	 * Get select modifiers.
	 *
	 * @return     An array with the select modifiers.
	 */
	public function getSelectModifiers()
	{
		return $this->selectModifiers;
	}

	/**
	 * Add group by column name.
	 *
	 * @param      string $groupBy The name of the column to group by.
	 * @return     A modified Criteria object.
	 */
	public function addGroupByColumn($groupBy)
	{
		$this->groupByColumns[] = $groupBy;
		return $this;
	}

	/**
	 * Add order by column name, explicitly specifying ascending.
	 *
	 * @param      name The name of the column to order by.
	 * @return     A modified Criteria object.
	 */
	public function addAscendingOrderByColumn($name)
	{
		$this->orderByColumns[] = $name . ' ' . self::ASC;
		return $this;
	}

	/**
	 * Add order by column name, explicitly specifying descending.
	 *
	 * @param      string $name The name of the column to order by.
	 * @return     Criteria Modified Criteria object (for fluent API)
	 */
	public function addDescendingOrderByColumn($name)
	{
		$this->orderByColumns[] = $name . ' ' . self::DESC;
		return $this;
	}

	/**
	 * Get order by columns.
	 *
	 * @return     array An array with the name of the order columns.
	 */
	public function getOrderByColumns()
	{
		return $this->orderByColumns;
	}

	/**
	 * Clear the order-by columns.
	 *
	 * @return     Criteria Modified Criteria object (for fluent API)
	 */
	public function clearOrderByColumns()
	{
		$this->orderByColumns = array();
		return $this;
	}

	/**
	 * Clear the group-by columns.
	 *
	 * @return     Criteria
	 */
	public function clearGroupByColumns()
	{
		$this->groupByColumns = array();
		return $this;
	}

	/**
	 * Get group by columns.
	 *
	 * @return     array
	 */
	public function getGroupByColumns()
	{
		return $this->groupByColumns;
	}

	/**
	 * Get Having Criterion.
	 *
	 * @return     Criterion A Criterion object that is the having clause.
	 */
	public function getHaving()
	{
		return $this->having;
	}
	
	/**
	 * Remove an object from the criteria.
	 *
	 * @param      string $key A string with the key to be removed.
	 * @return     mixed The removed value.
	 */
	public function remove($key)
	{
		if ( isset ( $this->map[$key] ) ) {
			$removed = $this->map[$key];
			unset ( $this->map[$key] );
			if ( $removed instanceof Criterion ) {
				return $removed->getValue();
			}
			return $removed;
		}
	}

	/**
	 * Build a string representation of the Criteria.
	 *
	 * @return     string A String with the representation of the Criteria.
	 */
	public function toString()
	{

		$sb = "Criteria:";
		try {

			$params = array();
			$sb .= "\nSQL (may not be complete): "
			  . BasePeer::createSelectSql($this, $params);

			$sb .= "\nParams: ";
			$paramstr = array();
			foreach ($params as $param) {
				$paramstr[] = $param['table'] . '.' . $param['column'] . ' => ' . var_export($param['value'], true);
			}
			$sb .= implode(", ", $paramstr);

		} catch (Exception $exc) {
			$sb .= "(Error: " . $exc->getMessage() . ")";
		}

		return $sb;
	}

	/**
	 * Returns the size (count) of this criteria.
	 * @return     int
	 */
	public function size()
	{
		return count($this->map);
	}

	/**
	 * This method checks another Criteria to see if they contain
	 * the same attributes and hashtable entries.
	 * @return     boolean
	 */
	public function equals($crit)
	{
		if ($crit === null || !($crit instanceof Criteria)) {
			return false;
		} elseif ($this === $crit) {
			return true;
		} elseif ($this->size() === $crit->size()) {

			// Important: nested criterion objects are checked

			$criteria = $crit; // alias
			if  ($this->offset          === $criteria->getOffset()
				&& $this->limit           === $criteria->getLimit()
				&& $this->ignoreCase      === $criteria->isIgnoreCase()
				&& $this->singleRecord    === $criteria->isSingleRecord()
				&& $this->dbName          === $criteria->getDbName()
				&& $this->selectModifiers === $criteria->getSelectModifiers()
				&& $this->selectColumns   === $criteria->getSelectColumns()
				&& $this->asColumns       === $criteria->getAsColumns()
				&& $this->orderByColumns  === $criteria->getOrderByColumns()
				&& $this->groupByColumns  === $criteria->getGroupByColumns()
				&& $this->aliases         === $criteria->getAliases()
			   ) // what about having ??
			{
				foreach ($criteria->keys() as $key) {
					if ($this->containsKey($key)) {
						$a = $this->getCriterion($key);
						$b = $criteria->getCriterion($key);
						if (!$a->equals($b)) {
							return false;
						}
					} else {
						return false;
					}
				}
				$joins = $criteria->getJoins();
				if (count($joins) != count($this->joins)) {
					return false;
				}
				foreach ($joins as $key => $join) {
					if (!$join->equals($this->joins[$key])) {
						return false;
					}
				}
				return true;
			} else {
				return false;
			}
		}
		return false;
	}
	
	/**
	 * Add the content of a Criteria to the current Criteria
	 * In case of conflict, the current Criteria keeps its properties
	 * 
	 * @param     Criteria $criteria The criteria to read properties from
	 * @param     string $operator The logical operator used to combine conditions
	 *              Defaults to Criteria::LOGICAL_AND, also accapts Criteria::LOGICAL_OR
	 *
	 * @return    Criteria The current criteria object
	 */
	public function mergeWith(Criteria $criteria, $operator = Criteria::LOGICAL_AND)
	{
		// merge limit
		$limit = $criteria->getLimit();
		if($limit != 0 && $this->getLimit() == 0) {
			$this->limit = $limit;
		}
		
		// merge offset
		$offset = $criteria->getOffset();
		if($offset != 0 && $this->getOffset() == 0) {
			$this->offset = $offset;
		}
		
		// merge select modifiers
		$selectModifiers = $criteria->getSelectModifiers();
		if ($selectModifiers && ! $this->selectModifiers){
			$this->selectModifiers = $selectModifiers;
		}
		
		// merge select columns
		$this->selectColumns = array_merge($this->getSelectColumns(), $criteria->getSelectColumns());
		
		// merge as columns
		$commonAsColumns = array_intersect_key($this->getAsColumns(), $criteria->getAsColumns());
		if (!empty($commonAsColumns)) {
			throw new PropelException('The given criteria contains an AsColumn with an alias already existing in the current object');
		}
		$this->asColumns = array_merge($this->getAsColumns(), $criteria->getAsColumns());
		
		// merge orderByColumns
		$orderByColumns = array_merge($this->getOrderByColumns(), $criteria->getOrderByColumns());
		$this->orderByColumns = array_unique($orderByColumns);

		// merge groupByColumns
		$groupByColumns = array_merge($this->getGroupByColumns(), $criteria->getGroupByColumns());
		$this->groupByColumns = array_unique($groupByColumns);
		
		// merge where conditions
		if ($operator == Criteria::LOGICAL_AND) {
			foreach ($criteria->getMap() as $key => $criterion) {
				if ($this->containsKey($key)) {
					$this->addAnd($criterion);
				} else {
					$this->add($criterion);
				}
			}
		} else {
			foreach ($criteria->getMap() as $key => $criterion) {
				$this->addOr($criterion);
			}
		}

		
		// merge having
		if ($having = $criteria->getHaving()) {
			if ($this->getHaving()) {
				$this->addHaving($this->getHaving()->addAnd($having));
			} else {
				$this->addHaving($having);
			}
		}
		
		// merge alias
		$commonAliases = array_intersect_key($this->getAliases(), $criteria->getAliases());
		if (!empty($commonAliases)) {
			throw new PropelException('The given criteria contains an alias already existing in the current object');
		}
		$this->aliases = array_merge($this->getAliases(), $criteria->getAliases());
		
		// merge join
		$this->joins = array_merge($this->getJoins(), $criteria->getJoins());
		
		return $this;
	}

	/**
	 * This method adds a prepared Criterion object to the Criteria as a having clause.
	 * You can get a new, empty Criterion object with the
	 * getNewCriterion() method.
	 *
	 * <p>
	 * <code>
	 * $crit = new Criteria();
	 * $c = $crit->getNewCriterion(BasePeer::ID, 5, Criteria::LESS_THAN);
	 * $crit->addHaving($c);
	 * </code>
	 *
	 * @param      having A Criterion object
	 *
	 * @return     A modified Criteria object.
	 */
	public function addHaving(Criterion $having)
	{
		$this->having = $having;
		return $this;
	}

	/**
	 * If a criterion for the requested column already exists, the condition is "AND"ed to the existing criterion (necessary for Propel 1.4 compatibility).
	 * If no criterion for the requested column already exists, the condition is "AND"ed to the latest criterion.
	 * If no criterion exist, the condition is added a new criterion
	 *
	 * Any comparison can be used.
	 *
	 * Supports a number of different signatures:
	 *  - addAnd(column, value, comparison)
	 *  - addAnd(column, value)
	 *  - addAnd(Criterion)
	 *
	 * @return     Criteria A modified Criteria object.
	 */
	public function addAnd($p1, $p2 = null, $p3 = null, $preferColumnCondition = true)
	{
		$criterion = ($p1 instanceof Criterion) ? $p1 : new Criterion($this, $p1, $p2, $p3);

		$key = $criterion->getTable() . '.' . $criterion->getColumn();
		if ($preferColumnCondition && $this->containsKey($key)) {
			// FIXME: addAnd() operates preferably on existing conditions on the same column
			// this may cause unexpected results, but it's there for BC with Propel 14
			$this->getCriterion($key)->addAnd($criterion);
		} else {
			// simply add the condition to the list - this is the expected behavior
			$this->add($criterion);
		}

		return $this;
	}
	
	/**
	 * If a criterion for the requested column already exists, the condition is "OR"ed to the existing criterion (necessary for Propel 1.4 compatibility).
	 * If no criterion for the requested column already exists, the condition is "OR"ed to the latest criterion.
	 * If no criterion exist, the condition is added a new criterion
	 *
	 * Any comparison can be used.
	 *
	 * Supports a number of different signatures:
	 *  - addOr(column, value, comparison)
	 *  - addOr(column, value)
	 *  - addOr(Criterion)
	 *
	 * @return     Criteria A modified Criteria object.
	 */
	public function addOr($p1, $p2 = null, $p3 = null, $preferColumnCondition = true)
	{
		$rightCriterion = ($p1 instanceof Criterion) ? $p1 : new Criterion($this, $p1, $p2, $p3);
		
		$key = $rightCriterion->getTable() . '.' . $rightCriterion->getColumn();
		if ($preferColumnCondition && $this->containsKey($key)) {
			// FIXME: addOr() operates preferably on existing conditions on the same column
			// this may cause unexpected results, but it's there for BC with Propel 14
			$leftCriterion = $this->getCriterion($key);
		} else {
			// fallback to the latest condition - this is the expected behavior
			$leftCriterion = $this->getLastCriterion();
		}

		if ($leftCriterion !== null) {
			// combine the given criterion with the existing one with an 'OR'
			$leftCriterion->addOr($rightCriterion);
		} else {
			// nothing to do OR / AND with, so make it first condition
			$this->add($rightCriterion);
		}

		return $this;
	}

	// Fluid Conditions

	/**
	 * Returns the current object if the condition is true,
	 * or a PropelConditionalProxy instance otherwise.
	 * Allows for conditional statements in a fluid interface.
	 *
	 * @param      bool $cond
	 *
	 * @return     PropelConditionalProxy|Criteria 
	 */
	public function _if($cond)
	{
		if($cond) {
			return $this;
		} else {
			return new PropelConditionalProxy($this);
		}
	}

	/**
	 * Returns a PropelConditionalProxy instance.
	 * Allows for conditional statements in a fluid interface.
	 *
	 * @param      bool $cond ignored
	 *
	 * @return     PropelConditionalProxy
	 */
	public function _elseif($cond)
	{
		return new PropelConditionalProxy($this);
	}

	/**
	 * Returns a PropelConditionalProxy instance.
	 * Allows for conditional statements in a fluid interface.
	 *
	 * @return     PropelConditionalProxy
	 */
	public function _else()
	{
		return new PropelConditionalProxy($this);
	}

	/**
	 * Returns the current object
	 * Allows for conditional statements in a fluid interface.
	 *
	 * @return     Criteria
	 */
	public function _endif()
	{
		return $this;
	}
	
	/**
	 * Ensures deep cloning of attached objects
	 */
	public function __clone()
	{
		foreach ($this->map as $key => $criterion) {
			$this->map[$key] = clone $criterion;
		}
		foreach ($this->joins as $key => $join) {
			$this->joins[$key] = clone $join;
		}
		if (null !== $this->having) {
			$this->having = clone $this->having;
		}
	}
	
}

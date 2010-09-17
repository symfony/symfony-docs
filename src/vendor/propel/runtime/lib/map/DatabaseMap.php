<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * DatabaseMap is used to model a database.
 *
 * GENERAL NOTE
 * ------------
 * The propel.map classes are abstract building-block classes for modeling
 * the database at runtime.  These classes are similar (a lite version) to the
 * propel.engine.database.model classes, which are build-time modeling classes.
 * These classes in themselves do not do any database metadata lookups.
 *
 * @author     Hans Lellelid <hans@xmpl.org> (Propel)
 * @author     John D. McNally <jmcnally@collab.net> (Torque)
 * @author     Daniel Rall <dlr@collab.net> (Torque)
 * @version    $Revision: 1802 $
 * @package    propel.runtime.map
 */
class DatabaseMap
{

  /** @var string Name of the database. */
  protected $name;

  /** @var array TableMap[] Tables in the database, using table name as key */ 
  protected $tables = array();
  
  /** @var array TableMap[] Tables in the database, using table phpName as key */
  protected $tablesByPhpName = array();

  /**
   * Constructor.
   *
   * @param      string $name Name of the database.
   */
  public function __construct($name)
  {
    $this->name = $name;
  }
  
  /**
   * Get the name of this database.
   *
   * @return     string The name of the database.
   */
  public function getName()
  {
    return $this->name;
  }
  
  /**
   * Add a new table to the database by name.
   *
   * @param      string $tableName The name of the table.
   * @return     TableMap The newly created TableMap.
   */
  public function addTable($tableName)
  {
    $this->tables[$tableName] = new TableMap($tableName, $this);
    return $this->tables[$tableName];
  }
  
  /**
   * Add a new table object to the database.
   *
   * @param      TableMap $table The table to add
   */
  public function addTableObject(TableMap $table)
  {
    $table->setDatabaseMap($this);
    $this->tables[$table->getName()] = $table;
    $this->tablesByPhpName[$table->getClassname()] = $table;
  }
  
  /**
   * Add a new table to the database, using the tablemap class name.
   *
   * @param      string $tableMapClass The name of the table map to add
   * @return     TableMap The TableMap object
   */
  public function addTableFromMapClass($tableMapClass)
  {
    $table = new $tableMapClass();
    if(!$this->hasTable($table->getName())) {
      $this->addTableObject($table);
      return $table;
    } else {
      return $this->getTable($table->getName());
    }
  }
  
  /**
   * Does this database contain this specific table?
   *
   * @param      string $name The String representation of the table.
   * @return     boolean True if the database contains the table.
   */
  public function hasTable($name)
  {
    if ( strpos($name, '.') > 0) {
      $name = substr($name, 0, strpos($name, '.'));
    }
    return array_key_exists($name, $this->tables);
  }

  /**
   * Get a TableMap for the table by name.
   *
   * @param      string $name Name of the table.
   * @return     TableMap A TableMap
   * @throws     PropelException if the table is undefined
   */
  public function getTable($name)
  {
    if (!isset($this->tables[$name])) {
      throw new PropelException("Cannot fetch TableMap for undefined table: " . $name );
    }
    return $this->tables[$name];
  }

  /**
   * Get a TableMap[] of all of the tables in the database.
   *
   * @return     array A TableMap[].
   */
  public function getTables()
  {
    return $this->tables;
  }

  /**
   * Get a ColumnMap for the column by name.
   * Name must be fully qualified, e.g. book.AUTHOR_ID
   *
   * @param      $qualifiedColumnName Name of the column.
   * @return     ColumnMap A TableMap
   * @throws     PropelException if the table is undefined, or if the table is undefined
   */  
  public function getColumn($qualifiedColumnName)
  {
    list($tableName, $columnName) = explode('.', $qualifiedColumnName);
    return $this->getTable($tableName)->getColumn($columnName, false);
  }
  
  // deprecated methods
  
  /**
   * Does this database contain this specific table?
   *
   * @deprecated Use hasTable() instead
   * @param      string $name The String representation of the table.
   * @return     boolean True if the database contains the table.
   */
  public function containsTable($name)
  {
    return $this->hasTable($name);
  }
  
  public function getTableByPhpName($phpName)
  {
    if (array_key_exists($phpName, $this->tablesByPhpName)) {
      return $this->tablesByPhpName[$phpName];
    } else if (class_exists($tmClass = $phpName . 'TableMap')) {
      $this->addTableFromMapClass($tmClass);
      return $this->tablesByPhpName[$phpName];
    } else if (class_exists($tmClass = substr_replace($phpName, '\\map\\', strrpos($phpName, '\\'), 1) . 'TableMap')) {
      $this->addTableFromMapClass($tmClass);
      return $this->tablesByPhpName[$phpName];
    } else {
      throw new PropelException("Cannot fetch TableMap for undefined table phpName: " . $phpName);
    }
  }
  
  /** 
   * Convenience method to get the DBAdapter registered with Propel for this database. 
   * @return  DBAdapter
   * @see     Propel::getDB(string) 
   */ 
  public function getDBAdapter() 
  { 
    return Propel::getDB($this->name); 
  }  
}

<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * RelationMap is used to model a database relationship.
 *
 * GENERAL NOTE
 * ------------
 * The propel.map classes are abstract building-block classes for modeling
 * the database at runtime.  These classes are similar (a lite version) to the
 * propel.engine.database.model classes, which are build-time modeling classes.
 * These classes in themselves do not do any database metadata lookups.
 *
 * @author     Francois Zaninotto
 * @version    $Revision: 1728 $
 * @package    propel.runtime.map
 */
class RelationMap
{

  const
    // types
    MANY_TO_ONE = 1,
    ONE_TO_MANY = 2,
    ONE_TO_ONE = 3,
    MANY_TO_MANY = 4,
    // representations
    LOCAL_TO_FOREIGN = 0,
    LEFT_TO_RIGHT = 1;
    
  protected 
    $name,
    $type,
    $localTable,
    $foreignTable,
    $localColumns = array(),
    $foreignColumns = array(),
    $onUpdate, $onDelete;

  /**
   * Constructor.
   *
   * @param      string $name Name of the relation.
   */
  public function __construct($name)
  {
    $this->name = $name;
  }
  
  /**
   * Get the name of this relation.
   *
   * @return     string The name of the relation.
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Set the type
   *
   * @param      integer $type The relation type (either self::MANY_TO_ONE, self::ONE_TO_MANY, or self::ONE_TO_ONE)
   */
  public function setType($type)
  {
    $this->type = $type;
  }

  /**
   * Get the type
   *
   * @return      integer the relation type
   */
  public function getType()
  {
    return $this->type;
  }

  /**
   * Set the local table
   *
   * @param      TableMap $table The local table for this relationship
   */
  public function setLocalTable($table)
  {
    $this->localTable = $table;
  }

  /**
   * Get the local table
   *
   * @return      TableMap The local table for this relationship
   */
  public function getLocalTable()
  {
    return $this->localTable;
  }

  /**
   * Set the foreign table
   *
   * @param      TableMap $table The foreign table for this relationship
   */
  public function setForeignTable($table)
  {
    $this->foreignTable = $table;
  }

  /**
   * Get the foreign table
   *
   * @return    TableMap The foreign table for this relationship
   */
  public function getForeignTable()
  {
    return $this->foreignTable;
  }
  
  /**
   * Get the left table of the relation
   *
   * @return    TableMap The left table for this relationship
   */
  public function getLeftTable()
  {
  	return ($this->getType() == RelationMap::MANY_TO_ONE) ? $this->getLocalTable() : $this->getForeignTable();
  }

  /**
   * Get the right table of the relation
   *
   * @return    TableMap The right table for this relationship
   */
  public function getRightTable()
  {
  	return ($this->getType() == RelationMap::MANY_TO_ONE) ? $this->getForeignTable() : $this->getLocalTable();
  }
  
  /**
   * Add a column mapping
   *
   * @param   ColumnMap $local The local column
   * @param   ColumnMap $foreign The foreign column
   */
  public function addColumnMapping(ColumnMap $local, ColumnMap $foreign)
  {
    $this->localColumns[] = $local;
    $this->foreignColumns[] = $foreign;
  }
  
	/**
	 * Get an associative array mapping local column names to foreign column names
	 * The arrangement of the returned array depends on the $direction parameter:
	 *  - If the value is RelationMap::LOCAL_TO_FOREIGN, then the returned array is local => foreign
	 *  - If the value is RelationMap::LEFT_TO_RIGHT, then the returned array is left => right
	 *
	 * @param     int $direction How the associative array must return columns
	 * @return    Array Associative array (local => foreign) of fully qualified column names
	 */
	public function getColumnMappings($direction = RelationMap::LOCAL_TO_FOREIGN)
	{
		$h = array();
		if ($direction == RelationMap::LEFT_TO_RIGHT && $this->getType() == RelationMap::MANY_TO_ONE) {
				$direction = RelationMap::LOCAL_TO_FOREIGN;
		}
		for ($i=0, $size=count($this->localColumns); $i < $size; $i++) {
			if ($direction == RelationMap::LOCAL_TO_FOREIGN) {
				$h[$this->localColumns[$i]->getFullyQualifiedName()] = $this->foreignColumns[$i]->getFullyQualifiedName();
			} else {
				$h[$this->foreignColumns[$i]->getFullyQualifiedName()] = $this->localColumns[$i]->getFullyQualifiedName();
			}
		}
		return $h;
	}
	
	/**
	 * Returns true if the relation has more than one column mapping
	 *
	 * @return boolean
	 */
	public function isComposite()
	{
		return $this->countColumnMappings() > 1;
	}
	
	/**
	 * Return the number of column mappings
	 *
	 * @return int
	 */
	public function countColumnMappings()
	{
		return count($this->localColumns);
	}
  
  /**
   * Get the local columns
   *
   * @return      Array list of ColumnMap objects
   */
  public function getLocalColumns()
  {
    return $this->localColumns;
  }
  
  /**
   * Get the foreign columns
   *
   * @return      Array list of ColumnMap objects
   */
  public function getForeignColumns()
  {
    return $this->foreignColumns;
  }
  
	/**
   * Get the left columns of the relation
   *
   * @return    array of ColumnMap objects
   */
  public function getLeftColumns()
  {
  	return ($this->getType() == RelationMap::MANY_TO_ONE) ? $this->getLocalColumns() : $this->getForeignColumns();
  }

  /**
   * Get the right columns of the relation
   *
   * @return    array of ColumnMap objects
   */
  public function getRightColumns()
  {
  	return ($this->getType() == RelationMap::MANY_TO_ONE) ? $this->getForeignColumns() : $this->getLocalColumns();
  }


  /**
   * Set the onUpdate behavior
   *
   * @param      string $onUpdate
   */
  public function setOnUpdate($onUpdate)
  {
    $this->onUpdate = $onUpdate;
  }

  /**
   * Get the onUpdate behavior
   *
   * @return      integer the relation type
   */
  public function getOnUpdate()
  {
    return $this->onUpdate;
  }
  
  /**
   * Set the onDelete behavior
   *
   * @param      string $onDelete
   */
  public function setOnDelete($onDelete)
  {
    $this->onDelete = $onDelete;
  }

  /**
   * Get the onDelete behavior
   *
   * @return      integer the relation type
   */
  public function getOnDelete()
  {
    return $this->onDelete;
  }
  
  /**
   * Gets the symmetrical relation
   *
   * @return    RelationMap
   */
  public function getSymmetricalRelation()
  {
  	$localMapping = array($this->getLeftColumns(), $this->getRightColumns());
  	foreach ($this->getRightTable()->getRelations() as $relation) {
  		if ($localMapping == array($relation->getRightColumns(), $relation->getLeftColumns())) {
  			return $relation;
  		}
  	}
  }
}

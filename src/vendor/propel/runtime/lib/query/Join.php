<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * Data object to describe a join between two tables, for example
 * <pre>
 * table_a LEFT JOIN table_b ON table_a.id = table_b.a_id
 * </pre>
 *
 * @author     Francois Zaninotto (Propel)
 * @author     Hans Lellelid <hans@xmpl.org> (Propel)
 * @author     Kaspars Jaudzems <kaspars.jaudzems@inbox.lv> (Propel)
 * @author     Frank Y. Kim <frank.kim@clearink.com> (Torque)
 * @author     John D. McNally <jmcnally@collab.net> (Torque)
 * @author     Brett McLaughlin <bmclaugh@algx.net> (Torque)
 * @author     Eric Dobbs <eric@dobbse.net> (Torque)
 * @author     Henning P. Schmiedehausen <hps@intermeta.de> (Torque)
 * @author     Sam Joseph <sam@neurogrid.com> (Torque)
 * @package    propel.runtime.query
 */
class Join
{
  // default comparison type
	const EQUAL = "=";
	
	// the left parts of the join condition
	protected $left = array();

	// the right parts of the join condition
	protected $right = array();

	// the comparison operators for each pair of columns in the join condition
	protected $operator = array();
	
	// the type of the join (LEFT JOIN, ...), or null for an implicit join
	protected $joinType = null;
	
	// the number of conditions in the join
	protected $count = 0;

	/**
	 * Constructor
	 * Use it preferably with no arguments, and then use addCondition() and setJoinType()
	 * Syntax with arguments used mainly for backwards compatibility
	 *
	 * @param string $leftColumn  The left column of the join condition
	 *                            (may contain an alias name)
	 * @param string $rightColumn The right column of the join condition
	 *                            (may contain an alias name)
	 * @param string $joinType    The type of the join. Valid join types are null (implicit join),
	 *                            Criteria::LEFT_JOIN, Criteria::RIGHT_JOIN, and Criteria::INNER_JOIN
	 */
	public function __construct($leftColumn = null, $rightColumn = null, $joinType = null)
	{
		if(!is_null($leftColumn)) {
		  if (!is_array($leftColumn)) {
		    // simple join
		    $this->addCondition($leftColumn, $rightColumn);
		  } else {
		    // join with multiple conditions
		    if (count($leftColumn) != count($rightColumn) ) {
			    throw new PropelException("Unable to create join because the left column count isn't equal to the right column count");
		    }
		    foreach ($leftColumn as $key => $value)
		    {
		      $this->addCondition($value, $rightColumn[$key]);
		    }
		  }
		  $this->setJoinType($joinType);
		}
	}
	
	/**
	 * Join condition definition
	 *
	 * @param string $left     The left column of the join condition
	 *                         (may contain an alias name)
	 * @param string $right    The right column of the join condition
	 *                         (may contain an alias name)
	 * @param string $operator The comparison operator of the join condition, default Join::EQUAL 
	 */
	public function addCondition($left, $right, $operator = self::EQUAL)
	{
		$this->left[] = $left;
		$this->right[] = $right;
		$this->operator[] = $operator;
		$this->count++;
	}
	
	/**
	 * Retrieve the number of conditions in the join
	 *
	 * @return integer The number of conditions in the join
	 */
	public function countConditions()
	{
	  return $this->count;
	}
	
	/**
	 * Return an array of the join conditions
	 *
	 * @return array An array of arrays representing (left, comparison, right) for each condition
	 */
	public function getConditions()
	{
	  $conditions = array();
	  for ($i=0; $i < $this->count; $i++) { 
	    $conditions[] = array(
	      'left'     => $this->getLeftColumn($i), 
	      'operator' => $this->getOperator($i),
	      'right'    => $this->getRightColumn($i)
	    );
	  }
	  return $conditions;
	}

  /**
   * @return     the comparison operator for the join condition
   */
  public function getOperator($index = 0)
  {
    return $this->operator[$index];
  }
	
	public function getOperators()
	{
	  return $this->operator;
	}
  
	/**
	 * Set the join type
	 *
	 * @param string  $joinType The type of the join. Valid join types are
	 *        null (adding the join condition to the where clause),
	 *        Criteria::LEFT_JOIN(), Criteria::RIGHT_JOIN(), and Criteria::INNER_JOIN()
	 */
	public function setJoinType($joinType = null)
	{
	  $this->joinType = $joinType;
	}
	
	/**
	 * Get the join type
	 *
	 * @return string The type of the join, i.e. Criteria::LEFT_JOIN(), ...,
	 *         or null for adding the join condition to the where Clause
	 */
	public function getJoinType()
	{
		return $this->joinType;
	}

	/**
	 * @return     the left column of the join condition
	 */
	public function getLeftColumn($index = 0)
	{
		return $this->left[$index];
	}
	
	/**
	 * @return     all right columns of the join condition
	 */
	public function getLeftColumns() 
	{
		return $this->left;
	}


	public function getLeftColumnName($index = 0)
	{
		return substr($this->left[$index], strrpos($this->left[$index], '.') + 1);
	}

	public function getLeftTableName($index = 0)
	{
		return substr($this->left[$index], 0, strrpos($this->left[$index], '.'));
	}

	/**
	 * @return     the right column of the join condition
	 */
	public function getRightColumn($index = 0)
	{
		return $this->right[$index];
	}
	
	/**
	 * @return     all right columns of the join condition
	 */
	public function getRightColumns() 
	{
		return $this->right;
	}

	public function getRightColumnName($index = 0)
	{
		return substr($this->right[$index], strrpos($this->right[$index], '.') + 1);
	}

	public function getRightTableName($index = 0)
	{
		return substr($this->right[$index], 0, strrpos($this->right[$index], '.'));
	}

	public function equals($join)
	{
		return $join !== null 
				&& $join instanceof Join
				&& $this->joinType == $join->getJoinType()
				&& $this->getConditions() == $join->getConditions();
	}
	
	/**
	 * returns a String representation of the class,
	 * mainly for debugging purposes
	 *
	 * @return string     A String representation of the class
	 */
	public function toString()
	{
		$result = '';
		if ($this->joinType !== null) {
			$result .= $this->joinType . ' : ';
		}
		foreach ($this->getConditions() as $index => $condition) {
		  $result .= implode($condition);
		  if ($index + 1 < $this->count) {
				$result .= ' AND ';
			}
		}
    $result .= '(ignoreCase not considered)';
    
		return $result;
	}
	
	public function __toString()
	{
		return $this->toString();
	}
}
 
<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * Class for iterating over a list of Propel objects
 *
 * @author     Francois Zaninotto
 * @package    propel.runtime.collection
 */
class PropelObjectCollection extends PropelCollection
{	
	
	/**
	 * Save all the elements in the collection
	 */
	public function save($con = null)
	{
		if (null === $con) {
			$con = $this->getConnection(Propel::CONNECTION_WRITE);
		}
		$con->beginTransaction();
		try {
			foreach ($this as $element) {
				$element->save($con);
			}
			$con->commit();
		} catch (PropelException $e) {
			$con->rollback();
			throw $e;
		}
	}
	
	/**
	 * Delete all the elements in the collection
	 */
	public function delete($con = null)
	{
		if (null === $con) {
			$con = $this->getConnection(Propel::CONNECTION_WRITE);
		}
		$con->beginTransaction();
		try {
			foreach ($this as $element) {
				$element->delete($con);
			}
			$con->commit();
		} catch (PropelException $e) {
			$con->rollback();
			throw $e;
		}
	}

	/**
	 * Get an array of the primary keys of all the objects in the collection
	 *
	 * @return    array The list of the primary keys of the collection
	 */
	public function getPrimaryKeys($usePrefix = true)
	{
		$ret = array();
		foreach ($this as $key => $obj) {
			$key = $usePrefix ? ($this->getModel() . '_' . $key) : $key;
			$ret[$key]= $obj->getPrimaryKey();
		}
		
		return $ret;
	}

	/**
	 * Populates the collection from an array
	 * Each object is populated from an array and the result is stored
	 * Does not empty the collection before adding the data from the array
	 *
	 * @param    array $arr
	 */
	public function fromArray($arr)
	{
		$class = $this->getModel();
		foreach ($arr as $element) {
			$obj = new $class();
			$obj->fromArray($element);
			$this->append($obj);
		}
	}
	
	/**
	 * Get an array representation of the collection
	 * Each object is turned into an array and the result is returned
	 *
	 * @param     string $keyColumn If null, the returned array uses an incremental index.
	 *              Otherwise, the array is indexed using the specified column
	 * @param     boolean $usePrefix If true, the returned array prefixes keys 
	 *              with the model class name ('Article_0', 'Article_1', etc).
	 *
	 * <code>
	 * $bookCollection->toArray(); 
	 * array(
	 *  0 => array('Id' => 123, 'Title' => 'War And Peace'),
	 *  1 => array('Id' => 456, 'Title' => 'Don Juan'),
	 * )
	 * $bookCollection->toArray('Id'); 
	 * array(
	 *  123 => array('Id' => 123, 'Title' => 'War And Peace'),
	 *  456 => array('Id' => 456, 'Title' => 'Don Juan'),
	 * )
	 * $bookCollection->toArray(null, true); 
	 * array(
	 *  'Book_0' => array('Id' => 123, 'Title' => 'War And Peace'),
	 *  'Book_1' => array('Id' => 456, 'Title' => 'Don Juan'),
	 * )
	 * </code>
	 * @return    array
	 */
	public function toArray($keyColumn = null, $usePrefix = false)
	{
		$ret = array();
		$keyGetterMethod = 'get' . $keyColumn;
		foreach ($this as $key => $obj) {
			$key = null === $keyColumn ? $key : $obj->$keyGetterMethod();
			$key = $usePrefix ? ($this->getModel() . '_' . $key) : $key;
			$ret[$key] = $obj->toArray();
		}
		
		return $ret;
	}
	
	/**
	 * Get an array representation of the collection
	 *
	 * @param     string $keyColumn If null, the returned array uses an incremental index.
	 *              Otherwise, the array is indexed using the specified column
	 * @param     boolean $usePrefix If true, the returned array prefixes keys 
	 *              with the model class name ('Article_0', 'Article_1', etc).
	 *
	 * <code>
	 * $bookCollection->getArrayCopy(); 
	 * array(
	 *  0 => $book0,
	 *  1 => $book1,
	 * )
	 * $bookCollection->getArrayCopy('Id'); 
	 * array(
	 *  123 => $book0,
	 *  456 => $book1,
	 * )
	 * $bookCollection->getArrayCopy(null, true); 
	 * array(
	 *  'Book_0' => $book0,
	 *  'Book_1' => $book1,
	 * )
	 * </code>
	 * @return    array
	 */
	public function getArrayCopy($keyColumn = null, $usePrefix = false)
	{
		if (null === $keyColumn && false === $usePrefix) {
			return parent::getArrayCopy();
		}
		$ret = array();
		$keyGetterMethod = 'get' . $keyColumn;
		foreach ($this as $key => $obj) {
			$key = null === $keyColumn ? $key : $obj->$keyGetterMethod();
			$key = $usePrefix ? ($this->getModel() . '_' . $key) : $key;
			$ret[$key] = $obj;
		}
		
		return $ret;
	}
	
	/**
	 * Get an associative array representation of the collection
	 * The first parameter specifies the column to be used for the key,
	 * And the seconf for the value.
	 * <code>
	 * $res = $coll->toKeyValue('Id', 'Name');
	 * </code>
	 *
	 * @return    array
	 */
	public function toKeyValue($keyColumn = 'PrimaryKey', $valueColumn = null)
	{
		$ret = array();
		$keyGetterMethod = 'get' . $keyColumn;
		$valueGetterMethod = (null === $valueColumn) ? '__toString' : ('get' . $valueColumn);
		foreach ($this as $obj) {
			$ret[$obj->$keyGetterMethod()] = $obj->$valueGetterMethod();
		}
		
		return $ret;
	}
	
	/**
	 * Makes an additional query to populate the objects related to the collection objects
	 * by a certain relation
	 *
	 * @param     string    $relation Relation name (e.g. 'Book')
	 * @param     Criteria  $criteria Optional Criteria object to filter the related object collection
	 * @param     PropelPDO $con      Optional connection object
	 *
	 * @return    PropelObjectCollection the list of related objects
	 */
	public function populateRelation($relation, $criteria = null, $con = null)
	{
		if (!Propel::isInstancePoolingEnabled()) {
			throw new PropelException('populateRelation() needs instance pooling to be enabled prior to populating the collection');
		}
		$relationMap = $this->getFormatter()->getTableMap()->getRelation($relation);
		$symRelationMap = $relationMap->getSymmetricalRelation();
		
		// query the db for the related objects
		$useMethod = 'use' . $symRelationMap->getName() . 'Query';
		$query = PropelQuery::from($relationMap->getRightTable()->getPhpName());
		if (null !== $criteria) {
			$query->mergeWith($criteria);
		}
		$relatedObjects = $query
			->$useMethod()
				->filterByPrimaryKeys($this->getPrimaryKeys())
			->endUse()
			->find($con);
			
		// associate the related objects to the main objects
		if ($relationMap->getType() == RelationMap::ONE_TO_MANY) {
			$getMethod = 'get' . $symRelationMap->getName();
			$addMethod = 'add' . $relationMap->getName();
			foreach ($relatedObjects as $object) {
				$mainObj = $object->$getMethod();  // instance pool is used here to avoid a query
				$mainObj->$addMethod($object);
			}
		} elseif ($relationMap->getType() == RelationMap::MANY_TO_ONE) {
			// nothing to do; the instance pool will catch all calls to getRelatedObject()
			// and return the object in memory
		} else {
			throw new PropelException('populateRelation() does not support this relation type');
		}
		
		return $relatedObjects;
	}

}

?>
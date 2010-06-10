<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * Object formatter for Propel query
 * format() returns a PropelObjectCollection of Propel model objects
 *
 * @author     Francois Zaninotto
 * @version    $Revision: 1733 $
 * @package    propel.runtime.formatter
 */
class PropelObjectFormatter extends PropelFormatter
{
	protected $collectionName = 'PropelObjectCollection';
	
	public function format(PDOStatement $stmt)
	{
		$this->checkInit();
		if($class = $this->collectionName) {
			$collection = new $class();
			$collection->setModel($this->class);
			$collection->setFormatter($this);
		} else {
			$collection = array();
		}
		if ($this->isWithOneToMany()) {
			if ($this->hasLimit) {
				throw new PropelException('Cannot use limit() in conjunction with with() on a one-to-many relationship. Please remove the with() call, or the limit() call.');
			}
			$pks = array();
			while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
				$object = $this->getAllObjectsFromRow($row);
				$pk = $object->getPrimaryKey();
				if (!in_array($pk, $pks)) {
					$collection[] = $object;
					$pks[] = $pk;
				}
			}
		} else {
			// only many-to-one relationships
			while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
				$collection[] =  $this->getAllObjectsFromRow($row);
			}
		}
		$stmt->closeCursor();
		
		return $collection;
	}
	
	public function formatOne(PDOStatement $stmt)
	{
		$this->checkInit();
		$result = null;
		while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$result = $this->getAllObjectsFromRow($row);
		}
		$stmt->closeCursor();
		
		return $result;
	}
	
	public function isObjectFormatter()
	{
		return true;
	}

	/**
	 * Hydrates a series of objects from a result row
	 * The first object to hydrate is the model of the Criteria
	 * The following objects (the ones added by way of ModelCriteria::with()) are linked to the first one
	 *
	 *  @param    array  $row associative array indexed by column number,
	 *                   as returned by PDOStatement::fetch(PDO::FETCH_NUM)
	 *
	 * @return    BaseObject
	 */
	public function getAllObjectsFromRow($row)
	{
		// main object
		list($obj, $col) = call_user_func(array($this->peer, 'populateObject'), $row);
		// related objects added using with()
		foreach ($this->getWith() as $class => $modelWith) {
			list($endObject, $col) = call_user_func(array($modelWith->getModelPeerName(), 'populateObject'), $row, $col);
			// as we may be in a left join, the endObject may be empty
			// in which case it should not be related to the previous object
			if (null === $endObject || $endObject->isPrimaryKeyNull()) {
				continue;
			}
			if (isset($hydrationChain)) {
				$hydrationChain[$class] = $endObject;
			} else {
				$hydrationChain = array($class => $endObject);
			}
			$startObject = $modelWith->isPrimary() ? $obj : $hydrationChain[$modelWith->getRelatedClass()];
			call_user_func(array($startObject, $modelWith->getRelationMethod()), $endObject);
		}
		// columns added using withColumn()
		foreach ($this->getAsColumns() as $alias => $clause) {
			$obj->setVirtualColumn($alias, $row[$col]);
			$col++;
		}
		return $obj;
	}

}
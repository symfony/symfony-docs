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
 * format() returns a PropelOnDemandCollection that hydrates objects as the use iterates on the collection
 * This formatter consumes less memory than the PropelObjectFormatter, but doesn't use Instance Pool
 *
 * @author     Francois Zaninotto
 * @version    $Revision: 1733 $
 * @package    propel.runtime.formatter
 */
class PropelOnDemandFormatter extends PropelObjectFormatter
{
	protected $collectionName = 'PropelOnDemandCollection';
	protected $isSingleTableInheritance = false;
	
	public function init(ModelCriteria $criteria)
	{
		parent::init($criteria);
		$this->isSingleTableInheritance = $criteria->getTableMap()->isSingleTableInheritance();
		
		return $this;
	}
	
	public function format(PDOStatement $stmt)
	{
		$this->checkInit();
		if ($this->isWithOneToMany()) {
			throw new PropelException('PropelOnDemandFormatter cannot hydrate related objects using a one-to-many relationship. Try removing with() from your query.');
		}
		$class = $this->collectionName;
		$collection = new $class();
		$collection->setModel($this->class);
		$collection->initIterator($this, $stmt);
		
		return $collection;
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
		$col = 0;
		// main object
		$class = $this->isSingleTableInheritance ? call_user_func(array($his->peer, 'getOMClass'), $row, $col, false) : $this->class;
		$obj = $this->getSingleObjectFromRow($row, $class, $col);
		// related objects using 'with'
		foreach ($this->getWith() as $modelWith) {
			if ($modelWith->isSingleTableInheritance()) {
				$class = call_user_func(array($modelWith->getModelPeerName(), 'getOMClass'), $row, $col, false);
				$refl = new ReflectionClass($class);
				if ($refl->isAbstract()) {
					$col += constant($class . 'Peer::NUM_COLUMNS');
					continue;
				} 
			} else {
				$class = $modelWith->getModelName();
			}
			$endObject = $this->getSingleObjectFromRow($row, $class, $col);
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
		foreach ($this->getAsColumns() as $alias => $clause) {
			$obj->setVirtualColumn($alias, $row[$col]);
			$col++;
		}
		return $obj;
	}
	
}
<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * Array formatter for Propel query
 * format() returns a PropelArrayCollection of associative arrays
 *
 * @author     Francois Zaninotto
 * @version    $Revision: 1898 $
 * @package    propel.runtime.formatter
 */
class PropelArrayFormatter extends PropelFormatter
{
	protected $collectionName = 'PropelArrayCollection';
	protected $alreadyHydratedObjects = array();
	protected $emptyVariable;
	
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
		if ($this->isWithOneToMany() && $this->hasLimit) {
			throw new PropelException('Cannot use limit() in conjunction with with() on a one-to-many relationship. Please remove the with() call, or the limit() call.');
		}
		while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			if ($object = &$this->getStructuredArrayFromRow($row)) {
				$collection[] = $object;
			}
		}
		$this->currentObjects = array();
		$this->alreadyHydratedObjects = array();
		$stmt->closeCursor();
		
		return $collection;
	}

	public function formatOne(PDOStatement $stmt)
	{
		$this->checkInit();
		$result = null;
		while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			if ($object = &$this->getStructuredArrayFromRow($row)) {
				$result = &$object;
			}
		}
		$this->currentObjects = array();
		$this->alreadyHydratedObjects = array();
		$stmt->closeCursor();
		return $result;
	}

	/**
	 * Formats an ActiveRecord object
	 *
	 * @param BaseObject $record the object to format
	 *
	 * @return array The original record turned into an array
	 */
	public function formatRecord($record = null)
	{
		return $record ? $record->toArray() : array();
	}
	
	public function isObjectFormatter()
	{
		return false;
	}
	

	/**
	 * Hydrates a series of objects from a result row
	 * The first object to hydrate is the model of the Criteria
	 * The following objects (the ones added by way of ModelCriteria::with()) are linked to the first one
	 *
	 *  @param    array  $row associative array indexed by column number,
	 *                   as returned by PDOStatement::fetch(PDO::FETCH_NUM)
	 *
	 * @return    Array
	 */
	public function &getStructuredArrayFromRow($row)
	{
		$col = 0;
		
		// hydrate main object or take it from registry
		$mainObjectIsNew = false;
		$mainKey = call_user_func(array($this->peer, 'getPrimaryKeyHashFromRow'), $row);
		// we hydrate the main object even in case of a one-to-many relationship
		// in order to get the $col variable increased anyway
		$obj = $this->getSingleObjectFromRow($row, $this->class, $col);
		if (!isset($this->alreadyHydratedObjects[$this->class][$mainKey])) {
			$this->alreadyHydratedObjects[$this->class][$mainKey] = $obj->toArray();
			$mainObjectIsNew = true;
		}
		
		$hydrationChain = array();
		
		// related objects added using with()
		foreach ($this->getWith() as $relAlias => $modelWith) {
			
			// determine class to use
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
			
			// hydrate related object or take it from registry
			$key = call_user_func(array($modelWith->getModelPeerName(), 'getPrimaryKeyHashFromRow'), $row, $col);
			// we hydrate the main object even in case of a one-to-many relationship
			// in order to get the $col variable increased anyway
			$secondaryObject = $this->getSingleObjectFromRow($row, $class, $col);
			if (!isset($this->alreadyHydratedObjects[$relAlias][$key])) {
				
				if ($secondaryObject->isPrimaryKeyNull()) {
					$this->alreadyHydratedObjects[$relAlias][$key] = array();
				} else {
					$this->alreadyHydratedObjects[$relAlias][$key] = $secondaryObject->toArray();
				}
			}
				
			if ($modelWith->isPrimary()) {
				$arrayToAugment = &$this->alreadyHydratedObjects[$this->class][$mainKey];
			} else {
				$arrayToAugment = &$hydrationChain[$modelWith->getLeftPhpName()];
			}
			
			if ($modelWith->isAdd()) {
				if (!isset($arrayToAugment[$modelWith->getRelationName()]) || !in_array($this->alreadyHydratedObjects[$relAlias][$key], $arrayToAugment[$modelWith->getRelationName()])) {
					$arrayToAugment[$modelWith->getRelationName()][] = &$this->alreadyHydratedObjects[$relAlias][$key];
				}
			} else {
				$arrayToAugment[$modelWith->getRelationName()] = &$this->alreadyHydratedObjects[$relAlias][$key];
			}
				
			$hydrationChain[$modelWith->getRightPhpName()] = &$this->alreadyHydratedObjects[$relAlias][$key];
		}
		
		// columns added using withColumn()
		foreach ($this->getAsColumns() as $alias => $clause) {
			$this->alreadyHydratedObjects[$this->class][$mainKey][$alias] = $row[$col];
			$col++;
		}
		
		if ($mainObjectIsNew) {
			return $this->alreadyHydratedObjects[$this->class][$mainKey];
		} else {
			// we still need to return a reference to something to avoid a warning
			return $emptyVariable;
		}
	}

}
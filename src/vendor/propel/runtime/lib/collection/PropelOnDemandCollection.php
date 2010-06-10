<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * Class for iterating over a statement and returning one Propel object at a time
 *
 * @author     Francois Zaninotto
 * @package    propel.runtime.collection
 */
class PropelOnDemandCollection extends PropelCollection
{
	protected
		$iterator,
		$currentRow, 
		$currentKey = -1,
		$isValid = null;
	
	public function initIterator(PropelFormatter $formatter, PDOStatement $stmt)
	{
		$this->iterator = new PropelOnDemandIterator($formatter, $stmt);
	}
	
	// IteratorAggregate Interface
	
	public function getIterator()
	{
		return $this->iterator;
	}

	// ArrayAccess Interface
	
	public function offsetExists($offset)
	{
		if ($offset == $this->currentKey) {
			return true;
		}
		throw new PropelException('The On Demand Collection does not allow acces by offset');
	}

	public function offsetGet($offset)
	{
		if ($offset == $this->currentKey) {
			return $this->currentRow;
		}
		throw new PropelException('The On Demand Collection does not allow acces by offset');
	}
	
	public function offsetSet($offset, $value)
	{
		throw new PropelException('The On Demand Collection is read only');
	}

	public function offsetUnset($offset)
	{
		throw new PropelException('The On Demand Collection is read only');
	}
	
	// Serializable Interface
	
	public function serialize()
	{
		throw new PropelException('The On Demand Collection cannot be serialized');
	}

	public function unserialize($data)
	{
		throw new PropelException('The On Demand Collection cannot be serialized');
	}
	
	// Countable Interface
	
	/**
	 * Returns the number of rows in the resultset
	 * Warning: this number is inaccurate for most databases. Do not rely on it for a portable application.
	 * 
	 * @return    int number of results
	 */
	public function count()
	{
		return $this->iterator->count();
	}
	
	// ArrayObject methods
	
	public function append($value)
	{
		throw new PropelException('The On Demand Collection is read only');
	}
	
	public function prepend($value)
	{
		throw new PropelException('The On Demand Collection is read only');
	}

	public function asort()
	{
		throw new PropelException('The On Demand Collection is read only');
	}
	
	public function exchangeArray($input)
	{
		throw new PropelException('The On Demand Collection is read only');
	}
	
	public function getArrayCopy()
	{
		throw new PropelException('The On Demand Collection does not allow acces by offset');
	}
	
	public function getFlags()
	{
		throw new PropelException('The On Demand Collection does not allow acces by offset');
	}
	
	public function ksort()
	{
		throw new PropelException('The On Demand Collection is read only');
	}
	
	public function natcasesort()
	{
		throw new PropelException('The On Demand Collection is read only');
	}
	
	public function natsort()
	{
		throw new PropelException('The On Demand Collection is read only');
	}
	
	public function setFlags($flags)
	{
		throw new PropelException('The On Demand Collection does not allow acces by offset');
	}
	
	public function uasort($cmp_function)
	{
		throw new PropelException('The On Demand Collection is read only');
	}
	
	public function uksort($cmp_function)
	{
		throw new PropelException('The On Demand Collection is read only');
	}
}
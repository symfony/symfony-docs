<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * PropelConfigurationIterator is used internally by PropelConfiguration to
 * build a flat array from nesting configuration arrays.
 *
 * @author     Veikko Mäkinen <veikko@veikko.fi>
 * @version    $Revision: 1612 $
 * @package    propel.runtime.config
 */
class PropelConfigurationIterator extends RecursiveIteratorIterator
{
	/**
	 * Node is a parent node
	 */
	const NODE_PARENT = 0;

	/**
	 * Node is an actual configuration item
	 */
	const NODE_ITEM = 1;

	/**
	 * Namespace stack when recursively iterating the configuration tree
	 *
	 * @var        array
	 */
	protected $namespaceStack = array();

	/**
	 * Current node type. Possible values: null (undefined), self::NODE_PARENT or self::NODE_ITEM
	 *
	 * @var        int
	 */
	protected $nodeType = null;

	/**
	 * Get current namespace
	 *
	 * @return     string
	 */
	public function getNamespace()
	{
		return implode('.', $this->namespaceStack);
	}

	/**
	 * Get current node type.
	 *
	 * @see        http://www.php.net/RecursiveIteratorIterator
	 * @return     int
	 *             - null (undefined)
	 *             - self::NODE_PARENT
	 *             - self::NODE_ITEM
	 */
	public function getNodeType()
	{
		return $this->nodeType;
	}

	/**
	 * Get the current element
	 *
	 * @see        http://www.php.net/RecursiveIteratorIterator
	 * @return     mixed
	 */
	public function current()
	{
		$current = parent::current();
		if (is_array($current)) {
			$this->namespaceStack[] = $this->key();
			$this->nodeType = self::NODE_PARENT;
		}
		else {
			$this->nodeType = self::NODE_ITEM;
		}

		return $current;
	}

	/**
	 * Called after current child iterator is invalid and right before it gets destructed.
	 *
	 * @see        http://www.php.net/RecursiveIteratorIterator
	 */
	public function endChildren()
	{
		if ($this->namespaceStack) {
			array_pop($this->namespaceStack);
		}
	}

}

?>

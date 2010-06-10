<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * This interface defines methods that must be implemented by all
 * business objects within the system to handle Node object.
 *
 * @author     Heltem <heltem@o2php.com> (Propel)
 * @version    $Revision: 1612 $
 * @package    propel.runtime.om
 */
interface NodeObject extends IteratorAggregate
{
	/**
	 * If object is saved without left/right values, set them as undefined (0)
	 *
	 * @param      PropelPDO $con	Connection to use.
	 * @return     void
	 * @throws     PropelException
	 */
	public function save(PropelPDO $con = null);

	/**
	 * Delete node and descendants
	 *
	 * @param      PropelPDO $con	Connection to use.
	 * @return     void
	 * @throws     PropelException
	 */
	public function delete(PropelPDO $con = null);

	/**
	 * Sets node properties to make it a root node.
	 *
	 * @return     object The current object (for fluent API support)
	 * @throws     PropelException
	 */
	public function makeRoot();

	/**
	 * Gets the level if set, otherwise calculates this and returns it
	 *
	 * @param      PropelPDO $con	Connection to use.
	 * @return     int
	 */
	public function getLevel(PropelPDO $con = null);

	/**
	 * Get the path to the node in the tree
	 *
	 * @param      PropelPDO $con	Connection to use.
	 * @return     array
	 */
	public function getPath(PropelPDO $con = null);

	/**
	 * Gets the number of children for the node (direct descendants)
	 *
	 * @param      PropelPDO $con	Connection to use.
	 * @return     int
	 */
	public function getNumberOfChildren(PropelPDO $con = null);

	/**
	 * Gets the total number of desceandants for the node
	 *
	 * @param      PropelPDO $con	Connection to use.
	 * @return     int
	 */
	public function getNumberOfDescendants(PropelPDO $con = null);

	/**
	 * Gets the children for the node
	 *
	 * @param      PropelPDO $con	Connection to use.
	 * @return     array
	 */
	public function getChildren(PropelPDO $con = null);

	/**
	 * Gets the descendants for the node
	 *
	 * @param      PropelPDO $con	Connection to use.
 	 * @return     array
	 */
	public function getDescendants(PropelPDO $con = null);

	/**
	 * Sets the level of the node in the tree
	 *
	 * @param      int $v new value
	 * @return     object The current object (for fluent API support)
	 */
	public function setLevel($level);

	/**
	 * Sets the children array of the node in the tree
	 *
	 * @param      array of Node $children	array of Propel node object
	 * @return     object The current object (for fluent API support)
	 */
	public function setChildren(array $children);

	/**
	 * Sets the parentNode of the node in the tree
	 *
	 * @param      Node $parent Propel node object
	 * @return     object The current object (for fluent API support)
	 */
	public function setParentNode(NodeObject $parent = null);

	/**
	 * Sets the previous sibling of the node in the tree
	 *
	 * @param      Node $node Propel node object
	 * @return     object The current object (for fluent API support)
	 */
	public function setPrevSibling(NodeObject $node = null);

	/**
	 * Sets the next sibling of the node in the tree
	 *
	 * @param      Node $node Propel node object
	 * @return     object The current object (for fluent API support)
	 */
	public function setNextSibling(NodeObject $node = null);

	/**
	 * Determines if the node is the root node
	 *
	 * @return     bool
	 */
	public function isRoot();

	/**
	 * Determines if the node is a leaf node
	 *
	 * @return     bool
	 */
	public function isLeaf();

	/**
	 * Tests if object is equal to $node
	 *
	 * @param      object $node	Propel object for node to compare to
	 * @return     bool
	 */
	public function isEqualTo(NodeObject $node);

	/**
	 * Tests if object has an ancestor
	 *
	 * @param      PropelPDO $con	Connection to use.
	 * @return     bool
	 */
	public function hasParent(PropelPDO $con = null);

	/**
	 * Determines if the node has children / descendants
	 *
	 * @return     bool
	 */
	public function hasChildren();

	/**
	 * Determines if the node has previous sibling
	 *
	 * @param      PropelPDO $con	Connection to use.
	 * @return     bool
	 */
	public function hasPrevSibling(PropelPDO $con = null);

	/**
	 * Determines if the node has next sibling
	 *
	 * @param      PropelPDO $con	Connection to use.
	 * @return     bool
	 */
	public function hasNextSibling(PropelPDO $con = null);

	/**
	 * Gets ancestor for the given node if it exists
	 *
	 * @param      PropelPDO $con	Connection to use.
	 * @return     mixed 		Propel object if exists else false
	 */
	public function retrieveParent(PropelPDO $con = null);

	/**
	 * Gets first child if it exists
	 *
	 * @param      PropelPDO $con	Connection to use.
	 * @return     mixed 		Propel object if exists else false
	 */
	public function retrieveFirstChild(PropelPDO $con = null);

	/**
	 * Gets last child if it exists
	 *
	 * @param      PropelPDO $con	Connection to use.
	 * @return     mixed 		Propel object if exists else false
	 */
	public function retrieveLastChild(PropelPDO $con = null);

	/**
	 * Gets prev sibling for the given node if it exists
	 *
	 * @param      PropelPDO $con	Connection to use.
	 * @return     mixed 		Propel object if exists else false
	 */
	public function retrievePrevSibling(PropelPDO $con = null);

	/**
	 * Gets next sibling for the given node if it exists
	 *
	 * @param      PropelPDO $con	Connection to use.
	 * @return     mixed 		Propel object if exists else false
	 */
	public function retrieveNextSibling(PropelPDO $con = null);

	/**
	 * Inserts as first child of destination node $parent
	 *
	 * @param      object $parent	Propel object for given destination node
	 * @param      PropelPDO $con	Connection to use.
	 * @return     object The current object (for fluent API support)
	 */
	public function insertAsFirstChildOf(NodeObject $parent, PropelPDO $con = null);

	/**
	 * Inserts as last child of destination node $parent
	 *
	 * @param      object $parent	Propel object for given destination node
	 * @param      PropelPDO $con	Connection to use.
	 * @return     object The current object (for fluent API support)
	 */
	public function insertAsLastChildOf(NodeObject $parent, PropelPDO $con = null);

	/**
	 * Inserts node as previous sibling to destination node $dest
	 *
	 * @param      object $dest	Propel object for given destination node
	 * @param      PropelPDO $con	Connection to use.
	 * @return     object The current object (for fluent API support)
	 */
	public function insertAsPrevSiblingOf(NodeObject $dest, PropelPDO $con = null);

	/**
	 * Inserts node as next sibling to destination node $dest
	 *
	 * @param      object $dest	Propel object for given destination node
	 * @param      PropelPDO $con	Connection to use.
	 * @return     object The current object (for fluent API support)
	 */
	public function insertAsNextSiblingOf(NodeObject $dest, PropelPDO $con = null);

	/**
	 * Moves node to be first child of $parent
	 *
	 * @param      object $parent	Propel object for destination node
	 * @param      PropelPDO $con Connection to use.
	 * @return     void
	 */
	public function moveToFirstChildOf(NodeObject $parent, PropelPDO $con = null);

	/**
	 * Moves node to be last child of $parent
	 *
	 * @param      object $parent	Propel object for destination node
	 * @param      PropelPDO $con Connection to use.
	 * @return     void
	 */
	public function moveToLastChildOf(NodeObject $parent, PropelPDO $con = null);

	/**
	 * Moves node to be prev sibling to $dest
	 *
	 * @param      object $dest	Propel object for destination node
	 * @param      PropelPDO $con Connection to use.
	 * @return     void
	 */
	public function moveToPrevSiblingOf(NodeObject $dest, PropelPDO $con = null);

	/**
	 * Moves node to be next sibling to $dest
	 *
	 * @param      object $dest	Propel object for destination node
	 * @param      PropelPDO $con Connection to use.
	 * @return     void
	 */
	public function moveToNextSiblingOf(NodeObject $dest, PropelPDO $con = null);

	/**
	 * Inserts node as parent of given node.
	 *
	 * @param      object $node  Propel object for given destination node
	 * @param      PropelPDO $con	Connection to use.
	 * @return     void
	 * @throws     Exception      When trying to insert node as parent of a root node
	 */
	public function insertAsParentOf(NodeObject $node, PropelPDO $con = null);

	/**
	 * Wraps the getter for the scope value
	 *
	 * @return     int
	 */
	public function getScopeIdValue();

	/**
	 * Set the value of scope column
	 *
	 * @param      int $v new value
	 * @return     object The current object (for fluent API support)
	 */
	public function setScopeIdValue($v);
} // NodeObject

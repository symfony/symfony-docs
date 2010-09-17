<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * This is a utility interface for all generated NodePeer classes in the system.
 *
 * @author     Heltem <heltem@o2php.com> (Propel)
 * @version    $Revision: 1612 $
 * @package    propel.runtime.util
 */
interface NodePeer
{
	/**
	 * Creates the supplied node as the root node.
	 *
	 * @param      object $node	Propel object for model
	 * @return     object		Inserted propel object for model
	 */
	public static function createRoot(NodeObject $node);

	/**
	 * Returns the root node for a given scope id
	 *
	 * @param      int $scopeId		Scope id to determine which root node to return
	 * @param      PropelPDO $con	Connection to use.
	 * @return     object			Propel object for root node
	 */
	public static function retrieveRoot($scopeId = 1, PropelPDO $con = null);

	/**
	 * Inserts $child as first child of destination node $parent
	 *
	 * @param      object $child	Propel object for child node
	 * @param      object $parent	Propel object for parent node
	 * @param      PropelPDO $con	Connection to use.
	 * @return     void
	 */
	public static function insertAsFirstChildOf(NodeObject $child, NodeObject $parent, PropelPDO $con = null);

	/**
	 * Inserts $child as last child of destination node $parent
	 *
	 * @param      object $child	Propel object for child node
	 * @param      object $parent	Propel object for parent node
	 * @param      PropelPDO $con	Connection to use.
	 * @return     void
	 */
	public static function insertAsLastChildOf(NodeObject $child, NodeObject $parent, PropelPDO $con = null);

	/**
	 * Inserts $sibling as previous sibling to destination node $node
	 *
	 * @param      object $node		Propel object for destination node
	 * @param      object $sibling	Propel object for source node
	 * @param      PropelPDO $con	Connection to use.
	 * @return     void
	 */
	public static function insertAsPrevSiblingOf(NodeObject $node, NodeObject $sibling, PropelPDO $con = null);

	/**
	 * Inserts $sibling as next sibling to destination node $node
	 *
	 * @param      object $node		Propel object for destination node
	 * @param      object $sibling	Propel object for source node
	 * @param      PropelPDO $con	Connection to use.
	 * @return     void
	 */
	public static function insertAsNextSiblingOf(NodeObject $node, NodeObject $sibling, PropelPDO $con = null);

	/**
	 * Inserts $parent as parent of given $node.
	 *
	 * @param      object $parent  	Propel object for given parent node
	 * @param      object $node  	Propel object for given destination node
	 * @param      PropelPDO $con	Connection to use.
	 * @return     void
	 * @throws     Exception      When trying to insert node as parent of a root node
	 */
	public static function insertAsParentOf(NodeObject $parent, NodeObject $node, PropelPDO $con = null);

	/**
	 * Inserts $node as root node
	 *
	 * @param      object $node	Propel object as root node
	 * @param      PropelPDO $con	Connection to use.
	 * @return     void
	 */
	public static function insertRoot(NodeObject $node, PropelPDO $con = null);

	/**
	 * Delete root node
	 *
	 * @param      int $scopeId		Scope id to determine which root node to delete
	 * @param      PropelPDO $con	Connection to use.
	 * @return     boolean		Deletion status
	 */
	public static function deleteRoot($scopeId = 1, PropelPDO $con = null);

	/**
	 * Delete $dest node
	 *
	 * @param      object $dest	Propel object node to delete
	 * @param      PropelPDO $con	Connection to use.
	 * @return     boolean		Deletion status
	 */
	public static function deleteNode(NodeObject $dest, PropelPDO $con = null);

	/**
	 * Moves $child to be first child of $parent
	 *
	 * @param      object $parent	Propel object for parent node
	 * @param      object $child	Propel object for child node
	 * @param      PropelPDO $con	Connection to use.
	 * @return     void
	 */
	public static function moveToFirstChildOf(NodeObject $parent, NodeObject $child, PropelPDO $con = null);

	/**
	 * Moves $node to be last child of $dest
	 *
	 * @param      object $dest	Propel object for destination node
	 * @param      object $node	Propel object for source node
	 * @param      PropelPDO $con	Connection to use.
	 * @return     void
	 */
	public static function moveToLastChildOf(NodeObject $dest, NodeObject $node, PropelPDO $con = null);

	/**
	 * Moves $node to be prev sibling to $dest
	 *
	 * @param      object $dest	Propel object for destination node
	 * @param      object $node	Propel object for source node
	 * @param      PropelPDO $con	Connection to use.
	 * @return     void
	 */
	public static function moveToPrevSiblingOf(NodeObject $dest, NodeObject $node, PropelPDO $con = null);

	/**
	 * Moves $node to be next sibling to $dest
	 *
	 * @param      object $dest	Propel object for destination node
	 * @param      object $node	Propel object for source node
	 * @param      PropelPDO $con	Connection to use.
	 * @return     void
	 */
	public static function moveToNextSiblingOf(NodeObject $dest, NodeObject $node, PropelPDO $con = null);

	/**
	 * Gets first child for the given node if it exists
	 *
	 * @param      object $node	Propel object for src node
	 * @param      PropelPDO $con	Connection to use.
	 * @return     mixed 		Propel object if exists else false
	 */
	public static function retrieveFirstChild(NodeObject $node, PropelPDO $con = null);

	/**
	 * Gets last child for the given node if it exists
	 *
	 * @param      object $node	Propel object for src node
	 * @param      PropelPDO $con	Connection to use.
	 * @return     mixed 		Propel object if exists else false
	 */
	public static function retrieveLastChild(NodeObject $node, PropelPDO $con = null);

	/**
	 * Gets prev sibling for the given node if it exists
	 *
	 * @param      object $node	Propel object for src node
	 * @param      PropelPDO $con	Connection to use.
	 * @return     mixed 		Propel object if exists else false
	 */
	public static function retrievePrevSibling(NodeObject $node, PropelPDO $con = null);

	/**
	 * Gets next sibling for the given node if it exists
	 *
	 * @param      object $node	Propel object for src node
	 * @param      PropelPDO $con	Connection to use.
	 * @return     mixed 		Propel object if exists else false
	 */
	public static function retrieveNextSibling(NodeObject $node, PropelPDO $con = null);

	/**
	 * Retrieves the entire tree from root
	 *
	 * @param      int $scopeId		Scope id to determine which scope tree to return
	 * @param      PropelPDO $con	Connection to use.
	 */
	public static function retrieveTree($scopeId = 1, PropelPDO $con = null);

	/**
	 * Retrieves the entire tree from parent $node
	 *
	 * @param      PropelPDO $con	Connection to use.
	 */
	public static function retrieveBranch(NodeObject $node, PropelPDO $con = null);

	/**
	 * Gets direct children for the node
	 *
	 * @param      object $node	Propel object for parent node
	 * @param      PropelPDO $con	Connection to use.
	 */
	public static function retrieveChildren(NodeObject $node, PropelPDO $con = null);

	/**
	 * Gets all descendants for the node
	 *
	 * @param      object $node	Propel object for parent node
	 * @param      PropelPDO $con	Connection to use.
	 */
	public static function retrieveDescendants(NodeObject $node, PropelPDO $con = null);

	/**
	 * Gets all siblings for the node
	 *
	 * @param      object $node	Propel object for src node
	 * @param      PropelPDO $con	Connection to use.
	 */
	public static function retrieveSiblings(NodeObject $node, PropelPDO $con = null);

	/**
	 * Gets ancestor for the given node if it exists
	 *
	 * @param      object $node	Propel object for src node
	 * @param      PropelPDO $con	Connection to use.
	 * @return     mixed 		Propel object if exists else false
	 */
	public static function retrieveParent(NodeObject $node, PropelPDO $con = null);

	/**
	 * Gets level for the given node
	 *
	 * @param      object $node	Propel object for src node
	 * @param      PropelPDO $con	Connection to use.
	 * @return     int			Level for the given node
	 */
	public static function getLevel(NodeObject $node, PropelPDO $con = null);

	/**
	 * Gets number of direct children for given node
	 *
	 * @param      object $node	Propel object for src node
	 * @param      PropelPDO $con	Connection to use.
	 * @return     int			Level for the given node
	 */
	public static function getNumberOfChildren(NodeObject $node, PropelPDO $con = null);

	/**
	 * Gets number of descendants for given node
	 *
	 * @param      object $node	Propel object for src node
	 * @param      PropelPDO $con	Connection to use.
	 * @return     int			Level for the given node
	 */
	public static function getNumberOfDescendants(NodeObject $node, PropelPDO $con = null);

 	/**
	 * Returns path to a specific node as an array, useful to create breadcrumbs
	 *
	 * @param      object $node	Propel object of node to create path to
	 * @param      PropelPDO $con	Connection to use.
	 * @return     array		Array in order of heirarchy
	 */
	public static function getPath(NodeObject $node, PropelPDO $con = null);

	/**
	 * Tests if node is valid
	 *
	 * @param      object $node	Propel object for src node
	 * @return     bool
	 */
	public static function isValid(NodeObject $node = null);

	/**
	 * Tests if node is a root
	 *
	 * @param      object $node	Propel object for src node
	 * @return     bool
	 */
	public static function isRoot(NodeObject $node);

	/**
	 * Tests if node is a leaf
	 *
	 * @param      object $node	Propel object for src node
	 * @return     bool
	 */
	public static function isLeaf(NodeObject $node);

	/**
	 * Tests if $child is a child of $parent
	 *
	 * @param      object $child	Propel object for node
	 * @param      object $parent	Propel object for node
	 * @return     bool
	 */
	public static function isChildOf(NodeObject $child, NodeObject $parent);

	/**
	 * Tests if $node1 is equal to $node2
	 *
	 * @param      object $node1	Propel object for node
	 * @param      object $node2	Propel object for node
	 * @return     bool
	 */
	public static function isEqualTo(NodeObject $node1, NodeObject $node2);

	/**
	 * Tests if $node has an ancestor
	 *
	 * @param      object $node	Propel object for node
	 * @param      PropelPDO $con		Connection to use.
	 * @return     bool
	 */
	public static function hasParent(NodeObject $node, PropelPDO $con = null);

	/**
	 * Tests if $node has prev sibling
	 *
	 * @param      object $node	Propel object for node
	 * @param      PropelPDO $con	Connection to use.
	 * @return     bool
	 */
	public static function hasPrevSibling(NodeObject $node, PropelPDO $con = null);

	/**
	 * Tests if $node has next sibling
	 *
	 * @param      object $node	Propel object for node
	 * @param      PropelPDO $con	Connection to use.
	 * @return     bool
	 */
	public static function hasNextSibling(NodeObject $node, PropelPDO $con = null);

	/**
	 * Tests if $node has children
	 *
	 * @param      object $node	Propel object for node
	 * @return     bool
	 */
	public static function hasChildren(NodeObject $node);

	/**
	 * Deletes $node and all of its descendants
	 *
	 * @param      object $node	Propel object for source node
	 * @param      PropelPDO $con	Connection to use.
	 */
	public static function deleteDescendants(NodeObject $node, PropelPDO $con = null);

	/**
	 * Returns a node given its primary key or the node itself
	 *
	 * @param      int/object $node	Primary key/instance of required node
	 * @param      PropelPDO $con	Connection to use.
	 * @return     object		Propel object for model
	 */
	public static function getNode($node, PropelPDO $con = null);

} // NodePeer

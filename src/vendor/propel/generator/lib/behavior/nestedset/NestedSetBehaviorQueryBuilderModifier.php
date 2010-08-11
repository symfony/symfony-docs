<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */
 
/**
 * Behavior to adds nested set tree structure columns and abilities
 *
 * @author     François Zaninotto
 * @package    propel.generator.behavior.nestedset
 */
class NestedSetBehaviorQueryBuilderModifier
{
	protected $behavior, $table, $builder, $objectClassname, $peerClassname;
	
	public function __construct($behavior)
	{
		$this->behavior = $behavior;
		$this->table = $behavior->getTable();
	}
	
	protected function getParameter($key)
	{
		return $this->behavior->getParameter($key);
	}
	
	protected function getColumn($name)
	{
		return $this->behavior->getColumnForParameter($name);
	}
	
	protected function setBuilder($builder)
	{
		$this->builder = $builder;
		$this->objectClassname = $builder->getStubObjectBuilder()->getClassname();
		$this->queryClassname = $builder->getStubQueryBuilder()->getClassname();
		$this->peerClassname = $builder->getStubPeerBuilder()->getClassname();
	}
	
	public function queryMethods($builder)
	{
		$this->setBuilder($builder);
		$script = '';
		
		// select filters
		if ($this->behavior->useScope()) {
			$this->addTreeRoots($script);
			$this->addInTree($script);
		}
		$this->addDescendantsOf($script);
		$this->addBranchOf($script);
		$this->addChildrenOf($script);
		$this->addSiblingsOf($script);
		$this->addAncestorsOf($script);
		$this->addRootsOf($script);
		// select orders
		$this->addOrderByBranch($script);
		$this->addOrderByLevel($script);
		// select termination methods
		$this->addFindRoot($script);
		$this->addFindTree($script);
		
		return $script;
	}

	protected function addTreeRoots(&$script)
	{
		$script .= "
/**
 * Filter the query to restrict the result to root objects
 *
 * @return    {$this->queryClassname} The current query, for fuid interface
 */
public function treeRoots()
{
	return \$this->addUsingAlias({$this->peerClassname}::LEFT_COL, 1, Criteria::EQUAL);
}
";
	}

	protected function addInTree(&$script)
	{
		$script .= "
/**
 * Returns the objects in a certain tree, from the tree scope
 *
 * @param     int \$scope		Scope to determine which objects node to return
 *
 * @return    {$this->queryClassname} The current query, for fuid interface
 */
public function inTree(\$scope = null)
{
	return \$this->addUsingAlias({$this->peerClassname}::SCOPE_COL, \$scope, Criteria::EQUAL);
}
";
	}

	protected function addDescendantsOf(&$script)
	{
		$objectName = '$' . $this->table->getStudlyPhpName();
		$script .= "
/**
 * Filter the query to restrict the result to descendants of an object
 *
 * @param     {$this->objectClassname} $objectName The object to use for descendant search
 *
 * @return    {$this->queryClassname} The current query, for fuid interface
 */
public function descendantsOf($objectName)
{
	return \$this";
		if ($this->behavior->useScope()) {
			$script .= "
		->inTree({$objectName}->getScopeValue())";
		}
		$script .= "
		->addUsingAlias({$this->peerClassname}::LEFT_COL, {$objectName}->getLeftValue(), Criteria::GREATER_THAN)
		->addUsingAlias({$this->peerClassname}::LEFT_COL, {$objectName}->getRightValue(), Criteria::LESS_THAN);
}
";
	}

	protected function addBranchOf(&$script)
	{
		$objectName = '$' . $this->table->getStudlyPhpName();
		$script .= "
/**
 * Filter the query to restrict the result to the branch of an object.
 * Same as descendantsOf(), except that it includes the object passed as parameter in the result
 *
 * @param     {$this->objectClassname} $objectName The object to use for branch search
 *
 * @return    {$this->queryClassname} The current query, for fuid interface
 */
public function branchOf($objectName)
{
	return \$this";
		if ($this->behavior->useScope()) {
			$script .= "
		->inTree({$objectName}->getScopeValue())";
		}
		$script .= "
		->addUsingAlias({$this->peerClassname}::LEFT_COL, {$objectName}->getLeftValue(), Criteria::GREATER_EQUAL)
		->addUsingAlias({$this->peerClassname}::LEFT_COL, {$objectName}->getRightValue(), Criteria::LESS_EQUAL);
}
";
	}

	protected function addChildrenOf(&$script)
	{
		$objectName = '$' . $this->table->getStudlyPhpName();
		$script .= "
/**
 * Filter the query to restrict the result to children of an object
 *
 * @param     {$this->objectClassname} $objectName The object to use for child search
 *
 * @return    {$this->queryClassname} The current query, for fuid interface
 */
public function childrenOf($objectName)
{
	return \$this
		->descendantsOf($objectName)
		->addUsingAlias({$this->peerClassname}::LEVEL_COL, {$objectName}->getLevel() + 1, Criteria::EQUAL);
}
";
	}
		
	protected function addSiblingsOf(&$script)
	{
		$objectName = '$' . $this->table->getStudlyPhpName();
		$script .= "
/**
 * Filter the query to restrict the result to siblings of an object.
 * The result does not include the object passed as parameter.
 *
 * @param     {$this->objectClassname} $objectName The object to use for sibling search
 * @param      PropelPDO \$con Connection to use.
 *
 * @return    {$this->queryClassname} The current query, for fuid interface
 */
public function siblingsOf($objectName, PropelPDO \$con = null)
{
	if ({$objectName}->isRoot()) {
		return \$this->
			add({$this->peerClassname}::LEVEL_COL, '1<>1', Criteria::CUSTOM);
	} else {
		return \$this
			->childrenOf({$objectName}->getParent(\$con))
			->prune($objectName);
	}
}
";
	}

	protected function addAncestorsOf(&$script)
	{
		$objectName = '$' . $this->table->getStudlyPhpName();
		$script .= "
/**
 * Filter the query to restrict the result to ancestors of an object
 *
 * @param     {$this->objectClassname} $objectName The object to use for ancestors search
 *
 * @return    {$this->queryClassname} The current query, for fuid interface
 */
public function ancestorsOf($objectName)
{
	return \$this";
		if ($this->behavior->useScope()) {
			$script .= "
		->inTree({$objectName}->getScopeValue())";
		}
		$script .= "
		->addUsingAlias({$this->peerClassname}::LEFT_COL, {$objectName}->getLeftValue(), Criteria::LESS_THAN)
		->addUsingAlias({$this->peerClassname}::RIGHT_COL, {$objectName}->getRightValue(), Criteria::GREATER_THAN);
}
";
	}

	protected function addRootsOf(&$script)
	{
		$objectName = '$' . $this->table->getStudlyPhpName();
		$script .= "
/**
 * Filter the query to restrict the result to roots of an object.
 * Same as ancestorsOf(), except that it includes the object passed as parameter in the result
 *
 * @param     {$this->objectClassname} $objectName The object to use for roots search
 *
 * @return    {$this->queryClassname} The current query, for fuid interface
 */
public function rootsOf($objectName)
{
	return \$this";
		if ($this->behavior->useScope()) {
			$script .= "
		->inTree({$objectName}->getScopeValue())";
		}
		$script .= "
		->addUsingAlias({$this->peerClassname}::LEFT_COL, {$objectName}->getLeftValue(), Criteria::LESS_EQUAL)
		->addUsingAlias({$this->peerClassname}::RIGHT_COL, {$objectName}->getRightValue(), Criteria::GREATER_EQUAL);
}
";
	}

	protected function addOrderByBranch(&$script)
	{
		$script .= "
/**
 * Order the result by branch, i.e. natural tree order
 *
 * @param     bool \$reverse if true, reverses the order
 *
 * @return    {$this->queryClassname} The current query, for fuid interface
 */
public function orderByBranch(\$reverse = false)
{
	if (\$reverse) {
		return \$this
			->addDescendingOrderByColumn({$this->peerClassname}::LEFT_COL);
	} else {
		return \$this
			->addAscendingOrderByColumn({$this->peerClassname}::LEFT_COL);
	}
}
";
	}

	protected function addOrderByLevel(&$script)
	{
		$script .= "
/**
 * Order the result by level, the closer to the root first
 *
 * @param     bool \$reverse if true, reverses the order
 *
 * @return    {$this->queryClassname} The current query, for fuid interface
 */
public function orderByLevel(\$reverse = false)
{
	if (\$reverse) {
		return \$this
			->addAscendingOrderByColumn({$this->peerClassname}::RIGHT_COL);
	} else {
		return \$this
			->addDescendingOrderByColumn({$this->peerClassname}::RIGHT_COL);
	}
}
";
	}
	
	protected function addFindRoot(&$script)
	{
		$useScope = $this->behavior->useScope();
		$script .= "
/**
 * Returns " . ($useScope ? 'a' : 'the') ." root node for the tree
 *";
 		if($useScope) {
 			$script .= "
 * @param      int \$scope		Scope to determine which root node to return";
 		}
		$script .= "
 * @param      PropelPDO \$con	Connection to use.
 *
 * @return     {$this->objectClassname} The tree root object
 */
public function findRoot(" . ($useScope ? "\$scope = null, " : "") . "\$con = null)
{
	return \$this
		->addUsingAlias({$this->peerClassname}::LEFT_COL, 1, Criteria::EQUAL)";
		if ($useScope) {
			$script .= "
		->inTree(\$scope)";
		}
		$script .= "
		->findOne(\$con);
}
";
	}

	protected function addFindTree(&$script)
	{
		$useScope = $this->behavior->useScope();
		$script .= "
/**
 * Returns " . ($useScope ? 'a' : 'the') ." tree of objects
 *";
 		if($useScope) {
 			$script .= "
 * @param      int \$scope		Scope to determine which tree node to return";
 		}
		$script .= "
 * @param      PropelPDO \$con	Connection to use.
 *
 * @return     mixed the list of results, formatted by the current formatter
 */
public function findTree(" . ($useScope ? "\$scope = null, " : "") . "\$con = null)
{
	return \$this";
		if ($useScope) {
			$script .= "
		->inTree(\$scope)";
		}
		$script .= "
		->orderByBranch()
		->find(\$con);
}
";
	}
}
<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */
 
/**
 * Behavior to add sortable query methods
 *
 * @author     François Zaninotto
 * @package    propel.generator.behavior.sortable
 */
class SortableBehaviorQueryBuilderModifier
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
			$this->addInList($script);
		}
		if ($this->getParameter('rank_column') != 'rank') {
			$this->addFilterByRank($script);
			$this->addOrderByRank($script);
		}
		
		// select termination methods
		if ($this->getParameter('rank_column') != 'rank') {
			$this->addFindOneByRank($script);
		}
		$this->addFindList($script);
		
		// utilities
		$this->addGetMaxRank($script);
		$this->addReorder($script);
		
		return $script;
	}

	protected function addInList(&$script)
	{
		$script .= "
/**
 * Returns the objects in a certain list, from the list scope
 *
 * @param     int \$scope		Scope to determine which objects node to return
 *
 * @return    {$this->queryClassname} The current query, for fuid interface
 */
public function inList(\$scope = null)
{
	return \$this->addUsingAlias({$this->peerClassname}::SCOPE_COL, \$scope, Criteria::EQUAL);
}
";
	}
	
	protected function addFilterByRank(&$script)
	{
		$useScope = $this->behavior->useScope();
		$peerClassname = $this->peerClassname;
		$script .= "
/**
 * Filter the query based on a rank in the list
 *
 * @param     integer   \$rank rank";
		if($useScope) {
			$script .= "
 * @param     int \$scope		Scope to determine which suite to consider";
		}
		$script .= "
 *
 * @return    " . $this->queryClassname . " The current query, for fluid interface
 */
public function filterByRank(\$rank" . ($useScope ? ", \$scope = null" : "") . ")
{
	return \$this";
		if ($useScope) {
			$script .= "
		->inList(\$scope)";
		}
		$script .= "
		->addUsingAlias($peerClassname::RANK_COL, \$rank, Criteria::EQUAL);
}
";
	}

	protected function addOrderByRank(&$script)
	{
		$script .= "
/**
 * Order the query based on the rank in the list.
 * Using the default \$order, returns the item with the lowest rank first
 *
 * @param     string \$order either Criteria::ASC (default) or Criteria::DESC
 *
 * @return    " . $this->queryClassname . " The current query, for fluid interface
 */
public function orderByRank(\$order = Criteria::ASC)
{
	\$order = strtoupper(\$order);
	switch (\$order) {
		case Criteria::ASC:
			return \$this->addAscendingOrderByColumn(\$this->getAliasedColName(" . $this->peerClassname . "::RANK_COL));
			break;
		case Criteria::DESC:
			return \$this->addDescendingOrderByColumn(\$this->getAliasedColName(" . $this->peerClassname . "::RANK_COL));
			break;
		default:
			throw new PropelException('" . $this->queryClassname . "::orderBy() only accepts \"asc\" or \"desc\" as argument');
	}
}
";
	}
	
	protected function addFindOneByRank(&$script)
	{
		$useScope = $this->behavior->useScope();
		$peerClassname = $this->peerClassname;
		$script .= "
/**
 * Get an item from the list based on its rank
 *
 * @param     integer   \$rank rank";
		if($useScope) {
			$script .= "
 * @param     int \$scope		Scope to determine which suite to consider";
		}
		$script .= "
 * @param     PropelPDO \$con optional connection
 *
 * @return    {$this->objectClassname}
 */
public function findOneByRank(\$rank, " . ($useScope ? "\$scope = null, " : "") . "PropelPDO \$con = null)
{
	return \$this
		->filterByRank(\$rank" . ($useScope ? ", \$scope" : "") . ")
		->findOne(\$con);
}
";
	}

	protected function addFindList(&$script)
	{
		$useScope = $this->behavior->useScope();
		$script .= "
/**
 * Returns " . ($useScope ? 'a' : 'the') ." list of objects
 *";
 		if($useScope) {
 			$script .= "
 * @param      int \$scope		Scope to determine which list to return";
 		}
		$script .= "
 * @param      PropelPDO \$con	Connection to use.
 *
 * @return     mixed the list of results, formatted by the current formatter
 */
public function findList(" . ($useScope ? "\$scope = null, " : "") . "\$con = null)
{
	return \$this";
		if ($useScope) {
			$script .= "
		->inList(\$scope)";
		}
		$script .= "
		->orderByRank()
		->find(\$con);
}
";
	}
			
	protected function addGetMaxRank(&$script)
	{
		$useScope = $this->behavior->useScope();
		$script .= "
/**
 * Get the highest rank
 * ";
		if($useScope) {
			$script .= "
 * @param      int \$scope		Scope to determine which suite to consider";
		}
		$script .= "
 * @param     PropelPDO optional connection
 *
 * @return    integer highest position
 */
public function getMaxRank(" . ($useScope ? "\$scope = null, " : "") . "PropelPDO \$con = null)
{
	if (\$con === null) {
		\$con = Propel::getConnection({$this->peerClassname}::DATABASE_NAME);
	}
	// shift the objects with a position lower than the one of object
	\$this->addSelectColumn('MAX(' . {$this->peerClassname}::RANK_COL . ')');";
		if ($useScope) {
		$script .= "
	\$this->add({$this->peerClassname}::SCOPE_COL, \$scope, Criteria::EQUAL);";
		}
		$script .= "
	\$stmt = \$this->getSelectStatement(\$con);
	
	return \$stmt->fetchColumn();
}
";
	}

	protected function addReorder(&$script)
	{
		$peerClassname = $this->peerClassname;
		$columnGetter = 'get' . $this->behavior->getColumnForParameter('rank_column')->getPhpName();
		$columnSetter = 'set' . $this->behavior->getColumnForParameter('rank_column')->getPhpName();
		$script .= "
/**
 * Reorder a set of sortable objects based on a list of id/position
 * Beware that there is no check made on the positions passed
 * So incoherent positions will result in an incoherent list
 *
 * @param     array     \$order id => rank pairs
 * @param     PropelPDO \$con   optional connection
 *
 * @return    boolean true if the reordering took place, false if a database problem prevented it
 */
public function reorder(array \$order, PropelPDO \$con = null)
{
	if (\$con === null) {
		\$con = Propel::getConnection($peerClassname::DATABASE_NAME);
	}
	
	\$con->beginTransaction();
	try {
		\$ids = array_keys(\$order);
		\$objects = \$this->findPks(\$ids, \$con);
		foreach (\$objects as \$object) {
			\$pk = \$object->getPrimaryKey();
			if (\$object->$columnGetter() != \$order[\$pk]) {
				\$object->$columnSetter(\$order[\$pk]);
				\$object->save(\$con);
			}
		}
		\$con->commit();

		return true;
	} catch (PropelException \$e) {
		\$con->rollback();
		throw \$e;
	}
}
";
	}

}
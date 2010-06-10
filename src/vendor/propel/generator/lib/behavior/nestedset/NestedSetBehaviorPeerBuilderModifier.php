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
 * @author     heltem <heltem@o2php.com>
 * @package    propel.generator.behavior.nestedset
 */
class NestedSetBehaviorPeerBuilderModifier
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
	
	protected function getColumnAttribute($name)
	{
		return strtolower($this->getColumn($name)->getName());
	}
	
	protected function getColumnConstant($name)
	{
		return strtoupper($this->getColumn($name)->getName());
	}

	protected function getColumnPhpName($name)
	{
		return $this->getColumn($name)->getPhpName();
	}
	
	protected function setBuilder($builder)
	{
		$this->builder = $builder;
		$this->objectClassname = $builder->getStubObjectBuilder()->getClassname();
		$this->peerClassname = $builder->getStubPeerBuilder()->getClassname();
	}
	
	public function staticAttributes($builder)
	{
		$tableName = $this->table->getName();

		$script = "
/**
 * Left column for the set
 */
const LEFT_COL = '" . $tableName . '.' . $this->getColumnConstant('left_column') . "';

/**
 * Right column for the set
 */
const RIGHT_COL = '" . $tableName . '.' . $this->getColumnConstant('right_column') . "';

/**
 * Level column for the set
 */
const LEVEL_COL = '" . $tableName . '.' . $this->getColumnConstant('level_column') . "';
";
	
		if ($this->behavior->useScope()) {
			$script .= 	"
/**
 * Scope column for the set
 */
const SCOPE_COL = '" . $tableName . '.' . $this->getColumnConstant('scope_column') . "';
";
		}
		
		return $script;
	}
	
	public function staticMethods($builder)
	{
		$this->setBuilder($builder);
		$script = '';
		
		if ($this->getParameter('use_scope') == 'true')
		{
			$this->addRetrieveRoots($script);
		}
		$this->addRetrieveRoot($script);
		$this->addRetrieveTree($script);
		$this->addIsValid($script);
		$this->addDeleteTree($script);
		$this->addShiftRLValues($script);
		$this->addShiftLevel($script);
		$this->addUpdateLoadedNodes($script);
		$this->addMakeRoomForLeaf($script);
		$this->addFixLevels($script);
		
		return $script;
	}

	protected function addRetrieveRoots(&$script)
	{
		$peerClassname = $this->peerClassname;
		$script .= "
/**
 * Returns the root nodes for the tree
 *
 * @param      PropelPDO \$con	Connection to use.
 * @return     {$this->objectClassname}			Propel object for root node
 */
public static function retrieveRoots(Criteria \$criteria = null, PropelPDO \$con = null)
{
	if (\$criteria === null) {
		\$criteria = new Criteria($peerClassname::DATABASE_NAME);
	}
	\$criteria->add($peerClassname::LEFT_COL, 1, Criteria::EQUAL);

	return $peerClassname::doSelect(\$criteria, \$con);
}
";
	}

	protected function addRetrieveRoot(&$script)
	{
		$peerClassname = $this->peerClassname;
		$useScope = $this->behavior->useScope();
		$script .= "
/**
 * Returns the root node for a given scope
 *";
 		if($useScope) {
 			$script .= "
 * @param      int \$scope		Scope to determine which root node to return";
 		}
 		$script .= "
 * @param      PropelPDO \$con	Connection to use.
 * @return     {$this->objectClassname}			Propel object for root node
 */
public static function retrieveRoot(" . ($useScope ? "\$scope = null, " : "") . "PropelPDO \$con = null)
{
	\$c = new Criteria($peerClassname::DATABASE_NAME);
	\$c->add($peerClassname::LEFT_COL, 1, Criteria::EQUAL);";
		if($useScope) {
			$script .= "
	\$c->add($peerClassname::SCOPE_COL, \$scope, Criteria::EQUAL);";
		}
		$script .= "

	return $peerClassname::doSelectOne(\$c, \$con);
}
";
	}

	protected function addRetrieveTree(&$script)
	{
		$peerClassname = $this->peerClassname;
		$useScope = $this->behavior->useScope();
		$script .= "
/**
 * Returns the whole tree node for a given scope
 *";
 		if($useScope) {
 			$script .= "
 * @param      int \$scope		Scope to determine which root node to return";
 		}
 		$script .= "
 * @param      Criteria \$criteria	Optional Criteria to filter the query
 * @param      PropelPDO \$con	Connection to use.
 * @return     {$this->objectClassname}			Propel object for root node
 */
public static function retrieveTree(" . ($useScope ? "\$scope = null, " : "") . "Criteria \$criteria = null, PropelPDO \$con = null)
{
	if (\$criteria === null) {
		\$criteria = new Criteria($peerClassname::DATABASE_NAME);
	}
	\$criteria->addAscendingOrderByColumn($peerClassname::LEFT_COL);";
		if($useScope) {
			$script .= "
	\$criteria->add($peerClassname::SCOPE_COL, \$scope, Criteria::EQUAL);";
		}
		$script .= "
	
	return $peerClassname::doSelect(\$criteria, \$con);
}
";
	}
	
	protected function addIsValid(&$script)
	{
		$objectClassname = $this->objectClassname;
		$script .= "
/**
 * Tests if node is valid
 *
 * @param      $objectClassname \$node	Propel object for src node
 * @return     bool
 */
public static function isValid($objectClassname \$node = null)
{
	if (is_object(\$node) && \$node->getRightValue() > \$node->getLeftValue()) {
		return true;
	} else {
		return false;
	}
}
";
	}
	
	protected function addDeleteTree(&$script)
	{
		$peerClassname = $this->peerClassname;
		$useScope = $this->behavior->useScope();
		$script .= "
/**
 * Delete an entire tree
 * ";
 		if($useScope) {
 			$script .= "
 * @param      int \$scope		Scope to determine which tree to delete";
 		}
 		$script .= "
 * @param      PropelPDO \$con	Connection to use.
 *
 * @return     int  The number of deleted nodes
 */
public static function deleteTree(" . ($useScope ? "\$scope = null, " : "") . "PropelPDO \$con = null)
{";
		if($useScope) {
			$script .= "
	\$c = new Criteria($peerClassname::DATABASE_NAME);
	\$c->add($peerClassname::SCOPE_COL, \$scope, Criteria::EQUAL);
	return $peerClassname::doDelete(\$c, \$con);";
		} else {
			$script .= "
	return $peerClassname::doDeleteAll(\$con);";
		}
		$script .= "
}
";
	}

	protected function addShiftRLValues(&$script)
	{
		$peerClassname = $this->peerClassname;
		$useScope = $this->behavior->useScope();
		$script .= "
/**
 * Adds \$delta to all L and R values that are >= \$first and <= \$last.
 * '\$delta' can also be negative.
 *
 * @param      int \$delta		Value to be shifted by, can be negative
 * @param      int \$first		First node to be shifted
 * @param      int \$last			Last node to be shifted (optional)";
		if($useScope) {
			$script .= "
 * @param      int \$scope		Scope to use for the shift";
		}
		$script .= "
 * @param      PropelPDO \$con		Connection to use.
 */
public static function shiftRLValues(\$delta, \$first, \$last = null" . ($useScope ? ", \$scope = null" : ""). ", PropelPDO \$con = null)
{
	if (\$con === null) {
		\$con = Propel::getConnection($peerClassname::DATABASE_NAME, Propel::CONNECTION_WRITE);
	}

	// Shift left column values
	\$whereCriteria = new Criteria($peerClassname::DATABASE_NAME);
	\$criterion = \$whereCriteria->getNewCriterion($peerClassname::LEFT_COL, \$first, Criteria::GREATER_EQUAL);
	if (null !== \$last) {
		\$criterion->addAnd(\$whereCriteria->getNewCriterion($peerClassname::LEFT_COL, \$last, Criteria::LESS_EQUAL));
	}
	\$whereCriteria->add(\$criterion);";
		if ($useScope) {
			$script .= "
	\$whereCriteria->add($peerClassname::SCOPE_COL, \$scope, Criteria::EQUAL);";
		}
		$script .= "
	
	\$valuesCriteria = new Criteria($peerClassname::DATABASE_NAME);
	\$valuesCriteria->add($peerClassname::LEFT_COL, array('raw' => $peerClassname::LEFT_COL . ' + ?', 'value' => \$delta), Criteria::CUSTOM_EQUAL);

	{$this->builder->getBasePeerClassname()}::doUpdate(\$whereCriteria, \$valuesCriteria, \$con);

	// Shift right column values
	\$whereCriteria = new Criteria($peerClassname::DATABASE_NAME);
	\$criterion = \$whereCriteria->getNewCriterion($peerClassname::RIGHT_COL, \$first, Criteria::GREATER_EQUAL);
	if (null !== \$last) {
		\$criterion->addAnd(\$whereCriteria->getNewCriterion($peerClassname::RIGHT_COL, \$last, Criteria::LESS_EQUAL));
	}
	\$whereCriteria->add(\$criterion);";
		if ($useScope) {
			$script .= "
	\$whereCriteria->add($peerClassname::SCOPE_COL, \$scope, Criteria::EQUAL);";
		}
		$script .= "

	\$valuesCriteria = new Criteria($peerClassname::DATABASE_NAME);
	\$valuesCriteria->add($peerClassname::RIGHT_COL, array('raw' => $peerClassname::RIGHT_COL . ' + ?', 'value' => \$delta), Criteria::CUSTOM_EQUAL);

	{$this->builder->getBasePeerClassname()}::doUpdate(\$whereCriteria, \$valuesCriteria, \$con);
}
";
	}

	protected function addShiftLevel(&$script)
	{
		$peerClassname = $this->peerClassname;
		$useScope = $this->behavior->useScope();
		$script .= "
/**
 * Adds \$delta to level for nodes having left value >= \$first and right value <= \$last.
 * '\$delta' can also be negative.
 *
 * @param      int \$delta		Value to be shifted by, can be negative
 * @param      int \$first		First node to be shifted
 * @param      int \$last			Last node to be shifted";
		if($useScope) {
			$script .= "
 * @param      int \$scope		Scope to use for the shift";
		}
		$script .= "
 * @param      PropelPDO \$con		Connection to use.
 */
public static function shiftLevel(\$delta, \$first, \$last" . ($useScope ? ", \$scope = null" : ""). ", PropelPDO \$con = null)
{
	if (\$con === null) {
		\$con = Propel::getConnection($peerClassname::DATABASE_NAME, Propel::CONNECTION_WRITE);
	}

	\$whereCriteria = new Criteria($peerClassname::DATABASE_NAME);
	\$whereCriteria->add($peerClassname::LEFT_COL, \$first, Criteria::GREATER_EQUAL);
	\$whereCriteria->add($peerClassname::RIGHT_COL, \$last, Criteria::LESS_EQUAL);";
		if ($useScope) {
			$script .= "
	\$whereCriteria->add($peerClassname::SCOPE_COL, \$scope, Criteria::EQUAL);";
		}
		$script .= "
	
	\$valuesCriteria = new Criteria($peerClassname::DATABASE_NAME);
	\$valuesCriteria->add($peerClassname::LEVEL_COL, array('raw' => $peerClassname::LEVEL_COL . ' + ?', 'value' => \$delta), Criteria::CUSTOM_EQUAL);

	{$this->builder->getBasePeerClassname()}::doUpdate(\$whereCriteria, \$valuesCriteria, \$con);
}
";
	}
	
	protected function addUpdateLoadedNodes(&$script)
	{
		$peerClassname = $this->peerClassname;
		$script .= "
/**
 * Reload all already loaded nodes to sync them with updated db
 *
 * @param      PropelPDO \$con		Connection to use.
 */
public static function updateLoadedNodes(PropelPDO \$con = null)
{
	if (Propel::isInstancePoolingEnabled()) {
		\$keys = array();
		foreach ($peerClassname::\$instances as \$obj) {
			\$keys[] = \$obj->getPrimaryKey();
		}

		if (!empty(\$keys)) {
			// We don't need to alter the object instance pool; we're just modifying these ones
			// already in the pool.
			\$criteria = new Criteria($peerClassname::DATABASE_NAME);";
		if (count($this->table->getPrimaryKey()) === 1) {
			$pkey = $this->table->getPrimaryKey();
			$col = array_shift($pkey);
			$script .= "
			\$criteria->add(".$this->builder->getColumnConstant($col).", \$keys, Criteria::IN);
";
		} else {
			$fields = array();
			foreach ($this->table->getPrimaryKey() as $k => $col) {
				$fields[] = $this->builder->getColumnConstant($col);
			};
			$script .= "

			// Loop on each instances in pool
			foreach (\$keys as \$values) {
			  // Create initial Criterion
				\$cton = \$criteria->getNewCriterion(" . $fields[0] . ", \$values[0]);";
			unset($fields[0]);
			foreach ($fields as $k => $col) {
				$script .= "

				// Create next criterion
				\$nextcton = \$criteria->getNewCriterion(" . $col . ", \$values[$k]);
				// And merge it with the first
				\$cton->addAnd(\$nextcton);";
			}
			$script .= "

				// Add final Criterion to Criteria
				\$criteria->addOr(\$cton);
			}";
		}

		$script .= "
			\$stmt = $peerClassname::doSelectStmt(\$criteria, \$con);
			while (\$row = \$stmt->fetch(PDO::FETCH_NUM)) {
				\$key = $peerClassname::getPrimaryKeyHashFromRow(\$row, 0);
				if (null !== (\$object = $peerClassname::getInstanceFromPool(\$key))) {";
		$n = 0;
		foreach ($this->table->getColumns() as $col) {
			if ($col->getPhpName() == $this->getColumnPhpName('left_column')) {
				$script .= "
					\$object->setLeftValue(\$row[$n]);";
			} else if ($col->getPhpName() == $this->getColumnPhpName('right_column')) {
				$script .= "
					\$object->setRightValue(\$row[$n]);";
			} else if ($col->getPhpName() == $this->getColumnPhpName('level_column')) {
				$script .= "
					\$object->setLevel(\$row[$n]);
					\$object->clearNestedSetChildren();";
			}
			$n++;
		}
		$script .= "
				}
			}
			\$stmt->closeCursor();
		}
	}
}
";
	}

	protected function addMakeRoomForLeaf(&$script)
	{
		$peerClassname = $this->peerClassname;
		$useScope = $this->behavior->useScope();
		$script .= "
/**
 * Update the tree to allow insertion of a leaf at the specified position
 *
 * @param      int \$left	left column value";
 		if ($useScope) {
 			 		$script .= "
 * @param      integer \$scope	scope column value";
 		}
 		$script .= "
 * @param      PropelPDO \$con	Connection to use.
 */
public static function makeRoomForLeaf(\$left" . ($useScope ? ", \$scope" : ""). ", PropelPDO \$con = null)
{	
	// Update database nodes
	$peerClassname::shiftRLValues(2, \$left, null" . ($useScope ? ", \$scope" : "") . ", \$con);

	// Update all loaded nodes
	$peerClassname::updateLoadedNodes(\$con);
}
";
	}
	
	protected function addFixLevels(&$script)
	{
		$peerClassname = $this->peerClassname;
		$useScope = $this->behavior->useScope();
		$script .= "
/**
 * Update the tree to allow insertion of a leaf at the specified position
 *";
 		if ($useScope) {
 			 		$script .= "
 * @param      integer \$scope	scope column value";
 		}
 		$script .= "
 * @param      PropelPDO \$con	Connection to use.
 */
public static function fixLevels(" . ($useScope ? "\$scope, " : ""). "PropelPDO \$con = null)
{
	\$c = new Criteria();";
		if ($useScope) {
			$script .= "
	\$c->add($peerClassname::SCOPE_COL, \$scope, Criteria::EQUAL);";
		}
		$script .= "
	\$c->addAscendingOrderByColumn($peerClassname::LEFT_COL);
	\$stmt = $peerClassname::doSelectStmt(\$c, \$con);
	";
		if (!$this->table->getChildrenColumn()) {
			$script .= "
	// set the class once to avoid overhead in the loop
	\$cls = $peerClassname::getOMClass(false);";
		}

		$script .= "
	\$level = null;
	// iterate over the statement
	while (\$row = \$stmt->fetch(PDO::FETCH_NUM)) {

		// hydrate object
		\$key = $peerClassname::getPrimaryKeyHashFromRow(\$row, 0);
		if (null === (\$obj = $peerClassname::getInstanceFromPool(\$key))) {";
		if ($this->table->getChildrenColumn()) {
			$script .= "
			// class must be set each time from the record row
			\$cls = $peerClassname::getOMClass(\$row, 0);
			\$cls = substr('.'.\$cls, strrpos('.'.\$cls, '.') + 1);
			" . $this->builder->buildObjectInstanceCreationCode('$obj', '$cls') . "
			\$obj->hydrate(\$row);
			$peerClassname::addInstanceToPool(\$obj, \$key);";
		} else {
			$script .= "
			" . $this->builder->buildObjectInstanceCreationCode('$obj', '$cls') . "
			\$obj->hydrate(\$row);
			$peerClassname::addInstanceToPool(\$obj, \$key);";
		}
		$script .= "
		}
		
		// compute level
		// Algorithm shamelessly stolen from sfPropelActAsNestedSetBehaviorPlugin
		// Probably authored by Tristan Rivoallan
		if (\$level === null) {
			\$level = 0;
			\$i = 0;
			\$prev = array(\$obj->getRightValue());
		} else {
			while (\$obj->getRightValue() > \$prev[\$i]) {
				\$i--;
			}
			\$level = ++\$i;
			\$prev[\$i] = \$obj->getRightValue();
		}
		
		// update level in node if necessary
		if (\$obj->getLevel() !== \$level) {
			\$obj->setLevel(\$level);
			\$obj->save(\$con);
		}
	}
	\$stmt->closeCursor();
}
";
	}
}
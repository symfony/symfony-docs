<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */
 
/**
 * Behavior to add sortable columns and abilities
 *
 * @author     François Zaninotto
 * @author     heltem <heltem@o2php.com>
 * @package    propel.generator.behavior.sortable
 */
class SortableBehaviorObjectBuilderModifier
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
	
	protected function getColumnAttribute($name)
	{
		return strtolower($this->behavior->getColumnForParameter($name)->getName());
	}

	protected function getColumnPhpName($name)
	{
		return $this->behavior->getColumnForParameter($name)->getPhpName();
	}
	
	protected function setBuilder($builder)
	{
		$this->builder = $builder;
		$this->objectClassname = $builder->getStubObjectBuilder()->getClassname();
		$this->queryClassname = $builder->getStubQueryBuilder()->getClassname();
		$this->peerClassname = $builder->getStubPeerBuilder()->getClassname();
	}
	
	/**
	 * Get the getter of the column of the behavior
	 *
	 * @return string The related getter, e.g. 'getRank'
	 */
	protected function getColumnGetter($columnName = 'rank_column')
	{
		return 'get' . $this->behavior->getColumnForParameter($columnName)->getPhpName();
	}

	/**
	 * Get the setter of the column of the behavior
	 *
	 * @return string The related setter, e.g. 'setRank'
	 */
	protected function getColumnSetter($columnName = 'rank_column')
	{
		return 'set' . $this->behavior->getColumnForParameter($columnName)->getPhpName();
	}

	public function preSave($builder)
	{
		return "\$this->processSortableQueries(\$con);";
	}

	public function preInsert($builder)
	{
		$useScope = $this->behavior->useScope();
		$this->setBuilder($builder);
		return "if (!\$this->isColumnModified({$this->peerClassname}::RANK_COL)) {
	\$this->{$this->getColumnSetter()}({$this->queryClassname}::create()->getMaxRank(" . ($useScope ? "\$this->{$this->getColumnGetter('scope_column')}(), " : '') . "\$con) + 1);
}
";
	}
	
	public function preDelete($builder)
	{
		$useScope = $this->behavior->useScope();
		$this->setBuilder($builder);
		return "
{$this->peerClassname}::shiftRank(-1, \$this->{$this->getColumnGetter()}() + 1, null, " . ($useScope ? "\$this->{$this->getColumnGetter('scope_column')}(), " : '') . "\$con);
{$this->peerClassname}::clearInstancePool();
";
	}

	public function objectAttributes($builder)
	{
		return "
/**
 * Queries to be executed in the save transaction
 * @var        array
 */
protected \$sortableQueries = array();
";
	}
	
	public function objectMethods($builder)
	{
		$this->setBuilder($builder);
		$script = '';
		if ($this->getParameter('rank_column') != 'rank') {
			$this->addRankAccessors($script);
		}
		if ($this->behavior->useScope() && 
				$this->getParameter('scope_column') != 'scope_value') {
			$this->addScopeAccessors($script);
		}
		$this->addIsFirst($script);
		$this->addIsLast($script);
		$this->addGetNext($script);
		$this->addGetPrevious($script);
		$this->addInsertAtRank($script);
		$this->addInsertAtBottom($script);
		$this->addInsertAtTop($script);
		$this->addMoveToRank($script);
		$this->addSwapWith($script);
		$this->addMoveUp($script);
		$this->addMoveDown($script);
		$this->addMoveToTop($script);
		$this->addMoveToBottom($script);
		$this->addRemoveFromList($script);
		$this->addProcessSortableQueries($script);
		
		return $script;
	}

	/**
	 * Get the wraps for getter/setter, if the rank column has not the default name
	 *
	 * @return string
	 */
	protected function addRankAccessors(&$script)
	{
    $script .= "
/**
 * Wrap the getter for rank value
 *
 * @return    int
 */
public function getRank()
{
	return \$this->{$this->getColumnAttribute('rank_column')};
}

/**
 * Wrap the setter for rank value
 *
 * @param     int
 * @return    {$this->objectClassname}
 */
public function setRank(\$v)
{
	return \$this->{$this->getColumnSetter()}(\$v);
}
";
	}
	
	/**
	 * Get the wraps for getter/setter, if the scope column has not the default name
	 *
	 * @return string
	 */
	protected function addScopeAccessors(&$script)
	{
    $script .= "
/**
 * Wrap the getter for scope value
 *
 * @return    int
 */
public function getScopeValue()
{
	return \$this->{$this->getColumnAttribute('scope_column')};
}

/**
 * Wrap the setter for scope value
 *
 * @param     int
 * @return    {$this->objectClassname}
 */
public function setScopeValue(\$v)
{
	return \$this->{$this->getColumnSetter('scope_column')}(\$v);
}
";
	}

	protected function addIsFirst(&$script)
	{
		$script .= "
/**
 * Check if the object is first in the list, i.e. if it has 1 for rank
 *
 * @return    boolean
 */
public function isFirst()
{
	return \$this->{$this->getColumnGetter()}() == 1;
}
";
	}

	protected function addIsLast(&$script)
	{
		$useScope = $this->behavior->useScope();
		$script .= "
/**
 * Check if the object is last in the list, i.e. if its rank is the highest rank
 *
 * @param     PropelPDO  \$con      optional connection
 *
 * @return    boolean
 */
public function isLast(PropelPDO \$con = null)
{
	return \$this->{$this->getColumnGetter()}() == {$this->queryClassname}::create()->getMaxRank(" . ($useScope ? "\$this->{$this->getColumnGetter('scope_column')}(), " : '') . "\$con);
}
";
	}

	protected function addGetNext(&$script)
	{
		$useScope = $this->behavior->useScope();
		$script .= "
/**
 * Get the next item in the list, i.e. the one for which rank is immediately higher
 *
 * @param     PropelPDO  \$con      optional connection
 *
 * @return    {$this->objectClassname}
 */
public function getNext(PropelPDO \$con = null)
{";
		if ($this->behavior->getParameter('rank_column') == 'rank' && $useScope) {
			$script .= "
	return {$this->queryClassname}::create()
		->filterByRank(\$this->{$this->getColumnGetter()}() + 1)
		->inList(\$this->{$this->getColumnGetter('scope_column')}())
		->findOne(\$con);";
		} else {
			$script .= "
	return {$this->queryClassname}::create()->findOneByRank(\$this->{$this->getColumnGetter()}() + 1, " . ($useScope ? "\$this->{$this->getColumnGetter('scope_column')}(), " : '') . "\$con);";
		}

		$script .= "
}
";
	}

	protected function addGetPrevious(&$script)
	{
		$useScope = $this->behavior->useScope();
		$script .= "
/**
 * Get the previous item in the list, i.e. the one for which rank is immediately lower
 *
 * @param     PropelPDO  \$con      optional connection
 *
 * @return    {$this->objectClassname}
 */
public function getPrevious(PropelPDO \$con = null)
{";
		if ($this->behavior->getParameter('rank_column') == 'rank' && $useScope) {
			$script .= "
	return {$this->queryClassname}::create()
		->filterByRank(\$this->{$this->getColumnGetter()}() - 1)
		->inList(\$this->{$this->getColumnGetter('scope_column')}())
		->findOne(\$con);";
		} else {
			$script .= "
	return {$this->queryClassname}::create()->findOneByRank(\$this->{$this->getColumnGetter()}() - 1, " . ($useScope ? "\$this->{$this->getColumnGetter('scope_column')}(), " : '') . "\$con);";
		}
		$script .= "
}
";
	}

	protected function addInsertAtRank(&$script)
	{
		$useScope = $this->behavior->useScope();
		$peerClassname = $this->peerClassname;
		$script .= "
/**
 * Insert at specified rank
 * The modifications are not persisted until the object is saved.
 *
 * @param     integer    \$rank rank value
 * @param     PropelPDO  \$con      optional connection
 *
 * @return    {$this->objectClassname} the current object
 *
 * @throws    PropelException
 */
public function insertAtRank(\$rank, PropelPDO \$con = null)
{";
		if ($useScope) {
			$script .= "
	if (null === \$this->{$this->getColumnGetter('scope_column')}()) {
		throw new PropelException('The scope must be defined before inserting an object in a suite');
	}";
		}
		$script .= "
	\$maxRank = {$this->queryClassname}::create()->getMaxRank(" . ($useScope ? "\$this->{$this->getColumnGetter('scope_column')}(), " : '') . "\$con);
	if (\$rank < 1 || \$rank > \$maxRank + 1) {
		throw new PropelException('Invalid rank ' . \$rank);
	}
	// move the object in the list, at the given rank
	\$this->{$this->getColumnSetter()}(\$rank);
	if (\$rank != \$maxRank + 1) {
		// Keep the list modification query for the save() transaction
		\$this->sortableQueries []= array(
			'callable'  => array('$peerClassname', 'shiftRank'),
			'arguments' => array(1, \$rank, null, " . ($useScope ? "\$this->{$this->getColumnGetter('scope_column')}()" : '') . ")
		);
	}
	
	return \$this;
}
";
	}

	protected function addInsertAtBottom(&$script)
	{
		$useScope = $this->behavior->useScope();
		$script .= "
/**
 * Insert in the last rank
 * The modifications are not persisted until the object is saved.
 *
 * @param PropelPDO \$con optional connection
 *
 * @return    {$this->objectClassname} the current object
 *
 * @throws    PropelException
 */
public function insertAtBottom(PropelPDO \$con = null)
{";
		if ($useScope) {
			$script .= "
	if (null === \$this->{$this->getColumnGetter('scope_column')}()) {
		throw new PropelException('The scope must be defined before inserting an object in a suite');
	}";
		}
		$script .= "
	\$this->{$this->getColumnSetter()}({$this->queryClassname}::create()->getMaxRank(" . ($useScope ? "\$this->{$this->getColumnGetter('scope_column')}(), " : '') . "\$con) + 1);
	
	return \$this;
}
";
	}

	protected function addInsertAtTop(&$script)
	{
		$script .= "
/**
 * Insert in the first rank
 * The modifications are not persisted until the object is saved.
 *
 * @return    {$this->objectClassname} the current object
 */
public function insertAtTop()
{
	return \$this->insertAtRank(1);
}
";
	}

	protected function addMoveToRank(&$script)
	{
		$useScope = $this->behavior->useScope();
		$peerClassname = $this->peerClassname;
		$script .= "
/**
 * Move the object to a new rank, and shifts the rank
 * Of the objects inbetween the old and new rank accordingly
 *
 * @param     integer   \$newRank rank value
 * @param     PropelPDO \$con optional connection
 *
 * @return    {$this->objectClassname} the current object
 *
 * @throws    PropelException
 */
public function moveToRank(\$newRank, PropelPDO \$con = null)
{
	if (\$this->isNew()) {
		throw new PropelException('New objects cannot be moved. Please use insertAtRank() instead');
	}
	if (\$con === null) {
		\$con = Propel::getConnection($peerClassname::DATABASE_NAME);
	}
	if (\$newRank < 1 || \$newRank > {$this->queryClassname}::create()->getMaxRank(" . ($useScope ? "\$this->{$this->getColumnGetter('scope_column')}(), " : '') . "\$con)) {
		throw new PropelException('Invalid rank ' . \$newRank);
	}

	\$oldRank = \$this->{$this->getColumnGetter()}();
	if (\$oldRank == \$newRank) {
		return \$this;
	}
	
	\$con->beginTransaction();
	try {
		// shift the objects between the old and the new rank
		\$delta = (\$oldRank < \$newRank) ? -1 : 1;
		$peerClassname::shiftRank(\$delta, min(\$oldRank, \$newRank), max(\$oldRank, \$newRank), " . ($useScope ? "\$this->{$this->getColumnGetter('scope_column')}(), " : '') . "\$con);
			
		// move the object to its new rank
		\$this->{$this->getColumnSetter()}(\$newRank);
		\$this->save(\$con);
		
		\$con->commit();
		return \$this;
	} catch (Exception \$e) {
		\$con->rollback();
		throw \$e;
	}
}
";
	}

	protected function addSwapWith(&$script)
	{
		$script .= "
/**
 * Exchange the rank of the object with the one passed as argument, and saves both objects
 *
 * @param     {$this->objectClassname} \$object
 * @param     PropelPDO \$con optional connection
 *
 * @return    {$this->objectClassname} the current object
 *
 * @throws Exception if the database cannot execute the two updates
 */
public function swapWith(\$object, PropelPDO \$con = null)
{
	if (\$con === null) {
		\$con = Propel::getConnection({$this->peerClassname}::DATABASE_NAME);
	}
	\$con->beginTransaction();
	try {
		\$oldRank = \$this->{$this->getColumnGetter()}();
		\$newRank = \$object->{$this->getColumnGetter()}();
		\$this->{$this->getColumnSetter()}(\$newRank);
		\$this->save(\$con);
		\$object->{$this->getColumnSetter()}(\$oldRank);
		\$object->save(\$con);
		\$con->commit();
		
		return \$this;
	} catch (Exception \$e) {
		\$con->rollback();
		throw \$e;
	}
}
";
	}

	protected function addMoveUp(&$script)
	{
		$script .= "
/**
 * Move the object higher in the list, i.e. exchanges its rank with the one of the previous object
 *
 * @param     PropelPDO \$con optional connection
 *
 * @return    {$this->objectClassname} the current object
 */
public function moveUp(PropelPDO \$con = null)
{
	if (\$this->isFirst()) {
		return \$this;
	}
	if (\$con === null) {
		\$con = Propel::getConnection({$this->peerClassname}::DATABASE_NAME);
	}
	\$con->beginTransaction();
	try {
		\$prev = \$this->getPrevious(\$con);
		\$this->swapWith(\$prev, \$con);
		\$con->commit();
		
		return \$this;
	} catch (Exception \$e) {
		\$con->rollback();
		throw \$e;
	}
}
";
	}

	protected function addMoveDown(&$script)
	{
		$script .= "
/**
 * Move the object higher in the list, i.e. exchanges its rank with the one of the next object
 *
 * @param     PropelPDO \$con optional connection
 *
 * @return    {$this->objectClassname} the current object
 */
public function moveDown(PropelPDO \$con = null)
{
	if (\$this->isLast(\$con)) {
		return \$this;
	}
	if (\$con === null) {
		\$con = Propel::getConnection({$this->peerClassname}::DATABASE_NAME);
	}
	\$con->beginTransaction();
	try {
		\$next = \$this->getNext(\$con);
		\$this->swapWith(\$next, \$con);
		\$con->commit();
		
		return \$this;
	} catch (Exception \$e) {
		\$con->rollback();
		throw \$e;
	}
}
";
	}

	protected function addMoveToTop(&$script)
	{
		$script .= "
/**
 * Move the object to the top of the list
 *
 * @param     PropelPDO \$con optional connection
 *
 * @return    {$this->objectClassname} the current object
 */
public function moveToTop(PropelPDO \$con = null)
{
	if (\$this->isFirst()) {
		return \$this;
	}
	return \$this->moveToRank(1, \$con);
}
";
	}

	protected function addMoveToBottom(&$script)
	{
		$useScope = $this->behavior->useScope();
		$script .= "
/**
 * Move the object to the bottom of the list
 *
 * @param     PropelPDO \$con optional connection
 *
 * @return integer the old object's rank
 */
public function moveToBottom(PropelPDO \$con = null)
{
	if (\$this->isLast(\$con)) {
		return false;
	}
	if (\$con === null) {
		\$con = Propel::getConnection({$this->peerClassname}::DATABASE_NAME);
	}
	\$con->beginTransaction();
	try {
		\$bottom = {$this->queryClassname}::create()->getMaxRank(" . ($useScope ? "\$this->{$this->getColumnGetter('scope_column')}(), " : '') . "\$con);
		\$res = \$this->moveToRank(\$bottom, \$con);
		\$con->commit();
		
		return \$res;
	} catch (Exception \$e) {
		\$con->rollback();
		throw \$e;
	}
}
";
	}

	protected function addRemoveFromList(&$script)
	{
		$useScope = $this->behavior->useScope();
		$peerClassname = $this->peerClassname;
		$script .= "
/**
 * Removes the current object from the list.
 * The modifications are not persisted until the object is saved.
 *
 * @return    {$this->objectClassname} the current object
 */
public function removeFromList()
{
	// Keep the list modification query for the save() transaction
	\$this->sortableQueries []= array(
		'callable'  => array('$peerClassname', 'shiftRank'),
		'arguments' => array(-1, \$this->{$this->getColumnGetter()}() + 1, null" . ($useScope ? ", \$this->{$this->getColumnGetter('scope_column')}()" : '') . ")
	);
	// remove the object from the list
	\$this->{$this->getColumnSetter('rank_column')}(null);";
		if ($useScope) {
		$script .= "
	\$this->{$this->getColumnSetter('scope_column')}(null);";
		}
		$script .= "
	
	return \$this;
}
";
	}
	
	protected function addProcessSortableQueries(&$script)
	{
		$script .= "
/**
 * Execute queries that were saved to be run inside the save transaction
 */
protected function processSortableQueries(\$con)
{
	foreach (\$this->sortableQueries as \$query) {
		\$query['arguments'][]= \$con;
		call_user_func_array(\$query['callable'], \$query['arguments']);
	}
	\$this->sortableQueries = array();
}
";
	}
}
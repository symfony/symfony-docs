<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

require_once 'builder/om/PHP5ObjectBuilder.php';

/**
 * Generates a PHP5 base Object class for user object model (OM).
 *
 * This class produces the base object class (e.g. BaseMyTable) which contains all
 * the custom-built accessor and setter methods.
 *
 * This class overrides PHP5BaseObject to use Peer methods and Criteria
 * instead of Query objects for fetching foreign keys. This can be useful if
 * some legacy Propel 1.4 code assumes that the getters returns arrays 
 * instead of collections. 
 *
 * This class is not used by default. You must override 
 * the propel.builder.object.class setting in build.properties to use it:
 * <code>
 * propel.builder.object.class = builder.om.PHP5ObjectNoCollectionBuilder
 * </code>
 *
 * @deprecated Since Propel 1.5
 * @author     Hans Lellelid <hans@xmpl.org>
 * @package    propel.generator.builder.om
 */
class PHP5ObjectNoCollectionBuilder extends PHP5ObjectBuilder
{

	/**
	 * Adds the lazy loader method.
	 * @param      string &$script The script will be modified in this method.
	 * @param      Column $col The current column.
	 * @see        parent::addColumnAccessors()
	 */
	protected function addLazyLoader(&$script, Column $col)
	{
		$this->addLazyLoaderComment($script, $col);
		$this->addLazyLoaderOpen($script, $col);
		$this->addLazyLoaderBody($script, $col);
		$this->addLazyLoaderClose($script, $col);
	}

	/**
	 * Adds the comment for the lazy loader method
	 * @param      string &$script The script will be modified in this method.
	 * @param      Column $col The current column.
	 * @see        addLazyLoader()
	 **/
	protected function addLazyLoaderComment(&$script, Column $col) {
		$clo = strtolower($col->getName());

		$script .= "
	/**
	 * Load the value for the lazy-loaded [$clo] column.
	 *
	 * This method performs an additional query to return the value for
	 * the [$clo] column, since it is not populated by
	 * the hydrate() method.
	 *
	 * @param      \$con PropelPDO (optional) The PropelPDO connection to use.
	 * @return     void
	 * @throws     PropelException - any underlying error will be wrapped and re-thrown.
	 */";
	}

	/**
	 * Adds the function declaration for the lazy loader method
	 * @param      string &$script The script will be modified in this method.
	 * @param      Column $col The current column.
	 * @see        addLazyLoader()
	 **/
	protected function addLazyLoaderOpen(&$script, Column $col) {
		$cfc = $col->getPhpName();
		$script .= "
	protected function load$cfc(PropelPDO \$con = null)
	{";
	}

	/**
	 * Adds the function body for the lazy loader method
	 * @param      string &$script The script will be modified in this method.
	 * @param      Column $col The current column.
	 * @see        addLazyLoader()
	 **/
	protected function addLazyLoaderBody(&$script, Column $col) {
		$platform = $this->getPlatform();
		$clo = strtolower($col->getName());

		$script .= "
		\$c = \$this->buildPkeyCriteria();
		\$c->addSelectColumn(".$this->getColumnConstant($col).");
		try {
			\$stmt = ".$this->getPeerClassname()."::doSelectStmt(\$c, \$con);
			\$row = \$stmt->fetch(PDO::FETCH_NUM);
			\$stmt->closeCursor();";

		if ($col->getType() === PropelTypes::CLOB && $this->getPlatform() instanceof OraclePlatform) {
			// PDO_OCI returns a stream for CLOB objects, while other PDO adapters return a string...
			$script .= "
			\$this->$clo = stream_get_contents(\$row[0]);";
		}	elseif ($col->isLobType() && !$platform->hasStreamBlobImpl()) {
			$script .= "
			if (\$row[0] !== null) {
				\$this->$clo = fopen('php://memory', 'r+');
				fwrite(\$this->$clo, \$row[0]);
				rewind(\$this->$clo);
			} else {
				\$this->$clo = null;
			}";
		} elseif ($col->isPhpPrimitiveType()) {
			$script .= "
			\$this->$clo = (\$row[0] !== null) ? (".$col->getPhpType().") \$row[0] : null;";
		} elseif ($col->isPhpObjectType()) {
			$script .= "
			\$this->$clo = (\$row[0] !== null) ? new ".$col->getPhpType()."(\$row[0]) : null;";
		} else {
			$script .= "
			\$this->$clo = \$row[0];";
		}

		$script .= "
			\$this->".$clo."_isLoaded = true;
		} catch (Exception \$e) {
			throw new PropelException(\"Error loading value for [$clo] column on demand.\", \$e);
		}";
	}

	/**
	 * Adds the function close for the lazy loader
	 * @param      string &$script The script will be modified in this method.
	 * @param      Column $col The current column.
	 * @see        addLazyLoader()
	 **/
	protected function addLazyLoaderClose(&$script, Column $col) {
		$script .= "
	}";
	} // addLazyLoader()

	/**
	 * Adds the buildPkeyCriteria method
	 * @param      string &$script The script will be modified in this method.
	 **/
	protected function addBuildPkeyCriteria(&$script) {
		$this->addBuildPkeyCriteriaComment($script);
		$this->addBuildPkeyCriteriaOpen($script);
		$this->addBuildPkeyCriteriaBody($script);
		$this->addBuildPkeyCriteriaClose($script);
	}

	/**
	 * Adds the comment for the buildPkeyCriteria method
	 * @param      string &$script The script will be modified in this method.
	 * @see        addBuildPkeyCriteria()
	 **/
	protected function addBuildPkeyCriteriaComment(&$script) {
		$script .= "
	/**
	 * Builds a Criteria object containing the primary key for this object.
	 *
	 * Unlike buildCriteria() this method includes the primary key values regardless
	 * of whether or not they have been modified.
	 *
	 * @return     Criteria The Criteria object containing value(s) for primary key(s).
	 */";
	}

	/**
	 * Adds the function declaration for the buildPkeyCriteria method
	 * @param      string &$script The script will be modified in this method.
	 * @see        addBuildPkeyCriteria()
	 **/
	protected function addBuildPkeyCriteriaOpen(&$script) {
		$script .= "
	public function buildPkeyCriteria()
	{";
	}

	/**
	 * Adds the function body for the buildPkeyCriteria method
	 * @param      string &$script The script will be modified in this method.
	 * @see        addBuildPkeyCriteria()
	 **/
	protected function addBuildPkeyCriteriaBody(&$script) {
		$script .= "
		\$criteria = new Criteria(".$this->getPeerClassname()."::DATABASE_NAME);";
		foreach ($this->getTable()->getColumns() as $col) {
			$clo = strtolower($col->getName());
			if ($col->isPrimaryKey()) {
				$script .= "
		\$criteria->add(".$this->getColumnConstant($col).", \$this->$clo);";
			}
		}
	}

	/**
	 * Adds the function close for the buildPkeyCriteria method
	 * @param      string &$script The script will be modified in this method.
	 * @see        addBuildPkeyCriteria()
	 **/
	protected function addBuildPkeyCriteriaClose(&$script) {
		$script .= "

		return \$criteria;
	}
";
	}

	/**
	 * Adds the buildCriteria method
	 * @param      string &$script The script will be modified in this method.
	 **/
	protected function addBuildCriteria(&$script)
	{
		$this->addBuildCriteriaComment($script);
		$this->addBuildCriteriaOpen($script);
		$this->addBuildCriteriaBody($script);
		$this->addBuildCriteriaClose($script);
	}

	/**
	 * Adds comment for the buildCriteria method
	 * @param      string &$script The script will be modified in this method.
	 * @see        addBuildCriteria()
	 **/
	protected function addBuildCriteriaComment(&$script) {
		$script .= "
	/**
	 * Build a Criteria object containing the values of all modified columns in this object.
	 *
	 * @return     Criteria The Criteria object containing all modified values.
	 */";
	}

	/**
	 * Adds the function declaration of the buildCriteria method
	 * @param      string &$script The script will be modified in this method.
	 * @see        addBuildCriteria()
	 **/
	protected function addBuildCriteriaOpen(&$script) {
		$script .= "
	public function buildCriteria()
	{";
	}

	/**
	 * Adds the function body of the buildCriteria method
	 * @param      string &$script The script will be modified in this method.
	 * @see        addBuildCriteria()
	 **/
	protected function addBuildCriteriaBody(&$script) {
		$script .= "
		\$criteria = new Criteria(".$this->getPeerClassname()."::DATABASE_NAME);
";
		foreach ($this->getTable()->getColumns() as $col) {
			$clo = strtolower($col->getName());
			$script .= "
		if (\$this->isColumnModified(".$this->getColumnConstant($col).")) \$criteria->add(".$this->getColumnConstant($col).", \$this->$clo);";
		}
	}

	/**
	 * Adds the function close of the buildCriteria method
	 * @param      string &$script The script will be modified in this method.
	 * @see        addBuildCriteria()
	 **/
	protected function addBuildCriteriaClose(&$script) {
		$script .= "

		return \$criteria;
	}
";
	}

	/**
	 * Adds the function body for the delete function
	 * @param      string &$script The script will be modified in this method.
	 * @see        addDelete()
	 **/
	protected function addDeleteBody(&$script) {
		$script .= "
		if (\$this->isDeleted()) {
			throw new PropelException(\"This object has already been deleted.\");
		}

		if (\$con === null) {
			\$con = Propel::getConnection(".$this->getPeerClassname()."::DATABASE_NAME, Propel::CONNECTION_WRITE);
		}
		
		\$con->beginTransaction();
		try {";
		if($this->getGeneratorConfig()->getBuildProperty('addHooks')) {
			$script .= "
			\$ret = \$this->preDelete(\$con);";
			// apply behaviors
			$this->applyBehaviorModifier('preDelete', $script, "			");
			$script .= "
			if (\$ret) {
				".$this->getPeerClassname()."::doDelete(\$this, \$con);
				\$this->postDelete(\$con);";
			// apply behaviors
			$this->applyBehaviorModifier('postDelete', $script, "				");
			$script .= "
				\$con->commit();
				\$this->setDeleted(true);
			} else {
				\$con->commit();
			}";
		} else {
			// apply behaviors
			$this->applyBehaviorModifier('preDelete', $script, "			");
			$script .= "
			".$this->getPeerClassname()."::doDelete(\$this, \$con);";
			// apply behaviors
			$this->applyBehaviorModifier('postDelete', $script, "			");
			$script .= "
			\$con->commit();
			\$this->setDeleted(true);";
		}

		$script .= "
		} catch (PropelException \$e) {
			\$con->rollBack();
			throw \$e;
		}";
	}

	/**
	 * Adds a reload() method to re-fetch the data for this object from the database.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addReload(&$script)
	{
		$table = $this->getTable();
		$script .= "
	/**
	 * Reloads this object from datastore based on primary key and (optionally) resets all associated objects.
	 *
	 * This will only work if the object has been saved and has a valid primary key set.
	 *
	 * @param      boolean \$deep (optional) Whether to also de-associated any related objects.
	 * @param      PropelPDO \$con (optional) The PropelPDO connection to use.
	 * @return     void
	 * @throws     PropelException - if this object is deleted, unsaved or doesn't have pk match in db
	 */
	public function reload(\$deep = false, PropelPDO \$con = null)
	{
		if (\$this->isDeleted()) {
			throw new PropelException(\"Cannot reload a deleted object.\");
		}

		if (\$this->isNew()) {
			throw new PropelException(\"Cannot reload an unsaved object.\");
		}

		if (\$con === null) {
			\$con = Propel::getConnection(".$this->getPeerClassname()."::DATABASE_NAME, Propel::CONNECTION_READ);
		}

		// We don't need to alter the object instance pool; we're just modifying this instance
		// already in the pool.

		\$stmt = ".$this->getPeerClassname()."::doSelectStmt(\$this->buildPkeyCriteria(), \$con);
		\$row = \$stmt->fetch(PDO::FETCH_NUM);
		\$stmt->closeCursor();
		if (!\$row) {
			throw new PropelException('Cannot find matching row in the database to reload object values.');
		}
		\$this->hydrate(\$row, 0, true); // rehydrate
";

		// support for lazy load columns
		foreach ($table->getColumns() as $col) {
			if ($col->isLazyLoad()) {
				$clo = strtolower($col->getName());
				$script .= "
		// Reset the $clo lazy-load column
		\$this->" . $clo . " = null;
		\$this->".$clo."_isLoaded = false;
";
			}
		}

		$script .= "
		if (\$deep) {  // also de-associate any related objects?
";

		foreach ($table->getForeignKeys() as $fk) {
			$varName = $this->getFKVarName($fk);
			$script .= "
			\$this->".$varName." = null;";
		}

		foreach ($table->getReferrers() as $refFK) {
			if ($refFK->isLocalPrimaryKey()) {
				$script .= "
			\$this->".$this->getPKRefFKVarName($refFK)." = null;
";
			} else {
				$script .= "
			\$this->".$this->getRefFKCollVarName($refFK)." = null;
			\$this->".$this->getRefFKLastCriteriaVarName($refFK)." = null;
";
			}
		}

		$script .= "
		} // if (deep)
	}
";
	} // addReload()

	/**
	 * Gets variable name for the Criteria which was used to fetch the objects which
	 * referencing current table by specified foreign key.
	 * @param      ForeignKey $fk
	 * @return     string
	 */
	protected function getRefFKLastCriteriaVarName(ForeignKey $fk)
	{
		return 'last' . $this->getRefFKPhpNameAffix($fk, $plural = false) . 'Criteria';
	}



	/**
	 * Adds the accessor (getter) method for getting an fkey related object.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addFKAccessor(&$script, ForeignKey $fk)
	{
		$table = $this->getTable();

		$varName = $this->getFKVarName($fk);
		$pCollName = $this->getFKPhpNameAffix($fk, $plural = true);
		
		$fkPeerBuilder = $this->getNewPeerBuilder($this->getForeignTable($fk));
		$fkObjectBuilder = $this->getNewObjectBuilder($this->getForeignTable($fk))->getStubObjectBuilder();
		$className = $fkObjectBuilder->getClassname(); // get the Classname that has maybe a prefix
		
		$and = "";
		$comma = "";
		$conditional = "";
		$argmap = array(); // foreign -> local mapping
		$argsize = 0;
		foreach ($fk->getLocalColumns() as $columnName) {
			
			$lfmap = $fk->getLocalForeignMapping();
			
			$localColumn = $table->getColumn($columnName);
			$foreignColumn = $fk->getForeignTable()->getColumn($lfmap[$columnName]);
			
			$column = $table->getColumn($columnName);
			$cptype = $column->getPhpType();
			$clo = strtolower($column->getName());
			
			if ($cptype == "integer" || $cptype == "float" || $cptype == "double") {
				$conditional .= $and . "\$this->". $clo ." != 0";
			} elseif ($cptype == "string") {
				$conditional .= $and . "(\$this->" . $clo ." !== \"\" && \$this->".$clo." !== null)";
			} else {
				$conditional .= $and . "\$this->" . $clo ." !== null";
			}
			
			$argmap[] = array('foreign' => $foreignColumn, 'local' => $localColumn);
			$and = " && ";
			$comma = ", ";
			$argsize = $argsize + 1;
		}
		
		// If the related column is a primary kay and if it's a simple association,
		// The use retrieveByPk() instead of doSelect() to take advantage of instance pooling
		$useRetrieveByPk = count($argmap) == 1 && $argmap[0]['foreign']->isPrimaryKey();

		$script .= "

	/**
	 * Get the associated $className object
	 *
	 * @param      PropelPDO Optional Connection object.
	 * @return     $className The associated $className object.
	 * @throws     PropelException
	 */
	public function get".$this->getFKPhpNameAffix($fk, $plural = false)."(PropelPDO \$con = null)
	{";
		$script .= "
		if (\$this->$varName === null && ($conditional)) {";
		if ($useRetrieveByPk) {
			$script .= "
			\$this->$varName = ".$fkPeerBuilder->getPeerClassname()."::retrieveByPk(\$this->$clo);";
		} else {
			$script .= "
			\$c = new Criteria(".$fkPeerBuilder->getPeerClassname()."::DATABASE_NAME);";
			foreach ($argmap as $el) {
				$fcol = $el['foreign'];
				$lcol = $el['local'];
				$clo = strtolower($lcol->getName());
				$script .= "
			\$c->add(".$fkPeerBuilder->getColumnConstant($fcol).", \$this->".$clo.");";
			}
			$script .= "
			\$this->$varName = ".$fkPeerBuilder->getPeerClassname()."::doSelectOne(\$c, \$con);";
		}
		if ($fk->isLocalPrimaryKey()) {
			$script .= "
			// Because this foreign key represents a one-to-one relationship, we will create a bi-directional association.
			\$this->{$varName}->set".$this->getRefFKPhpNameAffix($fk, $plural = false)."(\$this);";
		} else {
			$script .= "
			/* The following can be used additionally to
			   guarantee the related object contains a reference
			   to this object.  This level of coupling may, however, be
			   undesirable since it could result in an only partially populated collection
			   in the referenced object.
			   \$this->{$varName}->add".$this->getRefFKPhpNameAffix($fk, $plural = true)."(\$this);
			 */";
		}

		$script .= "
		}
		return \$this->$varName;
	}
";

	} // addFKAccessor


	/**
	 * Adds the method that fetches fkey-related (referencing) objects but also joins in data from another table.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addRefFKGetJoinMethods(&$script, ForeignKey $refFK)
	{
		$table = $this->getTable();
		$tblFK = $refFK->getTable();
		$join_behavior = $this->getGeneratorConfig()->getBuildProperty('useLeftJoinsInDoJoinMethods') ? 'Criteria::LEFT_JOIN' : 'Criteria::INNER_JOIN';

		$peerClassname = $this->getStubPeerBuilder()->getClassname();
		$relCol = $this->getRefFKPhpNameAffix($refFK, $plural=true);
		$collName = $this->getRefFKCollVarName($refFK);
		$lastCriteriaName = $this->getRefFKLastCriteriaVarName($refFK);

		$fkPeerBuilder = $this->getNewPeerBuilder($tblFK);

		$lastTable = "";
		foreach ($tblFK->getForeignKeys() as $fk2) {

			$tblFK2 = $this->getForeignTable($fk2);
			$doJoinGet = !$tblFK2->isForReferenceOnly();

			// it doesn't make sense to join in rows from the curent table, since we are fetching
			// objects related to *this* table (i.e. the joined rows will all be the same row as current object)
			if ($this->getTable()->getPhpName() == $tblFK2->getPhpName()) {
				$doJoinGet = false;
			}

			$relCol2 = $this->getFKPhpNameAffix($fk2, $plural = false);

			if ( $this->getRelatedBySuffix($refFK) != "" &&
			($this->getRelatedBySuffix($refFK) == $this->getRelatedBySuffix($fk2))) {
				$doJoinGet = false;
			}

			if ($doJoinGet) {
				$script .= "

	/**
	 * If this collection has already been initialized with
	 * an identical criteria, it returns the collection.
	 * Otherwise if this ".$table->getPhpName()." is new, it will return
	 * an empty collection; or if this ".$table->getPhpName()." has previously
	 * been saved, it will retrieve related $relCol from storage.
	 *
	 * This method is protected by default in order to keep the public
	 * api reasonable.  You can provide public methods for those you
	 * actually need in ".$table->getPhpName().".
	 */
	public function get".$relCol."Join".$relCol2."(\$criteria = null, \$con = null, \$join_behavior = $join_behavior)
	{";
				$script .= "
		if (\$criteria === null) {
			\$criteria = new Criteria($peerClassname::DATABASE_NAME);
		}
		elseif (\$criteria instanceof Criteria)
		{
			\$criteria = clone \$criteria;
		}

		if (\$this->$collName === null) {
			if (\$this->isNew()) {
				\$this->$collName = array();
			} else {
";
				foreach ($refFK->getForeignColumns() as $columnName) {
					$column = $table->getColumn($columnName);
					$flMap = $refFK->getForeignLocalMapping();
					$colFKName = $flMap[$columnName];
					$colFK = $tblFK->getColumn($colFKName);
					if ($colFK === null) {
						throw new EngineException("Column $colFKName not found in " . $tblFK->getName());
					}
					$clo = strtolower($column->getName());
					$script .= "
				\$criteria->add(".$fkPeerBuilder->getColumnConstant($colFK).", \$this->$clo);
";
				} // end foreach ($fk->getForeignColumns()

				$script .= "
				\$this->$collName = ".$fkPeerBuilder->getPeerClassname()."::doSelectJoin$relCol2(\$criteria, \$con, \$join_behavior);
			}
		} else {
			// the following code is to determine if a new query is
			// called for.  If the criteria is the same as the last
			// one, just return the collection.
";
				foreach ($refFK->getForeignColumns() as $columnName) {
					$column = $table->getColumn($columnName);
					$flMap = $refFK->getForeignLocalMapping();
					$colFKName = $flMap[$columnName];
					$colFK = $tblFK->getColumn($colFKName);
					$clo = strtolower($column->getName());
					$script .= "
			\$criteria->add(".$fkPeerBuilder->getColumnConstant($colFK).", \$this->$clo);
";
				} /* end foreach ($fk->getForeignColumns() */

				$script .= "
			if (!isset(\$this->$lastCriteriaName) || !\$this->".$lastCriteriaName."->equals(\$criteria)) {
				\$this->$collName = ".$fkPeerBuilder->getPeerClassname()."::doSelectJoin$relCol2(\$criteria, \$con, \$join_behavior);
			}
		}
		\$this->$lastCriteriaName = \$criteria;

		return \$this->$collName;
	}
";
			} /* end if ($doJoinGet) */

		} /* end foreach ($tblFK->getForeignKeys() as $fk2) { */

	} // function

	/**
	 * Adds the method that initializes the referrer fkey collection.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addRefFKInit(&$script, ForeignKey $refFK) {

		$relCol = $this->getRefFKPhpNameAffix($refFK, $plural = true);
		$collName = $this->getRefFKCollVarName($refFK);

		$script .= "
	/**
	 * Initializes the $collName collection (array).
	 *
	 * By default this just sets the $collName collection to an empty array (like clear$collName());
	 * however, you may wish to override this method in your stub class to provide setting appropriate
	 * to your application -- for example, setting the initial array to the values stored in database.
	 *
	 * @return     void
	 */
	public function init$relCol()
	{
		\$this->$collName = array();
	}
";
	} // addRefererInit()

	/**
	 * Adds the method that adds an object into the referrer fkey collection.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addRefFKAdd(&$script, ForeignKey $refFK)
	{
		$tblFK = $refFK->getTable();

		$joinedTableObjectBuilder = $this->getNewObjectBuilder($refFK->getTable());
		$className = $joinedTableObjectBuilder->getObjectClassname();

		$collName = $this->getRefFKCollVarName($refFK);

		$script .= "
	/**
	 * Method called to associate a $className object to this object
	 * through the $className foreign key attribute.
	 *
	 * @param      $className \$l $className
	 * @return     void
	 * @throws     PropelException
	 */
	public function add".$this->getRefFKPhpNameAffix($refFK, $plural = false)."($className \$l)
	{
		if (\$this->$collName === null) {
			\$this->init".$this->getRefFKPhpNameAffix($refFK, $plural = true)."();
		}
		if (!in_array(\$l, \$this->$collName, true)) { // only add it if the **same** object is not already associated
			array_push(\$this->$collName, \$l);
			\$l->set".$this->getFKPhpNameAffix($refFK, $plural = false)."(\$this);
		}
	}
";
	} // addRefererAdd

	/**
	 * Adds the method that returns the size of the referrer fkey collection.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addRefFKCount(&$script, ForeignKey $refFK)
	{
		$table = $this->getTable();
		$tblFK = $refFK->getTable();

		$peerClassname = $this->getStubPeerBuilder()->getClassname();

		$fkPeerBuilder = $this->getNewPeerBuilder($refFK->getTable());
		$relCol = $this->getRefFKPhpNameAffix($refFK, $plural = true);

		$collName = $this->getRefFKCollVarName($refFK);
		$lastCriteriaName = $this->getRefFKLastCriteriaVarName($refFK);

		$className = $fkPeerBuilder->getObjectClassname();

		$script .= "
	/**
	 * Returns the number of related $className objects.
	 *
	 * @param      Criteria \$criteria
	 * @param      boolean \$distinct
	 * @param      PropelPDO \$con
	 * @return     int Count of related $className objects.
	 * @throws     PropelException
	 */
	public function count$relCol(Criteria \$criteria = null, \$distinct = false, PropelPDO \$con = null)
	{";

		$script .= "
		if (\$criteria === null) {
			\$criteria = new Criteria($peerClassname::DATABASE_NAME);
		} else {
			\$criteria = clone \$criteria;
		}

		if (\$distinct) {
			\$criteria->setDistinct();
		}

		\$count = null;

		if (\$this->$collName === null) {
			if (\$this->isNew()) {
				\$count = 0;
			} else {
";
		foreach ($refFK->getLocalColumns() as $colFKName) {
			// $colFKName is local to the referring table (i.e. foreign to this table)
			$lfmap = $refFK->getLocalForeignMapping();
			$localColumn = $this->getTable()->getColumn($lfmap[$colFKName]);
			$colFK = $refFK->getTable()->getColumn($colFKName);
			$clo = strtolower($localColumn->getName());
			$script .= "
				\$criteria->add(".$fkPeerBuilder->getColumnConstant($colFK).", \$this->$clo);
";
		} // end foreach ($fk->getForeignColumns()

		$script .= "
				\$count = ".$fkPeerBuilder->getPeerClassname()."::doCount(\$criteria, false, \$con);
			}
		} else {
			// criteria has no effect for a new object
			if (!\$this->isNew()) {
				// the following code is to determine if a new query is
				// called for.  If the criteria is the same as the last
				// one, just return count of the collection.
";
		foreach ($refFK->getLocalColumns() as $colFKName) {
			// $colFKName is local to the referring table (i.e. foreign to this table)
			$lfmap = $refFK->getLocalForeignMapping();
			$localColumn = $this->getTable()->getColumn($lfmap[$colFKName]);
			$colFK = $refFK->getTable()->getColumn($colFKName);
			$clo = strtolower($localColumn->getName());
			$script .= "

				\$criteria->add(".$fkPeerBuilder->getColumnConstant($colFK).", \$this->$clo);
";
		} // foreach ($fk->getForeignColumns()
		$script .= "
				if (!isset(\$this->$lastCriteriaName) || !\$this->".$lastCriteriaName."->equals(\$criteria)) {
					\$count = ".$fkPeerBuilder->getPeerClassname()."::doCount(\$criteria, false, \$con);
				} else {
					\$count = count(\$this->$collName);
				}
			} else {
				\$count = count(\$this->$collName);
			}
		}
		return \$count;
	}
";
	} // addRefererCount

	/**
	 * Adds the method that returns the referrer fkey collection.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addRefFKGet(&$script, ForeignKey $refFK)
	{
		$table = $this->getTable();
		$tblFK = $refFK->getTable();

		$peerClassname = $this->getStubPeerBuilder()->getClassname();
		$fkPeerBuilder = $this->getNewPeerBuilder($refFK->getTable());
		$relCol = $this->getRefFKPhpNameAffix($refFK, $plural = true);

		$collName = $this->getRefFKCollVarName($refFK);
		$lastCriteriaName = $this->getRefFKLastCriteriaVarName($refFK);

		$className = $fkPeerBuilder->getObjectClassname();

		$script .= "
	/**
	 * Gets an array of $className objects which contain a foreign key that references this object.
	 *
	 * If this collection has already been initialized with an identical Criteria, it returns the collection.
	 * Otherwise if this ".$this->getObjectClassname()." has previously been saved, it will retrieve
	 * related $relCol from storage. If this ".$this->getObjectClassname()." is new, it will return
	 * an empty collection or the current collection, the criteria is ignored on a new object.
	 *
	 * @param      PropelPDO \$con
	 * @param      Criteria \$criteria
	 * @return     array {$className}[]
	 * @throws     PropelException
	 */
	public function get$relCol(\$criteria = null, PropelPDO \$con = null)
	{";

		$script .= "
		if (\$criteria === null) {
			\$criteria = new Criteria($peerClassname::DATABASE_NAME);
		}
		elseif (\$criteria instanceof Criteria)
		{
			\$criteria = clone \$criteria;
		}

		if (\$this->$collName === null) {
			if (\$this->isNew()) {
			   \$this->$collName = array();
			} else {
";
		foreach ($refFK->getLocalColumns() as $colFKName) {
			// $colFKName is local to the referring table (i.e. foreign to this table)
			$lfmap = $refFK->getLocalForeignMapping();
			$localColumn = $this->getTable()->getColumn($lfmap[$colFKName]);
			$colFK = $refFK->getTable()->getColumn($colFKName);

			$clo = strtolower($localColumn->getName());

			$script .= "
				\$criteria->add(".$fkPeerBuilder->getColumnConstant($colFK).", \$this->$clo);
";
		} // end foreach ($fk->getForeignColumns()

		$script .= "
				".$fkPeerBuilder->getPeerClassname()."::addSelectColumns(\$criteria);
				\$this->$collName = ".$fkPeerBuilder->getPeerClassname()."::doSelect(\$criteria, \$con);
			}
		} else {
			// criteria has no effect for a new object
			if (!\$this->isNew()) {
				// the following code is to determine if a new query is
				// called for.  If the criteria is the same as the last
				// one, just return the collection.
";
		foreach ($refFK->getLocalColumns() as $colFKName) {
			// $colFKName is local to the referring table (i.e. foreign to this table)
			$lfmap = $refFK->getLocalForeignMapping();
			$localColumn = $this->getTable()->getColumn($lfmap[$colFKName]);
			$colFK = $refFK->getTable()->getColumn($colFKName);
			$clo = strtolower($localColumn->getName());
			$script .= "

				\$criteria->add(".$fkPeerBuilder->getColumnConstant($colFK).", \$this->$clo);
";
		} // foreach ($fk->getForeignColumns()
		$script .= "
				".$fkPeerBuilder->getPeerClassname()."::addSelectColumns(\$criteria);
				if (!isset(\$this->$lastCriteriaName) || !\$this->".$lastCriteriaName."->equals(\$criteria)) {
					\$this->$collName = ".$fkPeerBuilder->getPeerClassname()."::doSelect(\$criteria, \$con);
				}
			}
		}
		\$this->$lastCriteriaName = \$criteria;
		return \$this->$collName;
	}
";
	} // addRefererGet()

	/**
	 * Adds the method that gets a one-to-one related referrer fkey.
	 * This is for one-to-one relationship special case.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addPKRefFKGet(&$script, ForeignKey $refFK)
	{
		$table = $this->getTable();
		$tblFK = $refFK->getTable();

		$joinedTableObjectBuilder = $this->getNewObjectBuilder($refFK->getTable());
		$joinedTablePeerBuilder = $this->getNewObjectBuilder($refFK->getTable());
		$className = $joinedTableObjectBuilder->getObjectClassname();

		$varName = $this->getPKRefFKVarName($refFK);

		$script .= "
	/**
	 * Gets a single $className object, which is related to this object by a one-to-one relationship.
	 *
	 * @param      PropelPDO \$con
	 * @return     $className
	 * @throws     PropelException
	 */
	public function get".$this->getRefFKPhpNameAffix($refFK, $plural = false)."(PropelPDO \$con = null)
	{
";
		$script .= "
		if (\$this->$varName === null && !\$this->isNew()) {";

		$lfmap = $refFK->getLocalForeignMapping();

		// remember: this object represents the foreign table,
		// so we need foreign columns of the reffk to know the local columns
		// that we need to set :)

		$localcols = $refFK->getForeignColumns();

		// we know that at least every column in the primary key of the foreign table
		// is represented in this foreign key

		$params = array();
		foreach ($tblFK->getPrimaryKey() as $col) {
			$localColumn = $table->getColumn($lfmap[$col->getName()]);
			$clo = strtolower($localColumn->getName());
			$params[] = "\$this->$clo";
		}

		$script .= "
			\$this->$varName = ".$joinedTableObjectBuilder->getPeerClassname()."::retrieveByPK(".implode(", ", $params).", \$con);
		}

		return \$this->$varName;
	}
";
	} // addPKRefFKGet()

} // PHP5ObjectBuilder

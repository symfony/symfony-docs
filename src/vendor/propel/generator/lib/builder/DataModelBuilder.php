<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * This is the base class for any builder class that is using the data model.
 *
 * This could be extended by classes that build SQL DDL, PHP classes, configuration
 * files, input forms, etc.
 *
 * The GeneratorConfig needs to be set on this class in order for the builders
 * to be able to access the propel generator build properties.  You should be
 * safe if you always use the GeneratorConfig to get a configured builder class
 * anyway.
 *
 * @author     Hans Lellelid <hans@xmpl.org>
 * @package    propel.generator.builder
 */
abstract class DataModelBuilder
{

	/**
	 * The current table.
	 * @var        Table
	 */
	private $table;

	/**
	 * The generator config object holding build properties, etc.
	 *
	 * @var        GeneratorConfig
	 */
	private $generatorConfig;

	/**
	 * An array of warning messages that can be retrieved for display (e.g. as part of phing build process).
	 * @var        array string[]
	 */
	private $warnings = array();

	/**
	 * Peer builder class for current table.
	 * @var        DataModelBuilder
	 */
	private $peerBuilder;

	/**
	 * Stub Peer builder class for current table.
	 * @var        DataModelBuilder
	 */
	private $stubPeerBuilder;

	/**
	 * Object builder class for current table.
	 * @var        DataModelBuilder
	 */
	private $objectBuilder;

	/**
	 * Stub Object builder class for current table.
	 * @var        DataModelBuilder
	 */
	private $stubObjectBuilder;

	/**
	 * Query builder class for current table.
	 * @var        DataModelBuilder
	 */
	private $queryBuilder;

	/**
	 * Stub Query builder class for current table.
	 * @var        DataModelBuilder
	 */
	private $stubQueryBuilder;

	/**
	 * TableMap builder class for current table.
	 * @var        DataModelBuilder
	 */
	protected $tablemapBuilder;
	
	/**
	 * Stub Interface builder class for current table.
	 * @var        DataModelBuilder
	 */
	private $interfaceBuilder;

	/**
	 * Stub child object for current table.
	 * @var        DataModelBuilder
	 */
	private $multiExtendObjectBuilder;

	/**
	 * Node object builder for current table.
	 * @var        DataModelBuilder
	 */
	private $nodeBuilder;

	/**
	 * Node peer builder for current table.
	 * @var        DataModelBuilder
	 */
	private $nodePeerBuilder;

	/**
	 * Stub node object builder for current table.
	 * @var        DataModelBuilder
	 */
	private $stubNodeBuilder;

	/**
	 * Stub node peer builder for current table.
	 * @var        DataModelBuilder
	 */
	private $stubNodePeerBuilder;

	/**
	 * NestedSet object builder for current table.
	 * @var        DataModelBuilder
	 */
	private $nestedSetBuilder;

	/**
	 * NestedSet peer builder for current table.
	 * @var        DataModelBuilder
	 */
	private $nestedSetPeerBuilder;

	/**
	 * The DDL builder for current table.
	 * @var        DDLBuilder
	 */
	private $ddlBuilder;

	/**
	 * The Data-SQL builder for current table.
	 * @var        DataSQLBuilder
	 */
	private $dataSqlBuilder;

	/**
	 * The Pluralizer class to use.
	 * @var        Pluralizer
	 */
	private $pluralizer;


	/**
	 * Creates new instance of DataModelBuilder subclass.
	 * @param      Table $table The Table which we are using to build [OM, DDL, etc.].
	 */
	public function __construct(Table $table)
	{
		$this->table = $table;
	}

	/**
	 * Returns new or existing Peer builder class for this table.
	 * @return     PeerBuilder
	 */
	public function getPeerBuilder()
	{
		if (!isset($this->peerBuilder)) {
			$this->peerBuilder = $this->getGeneratorConfig()->getConfiguredBuilder($this->getTable(), 'peer');
		}
		return $this->peerBuilder;
	}

	/**
	 * Returns new or existing Pluralizer class.
	 * @return     Pluralizer
	 */
	public function getPluralizer()
	{
		if (!isset($this->pluralizer)) {
			$this->pluralizer = $this->getGeneratorConfig()->getConfiguredPluralizer();
		}
		return $this->pluralizer;
	}

	/**
	 * Returns new or existing stub Peer builder class for this table.
	 * @return     PeerBuilder
	 */
	public function getStubPeerBuilder()
	{
		if (!isset($this->stubPeerBuilder)) {
			$this->stubPeerBuilder = $this->getGeneratorConfig()->getConfiguredBuilder($this->getTable(), 'peerstub');
		}
		return $this->stubPeerBuilder;
	}

	/**
	 * Returns new or existing Object builder class for this table.
	 * @return     ObjectBuilder
	 */
	public function getObjectBuilder()
	{
		if (!isset($this->objectBuilder)) {
			$this->objectBuilder = $this->getGeneratorConfig()->getConfiguredBuilder($this->getTable(), 'object');
		}
		return $this->objectBuilder;
	}

	/**
	 * Returns new or existing stub Object builder class for this table.
	 * @return     ObjectBuilder
	 */
	public function getStubObjectBuilder()
	{
		if (!isset($this->stubObjectBuilder)) {
			$this->stubObjectBuilder = $this->getGeneratorConfig()->getConfiguredBuilder($this->getTable(), 'objectstub');
		}
		return $this->stubObjectBuilder;
	}

	/**
	 * Returns new or existing Query builder class for this table.
	 * @return     ObjectBuilder
	 */
	public function getQueryBuilder()
	{
		if (!isset($this->queryBuilder)) {
			$this->queryBuilder = $this->getGeneratorConfig()->getConfiguredBuilder($this->getTable(), 'query');
		}
		return $this->queryBuilder;
	}

	/**
	 * Returns new or existing stub Query builder class for this table.
	 * @return     ObjectBuilder
	 */
	public function getStubQueryBuilder()
	{
		if (!isset($this->stubQueryBuilder)) {
			$this->stubQueryBuilder = $this->getGeneratorConfig()->getConfiguredBuilder($this->getTable(), 'querystub');
		}
		return $this->stubQueryBuilder;
	}
	
	/**
	 * Returns new or existing Object builder class for this table.
	 * @return     ObjectBuilder
	 */
	public function getTableMapBuilder()
	{
		if (!isset($this->tablemapBuilder)) {
			$this->tablemapBuilder = $this->getGeneratorConfig()->getConfiguredBuilder($this->getTable(), 'tablemap');
		}
		return $this->tablemapBuilder;
	}
	
	/**
	 * Returns new or existing stub Interface builder class for this table.
	 * @return     ObjectBuilder
	 */
	public function getInterfaceBuilder()
	{
		if (!isset($this->interfaceBuilder)) {
			$this->interfaceBuilder = $this->getGeneratorConfig()->getConfiguredBuilder($this->getTable(), 'interface');
		}
		return $this->interfaceBuilder;
	}

	/**
	 * Returns new or existing stub child object builder class for this table.
	 * @return     ObjectBuilder
	 */
	public function getMultiExtendObjectBuilder()
	{
		if (!isset($this->multiExtendObjectBuilder)) {
			$this->multiExtendObjectBuilder = $this->getGeneratorConfig()->getConfiguredBuilder($this->getTable(), 'objectmultiextend');
		}
		return $this->multiExtendObjectBuilder;
	}

	/**
	 * Returns new or existing node Object builder class for this table.
	 * @return     ObjectBuilder
	 */
	public function getNodeBuilder()
	{
		if (!isset($this->nodeBuilder)) {
			$this->nodeBuilder = $this->getGeneratorConfig()->getConfiguredBuilder($this->getTable(), 'node');
		}
		return $this->nodeBuilder;
	}

	/**
	 * Returns new or existing node Peer builder class for this table.
	 * @return     PeerBuilder
	 */
	public function getNodePeerBuilder()
	{
		if (!isset($this->nodePeerBuilder)) {
			$this->nodePeerBuilder = $this->getGeneratorConfig()->getConfiguredBuilder($this->getTable(), 'nodepeer');
		}
		return $this->nodePeerBuilder;
	}

	/**
	 * Returns new or existing stub node Object builder class for this table.
	 * @return     ObjectBuilder
	 */
	public function getStubNodeBuilder()
	{
		if (!isset($this->stubNodeBuilder)) {
			$this->stubNodeBuilder = $this->getGeneratorConfig()->getConfiguredBuilder($this->getTable(), 'nodestub');
		}
		return $this->stubNodeBuilder;
	}

	/**
	 * Returns new or existing stub node Peer builder class for this table.
	 * @return     PeerBuilder
	 */
	public function getStubNodePeerBuilder()
	{
		if (!isset($this->stubNodePeerBuilder)) {
			$this->stubNodePeerBuilder = $this->getGeneratorConfig()->getConfiguredBuilder($this->getTable(), 'nodepeerstub');
		}
		return $this->stubNodePeerBuilder;
	}

	/**
	 * Returns new or existing nested set object builder class for this table.
	 * @return     ObjectBuilder
	 */
	public function getNestedSetBuilder()
	{
		if (!isset($this->nestedSetBuilder)) {
			$this->nestedSetBuilder = $this->getGeneratorConfig()->getConfiguredBuilder($this->getTable(), 'nestedset');
		}
		return $this->nestedSetBuilder;
	}

	/**
	 * Returns new or existing nested set Peer builder class for this table.
	 * @return     PeerBuilder
	 */
	public function getNestedSetPeerBuilder()
	{
		if (!isset($this->nestedSetPeerBuilder)) {
			$this->nestedSetPeerBuilder = $this->getGeneratorConfig()->getConfiguredBuilder($this->getTable(), 'nestedsetpeer');
		}
		return $this->nestedSetPeerBuilder;
	}

	/**
	 * Returns new or existing ddl builder class for this table.
	 * @return     DDLBuilder
	 */
	public function getDDLBuilder()
	{
		if (!isset($this->ddlBuilder)) {
			$this->ddlBuilder = $this->getGeneratorConfig()->getConfiguredBuilder($this->getTable(), 'ddl');
		}
		return $this->ddlBuilder;
	}

	/**
	 * Returns new or existing data sql builder class for this table.
	 * @return     DataSQLBuilder
	 */
	public function getDataSQLBuilder()
	{
		if (!isset($this->dataSqlBuilder)) {
			$this->dataSqlBuilder = $this->getGeneratorConfig()->getConfiguredBuilder($this->getTable(), 'datasql');
		}
		return $this->dataSqlBuilder;
	}

	/**
	 * Convenience method to return a NEW Peer class builder instance.
   * 
	 * This is used very frequently from the peer and object builders to get
	 * a peer builder for a RELATED table.
	 *
	 * @param      Table $table
	 * @return     PeerBuilder
	 */
	public function getNewPeerBuilder(Table $table)
	{
		return $this->getGeneratorConfig()->getConfiguredBuilder($table, 'peer');
	}
	
	/**
	 * Convenience method to return a NEW Peer stub class builder instance.
	 * 
	 * This is used from the peer and object builders to get
	 * a peer builder for a RELATED table.
	 *
	 * @param      Table $table
	 * @return     PeerBuilder
	 */
	public function getNewStubPeerBuilder(Table $table)
	{
		return $this->getGeneratorConfig()->getConfiguredBuilder($table, 'peerstub');
	}

	/**
	 * Convenience method to return a NEW Object class builder instance.
	 *
	 * This is used very frequently from the peer and object builders to get
	 * an object builder for a RELATED table.
	 *
	 * @param      Table $table
	 * @return     ObjectBuilder
	 */
	public function getNewObjectBuilder(Table $table)
	{
		return $this->getGeneratorConfig()->getConfiguredBuilder($table, 'object');
	}

	/**
	 * Convenience method to return a NEW Object stub class builder instance.
	 *
	 * This is used from the query builders to get
	 * an object builder for a RELATED table.
	 *
	 * @param      Table $table
	 * @return     ObjectBuilder
	 */
	public function getNewStubObjectBuilder(Table $table)
	{
		return $this->getGeneratorConfig()->getConfiguredBuilder($table, 'objectstub');
	}

	/**
	 * Convenience method to return a NEW query class builder instance.
	 *
	 * This is used from the query builders to get
	 * a query builder for a RELATED table.
	 *
	 * @param      Table $table
	 * @return     QueryBuilder
	 */
	public function getNewQueryBuilder(Table $table)
	{
		return $this->getGeneratorConfig()->getConfiguredBuilder($table, 'query');
	}
	
	/**
	 * Convenience method to return a NEW query stub class builder instance.
	 *
	 * This is used from the query builders to get
	 * a query builder for a RELATED table.
	 *
	 * @param      Table $table
	 * @return     QueryBuilder
	 */
	public function getNewStubQueryBuilder(Table $table)
	{
		return $this->getGeneratorConfig()->getConfiguredBuilder($table, 'querystub');
	}

	/**
	 * Returns new Query Inheritance builder class for this table.
	 * @return     ObjectBuilder
	 */
	public function getNewQueryInheritanceBuilder($child)
	{
		$queryInheritanceBuilder = $this->getGeneratorConfig()->getConfiguredBuilder($this->getTable(), 'queryinheritance');
		$queryInheritanceBuilder->setChild($child);
		return $queryInheritanceBuilder;
	}

	/**
	 * Returns new stub Query Inheritance builder class for this table.
	 * @return     ObjectBuilder
	 */
	public function getNewStubQueryInheritanceBuilder($child)
	{
		$stubQueryInheritanceBuilder = $this->getGeneratorConfig()->getConfiguredBuilder($this->getTable(), 'queryinheritancestub');
		$stubQueryInheritanceBuilder->setChild($child);
		return $stubQueryInheritanceBuilder;
	}
	
	/**
	 * Gets the GeneratorConfig object.
	 *
	 * @return     GeneratorConfig
	 */
	public function getGeneratorConfig()
	{
		return $this->generatorConfig;
	}

	/**
	 * Get a specific [name transformed] build property.
	 *
	 * @param      string $name
	 * @return     string
	 */
	public function getBuildProperty($name)
	{
		if ($this->getGeneratorConfig()) {
			return $this->getGeneratorConfig()->getBuildProperty($name);
		}
		return null; // just to be explicit
	}

	/**
	 * Sets the GeneratorConfig object.
	 *
	 * @param      GeneratorConfig $v
	 */
	public function setGeneratorConfig(GeneratorConfig $v)
	{
		$this->generatorConfig = $v;
	}

	/**
	 * Sets the table for this builder.
	 * @param      Table $table
	 */
	public function setTable(Table $table)
	{
		$this->table = $table;
	}

	/**
	 * Returns the current Table object.
	 * @return     Table
	 */
	public function getTable()
	{
		return $this->table;
	}

	/**
	 * Convenience method to returns the Platform class for this table (database).
	 * @return     Platform
	 */
	public function getPlatform()
	{
		if ($this->getTable() && $this->getTable()->getDatabase()) {
			return $this->getTable()->getDatabase()->getPlatform();
		}
	}

	/**
	 * Convenience method to returns the database for current table.
	 * @return     Database
	 */
	public function getDatabase()
	{
		if ($this->getTable()) {
			return $this->getTable()->getDatabase();
		}
	}

	/**
	 * Pushes a message onto the stack of warnings.
	 * @param      string $msg The warning message.
	 */
	protected function warn($msg)
	{
		$this->warnings[] = $msg;
	}

	/**
	 * Gets array of warning messages.
	 * @return     array string[]
	 */
	public function getWarnings()
	{
		return $this->warnings;
	}

	/**
	 * Wraps call to Platform->quoteIdentifier() with a check to see whether quoting is enabled.
	 *
	 * All subclasses should call this quoteIdentifier() method rather than calling the Platform
	 * method directly.  This method is used by both DataSQLBuilder and DDLBuilder, and potentially
	 * in the OM builders also, which is why it is defined in this class.
	 *
	 * @param      string $text The text to quote.
	 * @return     string Quoted text.
	 */
	public function quoteIdentifier($text)
	{
		if (!$this->getBuildProperty('disableIdentifierQuoting')) {
			return $this->getPlatform()->quoteIdentifier($text);
		}
		return $text;
	}

	/**
	 * Returns the name of the current class being built, with a possible prefix.
	 * @return     string
	 * @see        OMBuilder#getClassname()
	 */
	public function prefixClassname($identifier)
	{
		return $this->getBuildProperty('classPrefix') . $identifier;
	}

}

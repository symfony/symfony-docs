<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

require_once 'model/XMLElement.php';
require_once 'exception/EngineException.php';
require_once 'model/PropelTypes.php';
require_once 'model/Inheritance.php';
require_once 'model/Domain.php';
require_once 'model/ColumnDefaultValue.php';

/**
 * A Class for holding data about a column used in an Application.
 *
 * @author     Hans Lellelid <hans@xmpl.org> (Propel)
 * @author     Leon Messerschmidt <leon@opticode.co.za> (Torque)
 * @author     Jason van Zyl <jvanzyl@apache.org> (Torque)
 * @author     Jon S. Stevens <jon@latchkey.com> (Torque)
 * @author     Daniel Rall <dlr@finemaltcoding.com> (Torque)
 * @author     Byron Foster <byron_foster@yahoo.com> (Torque)
 * @author     Bernd Goldschmidt <bgoldschmidt@rapidsoft.de>
 * @version    $Revision: 1778 $
 * @package    propel.generator.model
 */
class Column extends XMLElement
{

	const DEFAULT_TYPE = "VARCHAR";
	const DEFAULT_VISIBILITY = 'public';
	public static $valid_visibilities = array('public', 'protected', 'private');

	private $name;
	private $description;
	private $phpName = null;
	private $phpNamingMethod;
	private $isNotNull = false;
	private $size;
	private $namePrefix;
	private $accessorVisibility;
	private $mutatorVisibility;

	/**
	 * The name to use for the Peer constant that identifies this column.
	 * (Will be converted to all-uppercase in the templates.)
	 * @var				 string
	 */
	private $peerName;

	/**
	 * Native PHP type (scalar or class name)
	 * @var				 string "string", "boolean", "int", "double"
	 */
	private $phpType;

	/**
	 * @var				 Table
	 */
	private $parentTable;

	private $position;
	private $isPrimaryKey = false;
	private $isNodeKey = false;
	private $nodeKeySep;
	private $isNestedSetLeftKey = false;
	private $isNestedSetRightKey = false;
	private $isTreeScopeKey = false;
	private $isUnique = false;
	private $isAutoIncrement = false;
	private $isLazyLoad = false;
	private $defaultValue;
	private $referrers;
	private $isPrimaryString = false;

	// only one type is supported currently, which assumes the
	// column either contains the classnames or a key to
	// classnames specified in the schema.	Others may be
	// supported later.
	private $inheritanceType;
	private $isInheritance;
	private $isEnumeratedClasses;
	private $inheritanceList;
	private $needsTransactionInPostgres; //maybe this can be retrieved from vendorSpecificInfo

	/** class name to do input validation on this column */
	private $inputValidator = null;

	/**
	 * @var				 Domain The domain object associated with this Column.
	 */
	private $domain;

	/**
	 * Creates a new column and set the name
	 *
	 * @param			 name column name
	 */
	public function __construct($name = null)
	{
		$this->name = $name;
	}

	/**
	 * Return a comma delimited string listing the specified columns.
	 *
	 * @param			 columns Either a list of <code>Column</code> objects, or
	 * a list of <code>String</code> objects with column names.
	 * @deprecated Use the DDLBuilder->getColumnList() method instead; this will be removed in 1.3
	 */
	public static function makeList($columns, Platform $platform)
	{
		$list = array();
		foreach ($columns as $col) {
			if ($col instanceof Column) {
				$col = $col->getName();
			}
			$list[] = $platform->quoteIdentifier($col);
		}
		return implode(", ", $list);
	}

	/**
	 * Sets up the Column object based on the attributes that were passed to loadFromXML().
	 * @see				 parent::loadFromXML()
	 */
	protected function setupObject()
	{
		try {
			$dom = $this->getAttribute("domain");
			if ($dom)	 {
				$this->getDomain()->copy($this->getTable()->getDatabase()->getDomain($dom));
			} else {
				$type = strtoupper($this->getAttribute("type"));
				if ($type) {
					$this->getDomain()->copy($this->getPlatform()->getDomainForType($type));
				} else {
					$this->getDomain()->copy($this->getPlatform()->getDomainForType(self::DEFAULT_TYPE));
				}
			}

			$this->name = $this->getAttribute("name");
			$this->phpName = $this->getAttribute("phpName");
			$this->phpType = $this->getAttribute("phpType");

			if ($this->getAttribute("prefix", null) !== null) {
				$this->namePrefix = $this->getAttribute("prefix");
			} elseif ($this->getTable()->getAttribute('columnPrefix', null) !== null) {
				$this->namePrefix = $this->getTable()->getAttribute('columnPrefix');
			} else {
				$this->namePrefix = '';
			}

			// Accessor visibility
			if ($this->getAttribute('accessorVisibility', null) !==	 null) {
				$this->setAccessorVisibility($this->getAttribute('accessorVisibility'));
			} elseif ($this->getTable()->getAttribute('defaultAccessorVisibility', null) !== null) {
				$this->setAccessorVisibility($this->getTable()->getAttribute('defaultAccessorVisibility'));
			} elseif ($this->getTable()->getDatabase()->getAttribute('defaultAccessorVisibility', null) !== null) {
				$this->setAccessorVisibility($this->getTable()->getDatabase()->getAttribute('defaultAccessorVisibility'));
			} else {
				$this->setAccessorVisibility(self::DEFAULT_VISIBILITY);
			}

			// Mutator visibility
			if ($this->getAttribute('mutatorVisibility', null) !==	null) {
				$this->setMutatorVisibility($this->getAttribute('mutatorVisibility'));
			} elseif ($this->getTable()->getAttribute('defaultMutatorVisibility', null) !== null) {
				$this->setMutatorVisibility($this->getTable()->getAttribute('defaultMutatorVisibility'));
			} elseif ($this->getTable()->getDatabase()->getAttribute('defaultMutatorVisibility', null) !== null) {
				$this->setMutatorVisibility($this->getTable()->getDatabase()->getAttribute('defaultMutatorVisibility'));
			} else {
				$this->setMutatorVisibility(self::DEFAULT_VISIBILITY);
			}

			$this->peerName = $this->getAttribute("peerName");

			// retrieves the method for converting from specified name to a PHP name, defaulting to parent tables default method
			$this->phpNamingMethod = $this->getAttribute("phpNamingMethod", $this->parentTable->getDatabase()->getDefaultPhpNamingMethod());

			$this->isPrimaryString = $this->booleanValue($this->getAttribute("primaryString"));
			
			$this->isPrimaryKey = $this->booleanValue($this->getAttribute("primaryKey"));

			$this->isNodeKey = $this->booleanValue($this->getAttribute("nodeKey"));
			$this->nodeKeySep = $this->getAttribute("nodeKeySep", ".");

			$this->isNestedSetLeftKey = $this->booleanValue($this->getAttribute("nestedSetLeftKey"));
			$this->isNestedSetRightKey = $this->booleanValue($this->getAttribute("nestedSetRightKey"));
			$this->isTreeScopeKey = $this->booleanValue($this->getAttribute("treeScopeKey"));

			$this->isNotNull = ($this->booleanValue($this->getAttribute("required"), false) || $this->isPrimaryKey); // primary keys are required

			//AutoIncrement/Sequences
			$this->isAutoIncrement = $this->booleanValue($this->getAttribute("autoIncrement"));
			$this->isLazyLoad = $this->booleanValue($this->getAttribute("lazyLoad"));

			// Add type, size information to associated Domain object
			$this->getDomain()->replaceSqlType($this->getAttribute("sqlType"));
			if (!$this->getAttribute("size") && $this->getDomain()->getType() == 'VARCHAR' && !$this->getAttribute("sqlType")) {
				$size = 255;
			} else {
				$size = $this->getAttribute("size");
			}
			$this->getDomain()->replaceSize($size);
			$this->getDomain()->replaceScale($this->getAttribute("scale"));

			$defval = $this->getAttribute("defaultValue", $this->getAttribute("default"));
			if ($defval !== null && strtolower($defval) !== 'null') {
				$this->getDomain()->setDefaultValue(new ColumnDefaultValue($defval, ColumnDefaultValue::TYPE_VALUE));
			} elseif ($this->getAttribute("defaultExpr") !== null) {
				$this->getDomain()->setDefaultValue(new ColumnDefaultValue($this->getAttribute("defaultExpr"), ColumnDefaultValue::TYPE_EXPR));
			}

			$this->inheritanceType = $this->getAttribute("inheritance");
			$this->isInheritance = ($this->inheritanceType !== null
			&& $this->inheritanceType !== "false"); // here we are only checking for 'false', so don't
			// use boleanValue()

			$this->inputValidator = $this->getAttribute("inputValidator");
			$this->description = $this->getAttribute("description");
		} catch (Exception $e) {
			throw new EngineException("Error setting up column " . var_export($this->getAttribute("name"), true) . ": " . $e->getMessage());
		}
	}

	/**
	 * Gets domain for this column, creating a new empty domain object if none is set.
	 * @return		 Domain
	 */
	public function getDomain()
	{
		if ($this->domain === null) {
			$this->domain = new Domain();
		}
		return $this->domain;
	}

	/**
	 * Returns table.column
	 */
	public function getFullyQualifiedName()
	{
		return ($this->parentTable->getName() . '.' . strtoupper($this->getName()));
	}

	/**
	 * Get the name of the column
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Set the name of the column
	 */
	public function setName($newName)
	{
		$this->name = $newName;
	}

	/**
	 * Get the description for the Table
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * Set the description for the Table
	 *
	 * @param			 newDescription description for the Table
	 */
	public function setDescription($newDescription)
	{
		$this->description = $newDescription;
	}

	/**
	 * Get name to use in PHP sources. It will set & return
	 * a self-generated phpName from it's name if it's
	 * not already set.
	 * @return		 string
	 */
	public function getPhpName()
	{
		if ($this->phpName === null) {
			$this->setPhpName();
		}
		return $this->phpName;
	}

	/**
	 * Set name to use in PHP sources.
	 * 
	 * It will generate a phpName from it's name if no
	 * $phpName is passed.
	 * 
	 * @param		String $phpName PhpName to be set
	 */
	public function setPhpName($phpName = null)
	{
		if ($phpName == null) {
			$this->phpName = self::generatePhpName($this->name, $this->phpNamingMethod, $this->namePrefix);
		} else {
			$this->phpName = $phpName;
		}
	}

	/**
	 * Get studly version of PHP name.
	 *
	 * The studly name is the PHP name with the first character lowercase.
	 *
	 * @return		 string
	 */
	public function getStudlyPhpName()
	{
		$phpname = $this->getPhpName();
		if (strlen($phpname) > 1) {
			return strtolower(substr($phpname, 0, 1)) . substr($phpname, 1);
		} else { // 0 or 1 chars (I suppose that's rare)
			return strtolower($phpname);
		}
	}

	/**
	 * Get the visibility of the accessors of this column / attribute
	 * @return		 string
	 */
	public function getAccessorVisibility() {
		if ($this->accessorVisibility !== null) {
			return $this->accessorVisibility;
		} else {
			return self::DEFAULT_VISIBILITY;
		}
	}

	/**
	 * Set the visibility of the accessor methods for this column / attribute
	 * @param			 $newVisibility string
	 */
	public function setAccessorVisibility($newVisibility) {
		if (in_array($newVisibility, self::$valid_visibilities)) {
			$this->accessorVisibility = $newVisibility;
		} else {
			$this->accessorVisibility = self::DEFAULT_VISIBILITY;
		}

	}

	/**
	 * Get the visibility of the mutator of this column / attribute
	 * @return		 string
	 */
	public function getMutatorVisibility() {
		if ($this->mutatorVisibility !== null) {
			return $this->mutatorVisibility;
		} else {
			return self::DEFAULT_VISIBILITY;
		}
	}

	/**
	 * Set the visibility of the mutator methods for this column / attribute
	 * @param			 $newVisibility string
	 */
	public function setMutatorVisibility($newVisibility) {
		if (in_array($newVisibility, self::$valid_visibilities)) {
			$this->mutatorVisibility = $newVisibility;
		} else {
			$this->mutatorVisibility = self::DEFAULT_VISIBILITY;
		}

	}

	/**
	 * Get the column constant name (e.g. PeerName::COLUMN_NAME).
	 *
	 * @return		 string A column constant name for insertion into PHP code
	 */
	public function getConstantName()
	{
		$classname = $this->getTable()->getPhpName() . 'Peer';
		$const = $this->getConstantColumnName();
		return $classname.'::'.$const;
	}

	public function getConstantColumnName()
	{
		// was it overridden in schema.xml ?
		if ($this->getPeerName()) {
			return strtoupper($this->getPeerName());
		} else {
			return strtoupper($this->getName());
		}
	}

	/**
	 * Get the Peer constant name that will identify this column.
	 * @return		 string
	 */
	public function getPeerName() {
		return $this->peerName;
	}

	/**
	 * Set the Peer constant name that will identify this column.
	 * @param			 $name string
	 */
	public function setPeerName($name) {
		$this->peerName = $name;
	}

	/**
	 * Get type to use in PHP sources.
	 *
	 * If no type has been specified, then uses results of getPhpNative().
	 *
	 * @return		 string The type name.
	 * @see				 getPhpNative()
	 */
	public function getPhpType()
	{
		if ($this->phpType !== null) {
			return $this->phpType;
		}
		return $this->getPhpNative();
	}

	/**
	 * Get the location of this column within the table (one-based).
	 * @return		 int value of position.
	 */
	public function getPosition()
	{
		return $this->position;
	}

	/**
	 * Get the location of this column within the table (one-based).
	 * @param			 int $v Value to assign to position.
	 */
	public function setPosition($v)
	{
		$this->position = $v;
	}

	/**
	 * Set the parent Table of the column
	 */
	public function setTable(Table $parent)
	{
		$this->parentTable = $parent;
	}

	/**
	 * Get the parent Table of the column
	 */
	public function getTable()
	{
		return $this->parentTable;
	}

	/**
	 * Returns the Name of the table the column is in
	 */
	public function getTableName()
	{
		return $this->parentTable->getName();
	}

	/**
	 * Adds a new inheritance definition to the inheritance list and set the
	 * parent column of the inheritance to the current column
	 * @param			 mixed $inhdata Inheritance or XML data.
	 */
	public function addInheritance($inhdata)
	{
		if ($inhdata instanceof Inheritance) {
			$inh = $inhdata;
			$inh->setColumn($this);
			if ($this->inheritanceList === null) {
				$this->inheritanceList = array();
				$this->isEnumeratedClasses = true;
			}
			$this->inheritanceList[] = $inh;
			return $inh;
		} else {
			$inh = new Inheritance();
			$inh->loadFromXML($inhdata);
			return $this->addInheritance($inh);
		}
	}

	/**
	 * Get the inheritance definitions.
	 */
	public function getChildren()
	{
		return $this->inheritanceList;
	}

	/**
	 * Determine if this column is a normal property or specifies a
	 * the classes that are represented in the table containing this column.
	 */
	public function isInheritance()
	{
		return $this->isInheritance;
	}

	/**
	 * Determine if possible classes have been enumerated in the xml file.
	 */
	public function isEnumeratedClasses()
	{
		return $this->isEnumeratedClasses;
	}

	/**
	 * Return the isNotNull property of the column
	 */
	public function isNotNull()
	{
		return $this->isNotNull;
	}

	/**
	 * Set the isNotNull property of the column
	 */
	public function setNotNull($status)
	{
		$this->isNotNull = (boolean) $status;
	}

	/**
	 * Return NOT NULL String for this column
	 *
	 * @return		 "NOT NULL" if null values are not allowed or an empty string.
	 */
	public function getNotNullString()
	{
		return $this->getTable()->getDatabase()->getPlatform()->getNullString($this->isNotNull());
	}

	/**
	 * Set whether the column is the primary string, 
	 * i.e. whether its value is the default string representation of the table
	 * @param			 boolean $v
	 */
	public function setPrimaryString($v)
	{
		$this->isPrimaryString = (boolean) $v;
	}

	/**
	 * Return true if the column is the primary string,
	 * i.e. if its value is the default string representation of the table
	 */
	public function isPrimaryString()
	{
		return $this->isPrimaryString;
	}

	/**
	 * Set whether the column is a primary key or not.
	 * @param			 boolean $v
	 */
	public function setPrimaryKey($v)
	{
		$this->isPrimaryKey = (boolean) $v;
	}

	/**
	 * Return true if the column is a primary key
	 */
	public function isPrimaryKey()
	{
		return $this->isPrimaryKey;
	}

	/**
	 * Set if the column is the node key of a tree
	 */
	public function setNodeKey($nk)
	{
		$this->isNodeKey = (boolean) $nk;
	}

	/**
	 * Return true if the column is a node key of a tree
	 */
	public function isNodeKey()
	{
		return $this->isNodeKey;
	}

	/**
	 * Set if the column is the node key of a tree
	 */
	public function setNodeKeySep($sep)
	{
		$this->nodeKeySep = (string) $sep;
	}

	/**
	 * Return true if the column is a node key of a tree
	 */
	public function getNodeKeySep()
	{
		return $this->nodeKeySep;
	}

	/**
	 * Set if the column is the nested set left key of a tree
	 */
	public function setNestedSetLeftKey($nslk)
	{
		$this->isNestedSetLeftKey = (boolean) $nslk;
	}

	/**
	 * Return true if the column is a nested set key of a tree
	 */
	public function isNestedSetLeftKey()
	{
		return $this->isNestedSetLeftKey;
	}

	/**
	 * Set if the column is the nested set right key of a tree
	 */
	public function setNestedSetRightKey($nsrk)
	{
		$this->isNestedSetRightKey = (boolean) $nsrk;
	}

	/**
	 * Return true if the column is a nested set right key of a tree
	 */
	public function isNestedSetRightKey()
	{
		return $this->isNestedSetRightKey;
	}

	/**
	 * Set if the column is the scope key of a tree
	 */
	public function setTreeScopeKey($tsk)
	{
		$this->isTreeScopeKey = (boolean) $tsk;
	}

	/**
	 * Return true if the column is a scope key of a tree
	 * @return		 boolean
	 */
	public function isTreeScopeKey()
	{
		return $this->isTreeScopeKey;
	}

	/**
	 * Set true if the column is UNIQUE
	 * @param			 boolean $u
	 */
	public function setUnique($u)
	{
		$this->isUnique = $u;
	}

	/**
	 * Get the UNIQUE property.
	 * @return		 boolean
	 */
	public function isUnique()
	{
		return $this->isUnique;
	}

	/**
	 * Return true if the column requires a transaction in Postgres
	 * @return		 boolean
	 */
	public function requiresTransactionInPostgres()
	{
		return $this->needsTransactionInPostgres;
	}

	/**
	 * Utility method to determine if this column is a foreign key.
	 * @return		 boolean
	 */
	public function isForeignKey()
	{
		return (count($this->getForeignKeys()) > 0);
	}

	/**
	 * Whether this column is a part of more than one foreign key.
	 * @return		 boolean
	 */
	public function hasMultipleFK()
	{
		return (count($this->getForeignKeys()) > 1);
	}

	/**
	 * Get the foreign key objects for this column (if it is a foreign key or part of a foreign key)
	 * @return		 array
	 */
	public function getForeignKeys()
	{
		return $this->parentTable->getColumnForeignKeys($this->name);
	}

	/**
	 * Adds the foreign key from another table that refers to this column.
	 */
	public function addReferrer(ForeignKey $fk)
	{
		if ($this->referrers === null) {
			$this->referrers = array();
		}
		$this->referrers[] = $fk;
	}

	/**
	 * Get list of references to this column.
	 */
	public function getReferrers()
	{
		if ($this->referrers === null) {
			$this->referrers = array();
		}
		return $this->referrers;
	}

	/**
	 * Sets the domain up for specified Propel type.
	 *
	 * Calling this method will implicitly overwrite any previously set type,
	 * size, scale (or other domain attributes).
	 *
	 * @param			 string $propelType
	 */
	public function setDomainForType($propelType)
	{
		$this->getDomain()->copy($this->getPlatform()->getDomainForType($propelType));
	}

	/**
	 * Sets the propel colunm type.
	 * @param			 string $propelType
	 * @see				 Domain::setType()
	 */
	public function setType($propelType)
	{
		$this->getDomain()->setType($propelType);
		if ($propelType == PropelTypes::VARBINARY|| $propelType == PropelTypes::LONGVARBINARY || $propelType == PropelTypes::BLOB) {
			$this->needsTransactionInPostgres = true;
		}
	}

	/**
	 * Returns the Propel column type as a string.
	 * @return		 string The constant representing Propel type: e.g. "VARCHAR".
	 * @see				 Domain::getType()
	 */
	public function getType()
	{
		return $this->getDomain()->getType();
	}

	/**
	 * Returns the column PDO type integer for this column's Propel type.
	 * @return		 int The integer value representing PDO type param: e.g. PDO::PARAM_INT
	 */
	public function getPDOType()
	{
		return PropelTypes::getPDOType($this->getType());
	}

	/**
	 * Returns the column type as given in the schema as an object
	 */
	public function getPropelType()
	{
		return $this->getType();
	}

	/**
	 * Utility method to know whether column needs Blob/Lob handling.
	 * @return		 boolean
	 */
	public function isLobType()
	{
		return PropelTypes::isLobType($this->getType());
	}

	/**
	 * Utility method to see if the column is text type.
	 */
	public function isTextType()
	{
		return PropelTypes::isTextType($this->getType());
	}

	/**
	 * Utility method to see if the column is numeric type.
	 * @return		 boolean
	 */
	public function isNumericType()
	{
		return PropelTypes::isNumericType($this->getType());
	}

	/**
	 * Utility method to see if the column is boolean type.
	 * @return		 boolean
	 */
	public function isBooleanType()
	{
		return PropelTypes::isBooleanType($this->getType());
	}
	
	/**
	 * Utility method to know whether column is a temporal column.
	 * @return		 boolean
	 */
	public function isTemporalType()
	{
		return PropelTypes::isTemporalType($this->getType());
	}

	/**
	 * @see				 XMLElement::appendXml(DOMNode)
	 */
	public function appendXml(DOMNode $node)
	{
		$doc = ($node instanceof DOMDocument) ? $node : $node->ownerDocument;

		$colNode = $node->appendChild($doc->createElement('column'));
		$colNode->setAttribute('name', $this->name);

		if ($this->phpName !== null) {
			$colNode->setAttribute('phpName', $this->getPhpName());
		}

		$colNode->setAttribute('type', $this->getType());

		$domain = $this->getDomain();

		if ($domain->getSize() !== null) {
			$colNode->setAttribute('size', $domain->getSize());
		}

		if ($domain->getScale() !== null) {
			$colNode->setAttribute('scale', $domain->getScale());
		}

		if ($this->isPrimaryKey) {
			$colNode->setAttribute('primaryKey', var_export($this->isPrimaryKey, true));
		}

		if ($this->isAutoIncrement) {
			$colNode->setAttribute('autoIncrement', var_export($this->isAutoIncrement, true));
		}

		if ($this->isNotNull) {
			$colNode->setAttribute('required', 'true');
		} else {
			$colNode->setAttribute('required', 'false');
		}

		if ($domain->getDefaultValue() !== null) {
			$def = $domain->getDefaultValue();
			if ($def->isExpression()) {
				$colNode->setAttribute('defaultExpr', $def->getValue());
			} else {
				$colNode->setAttribute('defaultValue', $def->getValue());
			}
		}

		if ($this->isInheritance()) {
			$colNode->setAttribute('inheritance', $this->inheritanceType);
			foreach ($this->inheritanceList as $inheritance) {
				$inheritance->appendXml($colNode);
			}
		}

		if ($this->isNodeKey()) {
			$colNode->setAttribute('nodeKey', 'true');
			if ($this->getNodeKeySep() !== null) {
				$colNode->setAttribute('nodeKeySep', $this->nodeKeySep);
			}
		}

		foreach ($this->vendorInfos as $vi) {
			$vi->appendXml($colNode);
		}
	}

	/**
	 * Returns the size of the column
	 * @return		 string
	 */
	public function getSize()
	{
		return $this->domain->getSize();
	}

	/**
	 * Set the size of the column
	 * @param			 string $newSize
	 */
	public function setSize($newSize)
	{
		$this->domain->setSize($newSize);
	}

	/**
	 * Returns the scale of the column
	 * @return		 string
	 */
	public function getScale()
	{
		return $this->domain->getScale();
	}

	/**
	 * Set the scale of the column
	 * @param			 string $newScale
	 */
	public function setScale($newScale)
	{
		$this->domain->setScale($newScale);
	}

	/**
	 * Return the size in brackets for use in an sql
	 * schema if the type is String.	Otherwise return an empty string
	 */
	public function printSize()
	{
		return $this->domain->printSize();
	}

	/**
	 * Return a string that will give this column a default value.
	 * @return		 string
	 */
	public function getDefaultSetting()
	{
		$dflt = "";
		$defaultValue = $this->getDefaultValue();
		if ($defaultValue !== null) {
			$dflt .= "default ";

			if ($this->getDefaultValue()->isExpression()) {
				$dflt .= $this->getDefaultValue()->getValue();
			} else {
				if ($this->isTextType()) {
					$dflt .= $this->getPlatform()->quote($defaultValue->getValue());
				} elseif ($this->getType() == PropelTypes::BOOLEAN) {
					$dflt .= $this->getPlatform()->getBooleanString($defaultValue->getValue());
				} else {
					$dflt .= $defaultValue->getValue();
				}
			}
		}
		return $dflt;
	}

	/**
	 * Return a string that will give this column a default value in PHP
	 * @return		 string
	 */
	public function getDefaultValueString()
	{
		$defaultValue = $this->getDefaultValue();
		if ($defaultValue !== null) {
			if ($this->isNumericType()) {
				$dflt = (float) $defaultValue->getValue();
			} elseif ($this->isTextType() || $this->getDefaultValue()->isExpression()) {
				$dflt = "'" . str_replace("'", "\'", $defaultValue->getValue()) . "'";
			} elseif ($this->getType() == PropelTypes::BOOLEAN) {
				$dflt = $this->booleanValue($defaultValue->getValue()) ? 'true' : 'false';
			} else {
				$dflt = "'" . $defaultValue->getValue() . "'";
			}
		} else {
			$dflt = "null";
		}
		return $dflt;
	}
	
	/**
	 * Set a string that will give this column a default value.
	 */
	public function setDefaultValue($def)
	{
		$this->domain->setDefaultValue($def);
	}

	/**
	 * Get the default value object for this column.
	 * @return		 ColumnDefaultValue
	 * @see				 Domain::getDefaultValue()
	 */
	public function getDefaultValue()
	{
		return $this->domain->getDefaultValue();
	}

	/**
	 * Get the default value suitable for use in PHP.
	 * @return		 mixed
	 * @see				 Domain::getPhpDefaultValue()
	 */
	public function getPhpDefaultValue()
	{
		return $this->domain->getPhpDefaultValue();
	}

	/**
	 * Returns the class name to do input validation
	 */
	public function getInputValidator()
	{
		return $this->inputValidator;
	}

	/**
	 * Return auto increment/sequence string for the target database. We need to
	 * pass in the props for the target database!
	 */
	public function isAutoIncrement()
	{
		return $this->isAutoIncrement;
	}

	/**
	 * Return auto increment/sequence string for the target database. We need to
	 * pass in the props for the target database!
	 */
	public function isLazyLoad()
	{
		return $this->isLazyLoad;
	}

	/**
	 * Gets the auto-increment string.
	 * @return		 string
	 */
	public function getAutoIncrementString()
	{
		if ($this->isAutoIncrement()&& IDMethod::NATIVE === $this->getTable()->getIdMethod()) {
			return $this->getPlatform()->getAutoIncrement();
		} elseif ($this->isAutoIncrement()) {
			throw new EngineException("You have specified autoIncrement for column '" . $this->name . "' but you have not specified idMethod=\"native\" for table '" . $this->getTable()->getName() . "'.");
		}
		return "";
	}

	/**
	 * Set the auto increment value.
	 * Use isAutoIncrement() to find out if it is set or not.
	 */
	public function setAutoIncrement($value)
	{
		$this->isAutoIncrement = (boolean) $value;
	}

	/**
	 * Set the column type from a string property
	 * (normally a string from an sql input file)
	 *
	 * @deprecated Do not use; this will be removed in next release.
	 */
	public function setTypeFromString($typeName, $size)
	{
		$tn = strtoupper($typeName);
		$this->setType($tn);

		if ($size !== null) {
			$this->size = $size;
		}

		if (strpos($tn, "CHAR") !== false) {
			$this->domain->setType(PropelTypes::VARCHAR);
		} elseif (strpos($tn, "INT") !== false) {
			$this->domain->setType(PropelTypes::INTEGER);
		} elseif (strpos($tn, "FLOAT") !== false) {
			$this->domain->setType(PropelTypes::FLOAT);
		} elseif (strpos($tn, "DATE") !== false) {
			$this->domain->setType(PropelTypes::DATE);
		} elseif (strpos($tn, "TIME") !== false) {
			$this->domain->setType(PropelTypes::TIMESTAMP);
		} else if (strpos($tn, "BINARY") !== false) {
			$this->domain->setType(PropelTypes::LONGVARBINARY);
		} else {
			$this->domain->setType(PropelTypes::VARCHAR);
		}
	}

	/**
	 * Return a string representation of the native PHP type which corresponds
	 * to the propel type of this column. Use in the generation of Base objects.
	 *
	 * @return		 string PHP datatype used by propel.
	 */
	public function getPhpNative()
	{
		return PropelTypes::getPhpNative($this->getType());
	}

	/**
	 * Returns true if the column's PHP native type is an boolean, int, long, float, double, string.
	 * @return		 boolean
	 * @see				 PropelTypes::isPhpPrimitiveType()
	 */
	public function isPhpPrimitiveType()
	{
		return PropelTypes::isPhpPrimitiveType($this->getPhpType());
	}

	/**
	 * Return true if column's PHP native type is an boolean, int, long, float, double.
	 * @return		 boolean
	 * @see				 PropelTypes::isPhpPrimitiveNumericType()
	 */
	public function isPhpPrimitiveNumericType()
	{
		return PropelTypes::isPhpPrimitiveNumericType($this->getPhpType());
	}

	/**
	 * Returns true if the column's PHP native type is a class name.
	 * @return		 boolean
	 * @see				 PropelTypes::isPhpObjectType()
	 */
	public function isPhpObjectType()
	{
		return PropelTypes::isPhpObjectType($this->getPhpType());
	}

	/**
	 * Get the platform/adapter impl.
	 *
	 * @return		 Platform
	 */
	public function getPlatform()
	{
		return $this->getTable()->getDatabase()->getPlatform();
	}

	/**
	 *
	 * @return		 string
	 * @deprecated Use DDLBuilder->getColumnDDL() instead; this will be removed in 1.3
	 */
	public function getSqlString()
	{
		$sb = "";
		$sb .= $this->getPlatform()->quoteIdentifier($this->getName()) . " ";
		$sb .= $this->getDomain()->getSqlType();
		if ($this->getPlatform()->hasSize($this->getDomain()->getSqlType())) {
			$sb .= $this->getDomain()->printSize();
		}
		$sb .= " ";
		$sb .= $this->getDefaultSetting() . " ";
		$sb .= $this->getNotNullString() . " ";
		$sb .= $this->getAutoIncrementString();
		return trim($sb);
	}
	
	public static function generatePhpName($name, $phpNamingMethod = PhpNameGenerator::CONV_METHOD_CLEAN, $namePrefix = null) {
		return NameFactory::generateName(NameFactory::PHP_GENERATOR, array($name, $phpNamingMethod, $namePrefix));
	}
}

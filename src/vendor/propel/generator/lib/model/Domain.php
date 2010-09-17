<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

require_once 'model/XMLElement.php';

/**
 * A class for holding data about a domain used in the schema.
 *
 * @author     Hans Lellelid <hans@xmpl.org> (Propel)
 * @author     Martin Poeschl <mpoeschl@marmot.at> (Torque)
 * @version    $Revision: 1612 $
 * @package    propel.generator.model
 */
class Domain extends XMLElement
{

	/**
	 * @var        string The name of this domain
	 */
	private $name;

	/**
	 * @var        string Description for this domain.
	 */
	private $description;

	/**
	 * @var        int Size
	 */
	private $size;

	/**
	 * @var        int Scale
	 */
	private $scale;

	/**
	 * @var        int Propel type from schema
	 */
	private $propelType;

	/**
	 * @var        string The SQL type to use for this column
	 */
	private $sqlType;

	/**
	 * @var        ColumnDefaultValue A default value
	 */
	private $defaultValue;

	/**
	 * @var        Database
	 */
	private $database;

	/**
	 * Creates a new Domain object.
	 * If this domain needs a name, it must be specified manually.
	 *
	 * @param      string $type Propel type.
	 * @param      string $sqlType SQL type.
	 * @param      string $size
	 * @param      string $scale
	 */
	public function __construct($type = null, $sqlType = null, $size = null, $scale = null)
	{
		$this->propelType = $type;
		$this->sqlType = ($sqlType !== null) ? $sqlType : $type;
		$this->size = $size;
		$this->scale = $scale;
	}

	/**
	 * Copy the values from current object into passed-in Domain.
	 * @param      Domain $domain Domain to copy values into.
	 */
	public function copy(Domain $domain)
	{
		$this->defaultValue = $domain->getDefaultValue();
		$this->description = $domain->getDescription();
		$this->name = $domain->getName();
		$this->scale = $domain->getScale();
		$this->size = $domain->getSize();
		$this->sqlType = $domain->getSqlType();
		$this->propelType = $domain->getType();
	}

	/**
	 * Sets up the Domain object based on the attributes that were passed to loadFromXML().
	 * @see        parent::loadFromXML()
	 */
	protected function setupObject()
	{
		$schemaType = strtoupper($this->getAttribute("type"));
		$this->copy($this->getDatabase()->getPlatform()->getDomainForType($schemaType));

		//Name
		$this->name = $this->getAttribute("name");

		// Default value
		$defval = $this->getAttribute("defaultValue", $this->getAttribute("default"));
		if ($defval !== null) {
			$this->setDefaultValue(new ColumnDefaultValue($defval, ColumnDefaultValue::TYPE_VALUE));
		} elseif ($this->getAttribute("defaultExpr") !== null) {
			$this->setDefaultValue(new ColumnDefaultValue($this->getAttribute("defaultExpr"), ColumnDefaultValue::TYPE_EXPR));
		}

		$this->size = $this->getAttribute("size");
		$this->scale = $this->getAttribute("scale");
		$this->description = $this->getAttribute("description");
	}

	/**
	 * Sets the owning database object (if this domain is being setup via XML).
	 * @param      Database $database
	 */
	public function setDatabase(Database $database)
	{
		$this->database = $database;
	}

	/**
	 * Gets the owning database object (if this domain was setup via XML).
	 * @return     Database
	 */
	public function getDatabase()
	{
		return $this->database;
	}

	/**
	 * @return     string Returns the description.
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * @param      string $description The description to set.
	 */
	public function setDescription($description)
	{
		$this->description = $description;
	}

	/**
	 * @return     string Returns the name.
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param      string $name The name to set.
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * @return     string Returns the scale.
	 */
	public function getScale()
	{
		return $this->scale;
	}

	/**
	 * @param      string $scale The scale to set.
	 */
	public function setScale($scale)
	{
		$this->scale = $scale;
	}

	/**
	 * Replaces the size if the new value is not null.
	 *
	 * @param      string $value The size to set.
	 */
	public function replaceScale($value)
	{
		if ($value !== null) {
			$this->scale = $value;
		}
	}

	/**
	 * @return     int Returns the size.
	 */
	public function getSize()
	{
		return $this->size;
	}

	/**
	 * @param      int $size The size to set.
	 */
	public function setSize($size)
	{
		$this->size = $size;
	}

	/**
	 * Replaces the size if the new value is not null.
	 *
	 * @param      int $value The size to set.
	 */
	public function replaceSize($value)
	{
		if ($value !== null) {
			$this->size = $value;
		}
	}

	/**
	 * @return     string Returns the propelType.
	 */
	public function getType()
	{
		return $this->propelType;
	}

	/**
	 * @param      string $propelType The PropelTypes type to set.
	 */
	public function setType($propelType)
	{
		$this->propelType = $propelType;
	}

	/**
	 * Replaces the type if the new value is not null.
	 *
	 * @param      string $value The tyep to set.
	 */
	public function replaceType($value)
	{
		if ($value !== null) {
			$this->propelType = $value;
		}
	}

	/**
	 * Gets the default value object.
	 * @return     ColumnDefaultValue The default value object for this domain.
	 */
	public function getDefaultValue()
	{
		return $this->defaultValue;
	}

	/**
	 * Gets the default value, type-casted for use in PHP OM.
	 * @return     mixed
	 * @see        getDefaultValue()
	 */
	public function getPhpDefaultValue()
	{
		if ($this->defaultValue === null) {
			return null;
		} else {
			if ($this->defaultValue->isExpression()) {
				throw new EngineException("Cannot get PHP version of default value for default value EXPRESSION.");
			}
			if ($this->propelType === PropelTypes::BOOLEAN || $this->propelType === PropelTypes::BOOLEAN_EMU) {
				return $this->booleanValue($this->defaultValue->getValue());
			} else {
				return $this->defaultValue->getValue();
			}
		}
	}

	/**
	 * @param      ColumnDefaultValue $value The column default value to set.
	 */
	public function setDefaultValue(ColumnDefaultValue $value)
	{
		$this->defaultValue = $value;
	}

	/**
	 * Replaces the default value if the new value is not null.
	 *
	 * @param      ColumnDefaultValue $value The defualt value object
	 */
	public function replaceDefaultValue(ColumnDefaultValue $value = null)
	{
		if ($value !== null) {
			$this->defaultValue = $value;
		}
	}

	/**
	 * @return     string Returns the sqlType.
	 */
	public function getSqlType()
	{
		return $this->sqlType;
	}

	/**
	 * @param      string $sqlType The sqlType to set.
	 */
	public function setSqlType($sqlType)
	{
		$this->sqlType = $sqlType;
	}

	/**
	 * Replaces the SQL type if the new value is not null.
	 * @param      string $sqlType The native SQL type to use for this domain.
	 */
	public function replaceSqlType($sqlType)
	{
		if ($sqlType !== null) {
			$this->sqlType = $sqlType;
		}
	}

	/**
	 * Return the size and scale in brackets for use in an sql schema.
	 *
	 * @return     string Size and scale or an empty String if there are no values
	 *         available.
	 */
	public function printSize()
	{
		if ($this->size !== null && $this->scale !== null)  {
			return '(' . $this->size . ',' . $this->scale . ')';
		} elseif ($this->size !== null) {
			return '(' . $this->size . ')';
		} else {
			return "";
		}
	}

	/**
	 * @see        XMLElement::appendXml(DOMNode)
	 */
	public function appendXml(DOMNode $node)
	{
		$doc = ($node instanceof DOMDocument) ? $node : $node->ownerDocument;

		$domainNode = $node->appendChild($doc->createElement('domain'));
		$domainNode->setAttribute('type', $this->getType());
		$domainNode->setAttribute('name', $this->getName());

		if ($this->sqlType !== $this->getType()) {
			$domainNode->setAttribute('sqlType', $this->sqlType);
		}

		$def = $this->getDefaultValue();
		if ($def) {
			if ($def->isExpression()) {
				$domainNode->setAttribute('defaultExpr', $def->getValue());
			} else {
				$domainNode->setAttribute('defaultValue', $def->getValue());
			}
		}

		if ($this->size) {
			$domainNode->setAttribute('size', $this->size);
		}

		if ($this->scale) {
			$domainNode->setAttribute('scale', $this->scale);
		}

		if ($this->description) {
			$domainNode->setAttribute('description', $this->description);
		}
	}

}

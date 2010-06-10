<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

require_once 'model/Index.php';
require_once 'builder/util/PropelTemplate.php';

/**
 * Information about behaviors of a table.
 *
 * @author     François Zaninotto
 * @version    $Revision: 1784 $
 * @package    propel.generator.model
 */
class Behavior extends XMLElement
{

	protected $table;
	protected $database;
	protected $name;
	protected $parameters = array();
	protected $isTableModified = false;
	protected $isEarly = false;
	protected $dirname;
	
	public function setName($name)
	{
		$this->name = $name;
	}	 
	
	public function getName()
	{
		return $this->name;
	}
	
	public function setTable(Table $table)
	{
		$this->table = $table;
	}

	public function getTable()
	{
		return $this->table;
	}

	public function setDatabase(Database $database)
	{
		$this->database = $database;
	}

	public function getDatabase()
	{
		return $this->database;
	}
	
	/**
	 * Add a parameter
	 * Expects an associative array looking like array('name' => 'foo', 'value' => bar)
	 *
	 * @param     array associative array with name and value keys
	 */
	public function addParameter($attribute)
	{
		$attribute = array_change_key_case($attribute, CASE_LOWER);
		$this->parameters[$attribute['name']] = $attribute['value'];
	}
	
	/**
	 * Overrides the behavior parameters
	 * Expects an associative array looking like array('foo' => 'bar')
	 *
	 * @param     array associative array
	 */
	public function setParameters($parameters)
	{
		$this->parameters = $parameters;
	}
	
	/**
	 * Get the associative array of parameters
	 * @return    array 
	 */
	public function getParameters()
	{
		return $this->parameters;
	}

	public function getParameter($name)
	{
		return $this->parameters[$name];
	}

	/**
	 * This method is automatically called on database behaviors when the database model is finished
	 * Propagate the behavior to the tables of the database
	 * Override this method to have a database behavior do something special
	 */
	public function modifyDatabase()
	{
		foreach ($this->getDatabase()->getTables() as $table)
		{
			$b = clone $this;
			$table->addBehavior($b);
		}
	}
	
	/**
	 * This method is automatically called on table behaviors when the database model is finished
	 * Override it to add columns to the current table
	 */
	public function modifyTable()
	{
	}

	public function setTableModified($bool)
	{
		$this->isTableModified = $bool;
	}

	public function isTableModified()
	{
		return $this->isTableModified;
	}

	public function setEarly($bool = true)
	{
		$this->isEarly = $bool;
	}
	
	public function isEarly()
	{
		return $this->isEarly;
	}
	
	/**
	 * Use Propel's simple templating system to render a PHP file
	 * using variables passed as arguments.
	 *
	 * @param string $filename    The template file name, relative to the behavior's dirname
	 * @param array  $vars        An associative array of argumens to be rendered
	 * @param string $templateDir The name of the template subdirectory
	 *
	 * @return string The rendered template
	 */
	public function renderTemplate($filename, $vars = array(), $templateDir = '/templates/')
	{
		$filePath = $this->getDirname() . $templateDir . $filename;
		if (!file_exists($filePath)) {
			// try with '.php' at the end
			$filePath = $filePath . '.php';
			if (!file_exists($filePath)) {
				throw new InvalidArgumentException(sprintf('Template "%s" not found in "%s" directory',
					$filename,
					$this->getDirname() . $templateDir
				));
			}
		}
		$template = new PropelTemplate();
		$template->setTemplateFile($filePath);
		$vars = array_merge($vars, array('behavior' => $this));
		
		return $template->render($vars);
	}
	
	/**
	 * Returns the current dirname of this behavior (also works for descendants)
	 *
	 * @return string The absolute directory name
	 */
	protected function getDirname()
	{
		if (null === $this->dirname) {
			$r = new ReflectionObject($this);
			$this->dirname = dirname($r->getFileName());
		}
		return $this->dirname;
	}
	
	/**
	 * Retrieve a column object using a name stored in the behavior parameters
	 * Useful for table behaviors
	 * 
	 * @param     string    $param Name of the parameter storing the column name
	 * @return    ColumnMap The column of the table supporting the behavior
	 */
	public function getColumnForParameter($param)
	{
		return $this->getTable()->getColumn($this->getParameter($param));
	}
	
	/**
	 * Sets up the Behavior object based on the attributes that were passed to loadFromXML().
	 * @see       parent::loadFromXML()
	 */
	protected function setupObject()
	{
		$this->name = $this->getAttribute("name");
	}
		
	/**
	 * @see       parent::appendXml(DOMNode)
	 */
	public function appendXml(DOMNode $node)
	{
		$doc = ($node instanceof DOMDocument) ? $node : $node->ownerDocument;

		$bNode = $node->appendChild($doc->createElement('behavior'));
		$bNode->setAttribute('name', $this->getName());

		foreach ($this->parameters as $name => $value) {
			$parameterNode = $bNode->appendChild($doc->createElement('parameter'));
			$parameterNode->setAttribute('name', $name);
			$parameterNode->setAttribute('value', $value);
		}
	}
	
	public function getTableModifier()
	{
		return $this;
	}	 
	
	public function getObjectBuilderModifier()
	{
		return $this;
	}

	public function getQueryBuilderModifier()
	{
		return $this;
	}

	public function getPeerBuilderModifier()
	{
		return $this;
	}
	
	public function getTableMapBuilderModifier()
	{
		return $this;
	}
}

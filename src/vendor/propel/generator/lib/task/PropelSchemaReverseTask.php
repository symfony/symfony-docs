<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

require_once 'phing/tasks/ext/pdo/PDOTask.php';
require_once 'config/GeneratorConfig.php';
require_once 'model/PropelTypes.php';

/**
 * This class generates an XML schema of an existing database from
 * the database metadata.
 *
 * @author     Hans Lellelid <hans@xmpl.org>
 * @version    $Revision: 1716 $
 * @package    propel.generator.task
 */
class PropelSchemaReverseTask extends PDOTask
{

	/**
	 * Zero bit for no validators
	 */
	const VALIDATORS_NONE = 0;

	/**
	 * Bit for maxLength validator
	 */
	const VALIDATORS_MAXLENGTH = 1;

	/**
	 * Bit for maxValue validator
	 */
	const VALIDATORS_MAXVALUE = 2;

	/**
	 * Bit for type validator
	 */
	const VALIDATORS_TYPE = 4;

	/**
	 * Bit for required validator
	 */
	const VALIDATORS_REQUIRED = 8;

	/**
	 * Bit for unique validator
	 */
	const VALIDATORS_UNIQUE = 16;

	/**
	 * Bit for all validators
	 */
	const VALIDATORS_ALL = 255;

	/**
	 * File to contain XML database schema.
	 * @var        PhingFIle
	 */
	protected $xmlSchema;

	/**
	 * DB encoding to use
	 * @var        string
	 */
	protected $dbEncoding = 'iso-8859-1';

	/**
	 * DB schema to use.
	 * @var        string
	 */
	protected $dbSchema;

	/**
	 * The datasource name (used for <database name=""> in schema.xml)
	 *
	 * @var        string
	 */
	protected $databaseName;

	/**
	 * DOM document produced.
	 * @var        DOMDocument
	 */
	protected $doc;

	/**
	 * The document root element.
	 * @var        DOMElement
	 */
	protected $databaseNode;

	/**
	 * Hashtable of columns that have primary keys.
	 * @var        array
	 */
	protected $primaryKeys;

	/**
	 * Whether to use same name for phpName or not.
	 * @var        boolean
	 */
	protected $samePhpName;

	/**
	 * whether to add vendor info or not
	 * @var        boolean
	 */
	protected $addVendorInfo;

	/**
	 * Bitfield to switch on/off which validators will be created.
	 *
	 * @var        int
	 */
	protected $validatorBits = PropelSchemaReverseTask::VALIDATORS_NONE;

	/**
	 * Collect validatorInfos to create validators.
	 *
	 * @var        int
	 */
	protected $validatorInfos;

	/**
	 * An initialized GeneratorConfig object containing the converted Phing props.
	 *
	 * @var        GeneratorConfig
	 */
	private $generatorConfig;

	/**
	 * Maps validator type tokens to bits
	 *
	 * The tokens are used in the propel.addValidators property to define
	 * which validators are to be added
	 *
	 * @var        array
	 */
	static protected $validatorBitMap = array (
		'none' => PropelSchemaReverseTask::VALIDATORS_NONE,
		'maxlength' => PropelSchemaReverseTask::VALIDATORS_MAXLENGTH,
		'maxvalue' => PropelSchemaReverseTask::VALIDATORS_MAXVALUE,
		'type' => PropelSchemaReverseTask::VALIDATORS_TYPE,
		'required' => PropelSchemaReverseTask::VALIDATORS_REQUIRED,
		'unique' => PropelSchemaReverseTask::VALIDATORS_UNIQUE,
		'all' => PropelSchemaReverseTask::VALIDATORS_ALL,
	);

	/**
	 * Defines messages that are added to validators
	 *
	 * @var        array
	 */
	static protected $validatorMessages = array (
		'maxlength' => array (
			'msg' => 'The field %s must be not longer than %s characters.',
			'var' => array('colName', 'value')
	),
		'maxvalue' => array (
			'msg' => 'The field %s must be not greater than %s.',
			'var' => array('colName', 'value')
	),
		'type' => array (
			'msg' => 'The column %s must be an %s value.',
			'var' => array('colName', 'value')
	),
		'required' => array (
			'msg' => 'The field %s is required.',
			'var' => array('colName')
	),
		'unique' => array (
			'msg' => 'This %s already exists in table %s.',
			'var' => array('colName', 'tableName')
	),
	);

	/**
	 * Gets the (optional) schema name to use.
	 *
	 * @return     string
	 */
	public function getDbSchema()
	{
		return $this->dbSchema;
	}

	/**
	 * Sets the name of a database schema to use (optional).
	 *
	 * @param      string $dbSchema
	 */
	public function setDbSchema($dbSchema)
	{
		$this->dbSchema = $dbSchema;
	}

	/**
	 * Gets the database encoding.
	 *
	 * @return     string
	 */
	public function getDbEncoding($v)
	{
		return $this->dbEncoding;
	}

	/**
	 * Sets the database encoding.
	 *
	 * @param      string $v
	 */
	public function setDbEncoding($v)
	{
		$this->dbEncoding = $v;
	}

	/**
	 * Gets the datasource name.
	 *
	 * @return     string
	 */
	public function getDatabaseName()
	{
		return $this->databaseName;
	}

	/**
	 * Sets the datasource name.
	 *
	 * This will be used as the <database name=""> value in the generated schema.xml
	 *
	 * @param      string $v
	 */
	public function setDatabaseName($v)
	{
		$this->databaseName = $v;
	}

	/**
	 * Sets the output name for the XML file.
	 *
	 * @param      PhingFile $v
	 */
	public function setOutputFile(PhingFile $v)
	{
		$this->xmlSchema = $v;
	}

	/**
	 * Set whether to use the column name as phpName without any translation.
	 *
	 * @param      boolean $v
	 */
	public function setSamePhpName($v)
	{
		$this->samePhpName = $v;
	}

	/**
	 * Set whether to add vendor info to the schema.
	 *
	 * @param      boolean $v
	 */
	public function setAddVendorInfo($v)
	{
		$this->addVendorInfo = (boolean) $v;
	}

	/**
	 * Sets set validator bitfield from a comma-separated list of "validator bit" names.
	 *
	 * @param      string $v The comma-separated list of which validators to add.
	 * @return     void
	 */
	public function setAddValidators($v)
	{
		$validKeys = array_keys(self::$validatorBitMap);

		// lowercase input
		$v = strtolower($v);

		$bits = self::VALIDATORS_NONE;

		$exprs = explode(',', $v);
		foreach ($exprs as $expr) {
			$expr = trim($expr);
			if(!empty($expr)) {
  			if (!isset(self::$validatorBitMap[$expr])) {
  				throw new BuildException("Unable to interpret validator in expression ('$v'): " . $expr);
  			}
  			$bits |= self::$validatorBitMap[$expr];
			}
		}

		$this->validatorBits = $bits;
	}

	/**
	 * Checks whether to add validators of specified type or not
	 *
	 * @param      int $type The validator type constant.
	 * @return     boolean
	 */
	protected function isValidatorRequired($type)
	{
		return (($this->validatorBits & $type) === $type);
	}

	/**
	 * Whether to use the column name as phpName without any translation.
	 *
	 * @return     boolean
	 */
	public function isSamePhpName()
	{
		return $this->samePhpName;
	}

	/**
	 * @throws     BuildException
	 */
	public function main()
	{
		if (!$this->getDatabaseName()) {
			throw new BuildException("databaseName attribute is required for schema reverse engineering", $this->getLocation());
		}

		//(not yet supported) $this->log("schema : " . $this->dbSchema);
		//DocumentTypeImpl docType = new DocumentTypeImpl(null, "database", null,
		//	   "http://jakarta.apache.org/turbine/dtd/database.dtd");

		$this->doc = new DOMDocument('1.0', 'utf-8');
		$this->doc->formatOutput = true; // pretty printing

		$this->doc->appendChild($this->doc->createComment("Autogenerated by ".get_class($this)." class."));

		try {

			$database = $this->buildModel();

			if ($this->validatorBits !== self::VALIDATORS_NONE) {
				$this->addValidators($database);
			}

			$database->appendXml($this->doc);

			$this->log("Writing XML to file: " . $this->xmlSchema->getPath());
			$out = new FileWriter($this->xmlSchema);
			$xmlstr = $this->doc->saveXML();
			$out->write($xmlstr);
			$out->close();

		} catch (Exception $e) {
			$this->log("There was an error building XML from metadata: " . $e->getMessage(), Project::MSG_ERR);
		}

		$this->log("Schema reverse engineering finished");
	}

	/**
	 * Gets the GeneratorConfig object for this task or creates it on-demand.
	 * @return     GeneratorConfig
	 */
	protected function getGeneratorConfig()
	{
		if ($this->generatorConfig === null) {
			$this->generatorConfig = new GeneratorConfig();
			$this->generatorConfig->setBuildProperties($this->getProject()->getProperties());
		}
		return $this->generatorConfig;
	}

	/**
	 * Builds the model classes from the database schema.
	 * @return     Database The built-out Database (with all tables, etc.)
	 */
	protected function buildModel()
	{
		$config = $this->getGeneratorConfig();
		$con = $this->getConnection();

		$database = new Database($this->getDatabaseName());
		$database->setPlatform($config->getConfiguredPlatform($con));

		// Some defaults ...
		$database->setDefaultIdMethod(IDMethod::NATIVE);

		$parser = $config->getConfiguredSchemaParser($con);

		$nbTables = $parser->parse($database, $this);
		
		$this->log("Successfully Reverse Engineered " . $nbTables . " tables");

		return $database;
	}

	/**
	 * Adds any requested validators to the data model.
	 *
	 * We will add the following type specific validators:
	 *
	 *      for notNull columns: required validator
	 *      for unique indexes: unique validator
	 * 		for varchar types: maxLength validators (CHAR, VARCHAR, LONGVARCHAR)
	 * 		for numeric types: maxValue validators (BIGINT, SMALLINT, TINYINT, INTEGER, FLOAT, DOUBLE, NUMERIC, DECIMAL, REAL)
	 * 		for integer and timestamp types: notMatch validator with [^\d]+ (BIGINT, SMALLINT, TINYINT, INTEGER, TIMESTAMP)
	 * 		for float types: notMatch validator with [^\d\.]+ (FLOAT, DOUBLE, NUMERIC, DECIMAL, REAL)
	 *
	 * @param      Database $database The Database model.
	 * @return     void
	 * @todo       find out how to evaluate the appropriate size and adjust maxValue rule values appropriate
	 * @todo       find out if float type column values must always notMatch('[^\d\.]+'), i.e. digits and point for any db vendor, language etc.
	 */
	protected function addValidators(Database $database)
	{

		$platform = $this->getGeneratorConfig()->getConfiguredPlatform();

		foreach ($database->getTables() as $table) {

			$set = new PropelSchemaReverse_ValidatorSet();

			foreach ($table->getColumns() as $col) {

				if ($col->isNotNull() && $this->isValidatorRequired(self::VALIDATORS_REQUIRED)) {
					$validator = $set->getValidator($col);
					$validator->addRule($this->getValidatorRule($col, 'required'));
				}

				if (in_array($col->getType(), array(PropelTypes::CHAR, PropelTypes::VARCHAR, PropelTypes::LONGVARCHAR))
						&& $col->getSize() && $this->isValidatorRequired(self::VALIDATORS_MAXLENGTH)) {
					$validator = $set->getValidator($col);
					$validator->addRule($this->getValidatorRule($col, 'maxLength', $col->getSize()));
				}

				if ($col->isNumericType() && $this->isValidatorRequired(self::VALIDATORS_MAXVALUE)) {
					$this->log("WARNING: maxValue validator added for column ".$col->getName().". You will have to adjust the size value manually.", Project::MSG_WARN);
					$validator = $set->getValidator($col);
					$validator->addRule($this->getValidatorRule($col, 'maxValue', 'REPLACEME'));
				}

				if ($col->isPhpPrimitiveType() && $this->isValidatorRequired(self::VALIDATORS_TYPE)) {
					$validator = $set->getValidator($col);
					$validator->addRule($this->getValidatorRule($col, 'type', $col->getPhpType()));
				}

			}

			foreach ($table->getUnices() as $unique) {
				$colnames = $unique->getColumns();
				if (count($colnames) == 1) { // currently 'unique' validator only works w/ single columns.
					$col = $table->getColumn($colnames[0]);
					$validator = $set->getValidator($col);
					$validator->addRule($this->getValidatorRule($col, 'unique'));
				}
			}

			foreach ($set->getValidators() as $validator) {
				$table->addValidator($validator);
			}

		} // foreach table

	}

	/**
	 * Gets validator rule for specified type (string).
	 *
	 * @param      Column $column The column that is being validated.
	 * @param      string $type The type (string) for validator (e.g. 'required').
	 * @param      mixed $value The value for the validator (if applicable)
	 */
	protected function getValidatorRule(Column $column, $type, $value = null)
	{
		$rule = new Rule();
		$rule->setName($type);
		if ($value !== null) {
			$rule->setValue($value);
		}
		$rule->setMessage($this->getRuleMessage($column, $type, $value));
		return $rule;
	}

	/**
	 * Gets the message for a specified rule.
	 *
	 * @param      Column $column
	 * @param      string $type
	 * @param      mixed $value
	 */
	protected function getRuleMessage(Column $column, $type, $value)
	{
		// create message
		$colName = $column->getName();
		$tableName = $column->getTable()->getName();
		$msg = self::$validatorMessages[strtolower($type)];
		$tmp = compact($msg['var']);
		array_unshift($tmp, $msg['msg']);
		$msg = call_user_func_array('sprintf', $tmp);
		return $msg;
	}

}

/**
 * A helper class to store validator sets indexed by column.
 * @package    propel.generator.task
 */
class PropelSchemaReverse_ValidatorSet
{

	/**
	 * Map of column names to validators.
	 *
	 * @var        array Validator[]
	 */
	private $validators = array();

	/**
	 * Gets a single validator for specified column name.
	 * @param      Column $column
	 * @return     Validator
	 */
	public function getValidator(Column $column)
	{
		$key = $column->getName();
		if (!isset($this->validators[$key])) {
			$this->validators[$key] = new Validator();
			$this->validators[$key]->setColumn($column);
		}
		return $this->validators[$key];
	}

	/**
	 * Gets all validators.
	 * @return     array Validator[]
	 */
	public function getValidators()
	{
		return $this->validators;
	}
}

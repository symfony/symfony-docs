<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

//include_once 'phing/tasks/ext/CapsuleTask.php';
require_once 'phing/Task.php';
include_once 'config/GeneratorConfig.php';
include_once 'model/AppData.php';
include_once 'model/Database.php';
include_once 'builder/util/XmlToAppData.php';

/**
 * An abstract base Propel task to perform work related to the XML schema file.
 *
 * The subclasses invoke templates to do the actual writing of the resulting files.
 *
 * @author     Hans Lellelid <hans@xmpl.org> (Propel)
 * @author     Jason van Zyl <jvanzyl@zenplex.com> (Torque)
 * @author     Daniel Rall <dlr@finemaltcoding.com> (Torque)
 * @package    propel.generator.task
 */
abstract class AbstractPropelDataModelTask extends Task
{

	/**
	 * Fileset of XML schemas which represent our data models.
	 * @var        array Fileset[]
	 */
	protected $schemaFilesets = array();

	/**
	 * Data models that we collect. One from each XML schema file.
	 */
	protected $dataModels = array();

	/**
	 * Have datamodels been initialized?
	 * @var        boolean
	 */
	private $dataModelsLoaded = false;

	/**
	 * Map of data model name to database name.
	 * Should probably stick to the convention
	 * of them being the same but I know right now
	 * in a lot of cases they won't be.
	 */
	protected $dataModelDbMap;

	/**
	 * The target database(s) we are generating SQL
	 * for. Right now we can only deal with a single
	 * target, but we will support multiple targets
	 * soon.
	 */
	protected $targetDatabase;

	/**
	 * DB encoding to use for XmlToAppData object
	 */
	protected $dbEncoding = 'iso-8859-1';

	/**
	 * Target PHP package to place the generated files in.
	 */
	protected $targetPackage;

	/**
	 * @var        Mapper
	 */
	protected $mapperElement;

	/**
	 * Destination directory for results of template scripts.
	 * @var        PhingFile
	 */
	protected $outputDirectory;

	/**
	 * Whether to package the datamodels or not
	 * @var        PhingFile
	 */
	protected $packageObjectModel;

	/**
	 * Whether to perform validation (XSD) on the schema.xml file(s).
	 * @var        boolean
	 */
	protected $validate;

	/**
	 * The XSD schema file to use for validation.
	 * @var        PhingFile
	 */
	protected $xsdFile;

	/**
	 * XSL file to use to normalize (or otherwise transform) schema before validation.
	 * @var        PhingFile
	 */
	protected $xslFile;

	/**
	 * Optional database connection url.
	 * @var        string
	 */
	private $url = null;

	/**
	 * Optional database connection user name.
	 * @var        string
	 */
	private $userId = null;

	/**
	 * Optional database connection password.
	 * @var        string
	 */
	private $password = null;

	/**
	 * PDO Connection.
	 * @var        PDO
	 */
	private $conn = false;

	/**
	 * An initialized GeneratorConfig object containing the converted Phing props.
	 *
	 * @var        GeneratorConfig
	 */
	private $generatorConfig;

	/**
	 * Return the data models that have been
	 * processed.
	 *
	 * @return     List data models
	 */
	public function getDataModels()
	{
		if (!$this->dataModelsLoaded) {
			$this->loadDataModels();
		}
		return $this->dataModels;
	}

	/**
	 * Return the data model to database name map.
	 *
	 * @return     Hashtable data model name to database name map.
	 */
	public function getDataModelDbMap()
	{
		if (!$this->dataModelsLoaded) {
			$this->loadDataModels();
		}
		return $this->dataModelDbMap;
	}

	/**
	 * Adds a set of xml schema files (nested fileset attribute).
	 *
	 * @param      set a Set of xml schema files
	 */
	public function addSchemaFileset(Fileset $set)
	{
		$this->schemaFilesets[] = $set;
	}

	/**
	 * Get the current target database.
	 *
	 * @return     String target database(s)
	 */
	public function getTargetDatabase()
	{
		return $this->targetDatabase;
	}

	/**
	 * Set the current target database. (e.g. mysql, oracle, ..)
	 *
	 * @param      v target database(s)
	 */
	public function setTargetDatabase($v)
	{
		$this->targetDatabase = $v;
	}

	/**
	 * Get the current target package.
	 *
	 * @return     string target PHP package.
	 */
	public function getTargetPackage()
	{
		return $this->targetPackage;
	}

	/**
	 * Set the current target package. This is where generated PHP classes will
	 * live.
	 *
	 * @param      string $v target PHP package.
	 */
	public function setTargetPackage($v)
	{
		$this->targetPackage = $v;
	}

	/**
	 * Set the packageObjectModel switch on/off
	 *
	 * @param      string $v The build.property packageObjectModel
	 */
	public function setPackageObjectModel($v)
	{
		$this->packageObjectModel = ($v === '1' ? true : false);
	}

	/**
	 * Set whether to perform validation on the datamodel schema.xml file(s).
	 * @param      boolean $v
	 */
	public function setValidate($v)
	{
		$this->validate = $v;
	}

	/**
	 * Set the XSD schema to use for validation of any datamodel schema.xml file(s).
	 * @param      $v PhingFile
	 */
	public function setXsd(PhingFile $v)
	{
		$this->xsdFile = $v;
	}

	/**
	 * Set the normalization XSLT to use to transform datamodel schema.xml file(s) before validation and parsing.
	 * @param      $v PhingFile
	 */
	public function setXsl(PhingFile $v)
	{
		$this->xslFile = $v;
	}

	/**
	 * [REQUIRED] Set the output directory. It will be
	 * created if it doesn't exist.
	 * @param      PhingFile $outputDirectory
	 * @return     void
	 * @throws     Exception
	 */
	public function setOutputDirectory(PhingFile $outputDirectory) {
		try {
			if (!$outputDirectory->exists()) {
				$this->log("Output directory does not exist, creating: " . $outputDirectory->getPath(),Project::MSG_VERBOSE);
				if (!$outputDirectory->mkdirs()) {
					throw new IOException("Unable to create Ouptut directory: " . $outputDirectory->getAbsolutePath());
				}
			}
			$this->outputDirectory = $outputDirectory->getCanonicalPath();
		} catch (IOException $ioe) {
			throw new BuildException($ioe);
		}
	}

	/**
	 * Set the current target database encoding.
	 *
	 * @param      v target database encoding
	 */
	public function setDbEncoding($v)
	{
		$this->dbEncoding = $v;
	}

	/**
	 * Set the DB connection url.
	 *
	 * @param      string $url connection url
	 */
	public function setUrl($url)
	{
		$this->url = $url;
	}

	/**
	 * Set the user name for the DB connection.
	 *
	 * @param      string $userId database user
	 */
	public function setUserid($userId)
	{
		$this->userId = $userId;
	}

	/**
	 * Set the password for the DB connection.
	 *
	 * @param      string $password database password
	 */
	public function setPassword($password)
	{
		$this->password = $password;
	}

	/**
	 * Get the output directory.
	 * @return     string
	 */
	public function getOutputDirectory() {
		return $this->outputDirectory;
	}

	/**
	 * Nested creator, creates one Mapper for this task.
	 *
	 * @return     Mapper  The created Mapper type object.
	 * @throws     BuildException
	 */
	public function createMapper() {
		if ($this->mapperElement !== null) {
			throw new BuildException("Cannot define more than one mapper.", $this->location);
		}
		$this->mapperElement = new Mapper($this->project);
		return $this->mapperElement;
	}

	/**
	 * Maps the passed in name to a new filename & returns resolved File object.
	 * @param      string $from
	 * @return     PhingFile Resolved File object.
	 * @throws     BuilException    - if no Mapper element se
	 *                          - if unable to map new filename.
	 */
	protected function getMappedFile($from)
	{
		if (!$this->mapperElement) {
			throw new BuildException("This task requires you to use a <mapper/> element to describe how filename changes should be handled.");
		}

		$mapper = $this->mapperElement->getImplementation();
		$mapped = $mapper->main($from);
		if (!$mapped) {
			throw new BuildException("Cannot create new filename based on: " . $from);
		}
		// Mappers always return arrays since it's possible for some mappers to map to multiple names.
		$outFilename = array_shift($mapped);
		$outFile = new PhingFile($this->getOutputDirectory(), $outFilename);
		return $outFile;
	}

	/**
	 * Gets the PDO connection, if URL specified.
	 * @return     PDO Connection to use (for quoting, Platform class, etc.) or NULL if no connection params were specified.
	 */
	public function getConnection()
	{
		if ($this->conn === false) {
			$this->conn = null;
			if ($this->url) {
				$buf = "Using database settings:\n"
					. " URL: " . $this->url . "\n"
					. ($this->userId ? " user: " . $this->userId . "\n" : "")
				. ($this->password ? " password: " . $this->password . "\n" : "");

				$this->log($buf, Project::MSG_VERBOSE);

				// Set user + password to null if they are empty strings
				if (!$this->userId) { $this->userId = null; }
				if (!$this->password) { $this->password = null; }
				try {
					$this->conn = new PDO($this->url, $this->userId, $this->password);
					$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				} catch (PDOException $x) {
					$this->log("Unable to create a PDO connection: " . $x->getMessage(), Project::MSG_WARN);
				}
			}
		}
		return $this->conn;
	}

	/**
	 * Gets all matching XML schema files and loads them into data models for class.
	 * @return     void
	 */
	protected function loadDataModels()
	{
		$ads = array();

		// Get all matched files from schemaFilesets
		foreach ($this->schemaFilesets as $fs) {
			$ds = $fs->getDirectoryScanner($this->project);
			$srcDir = $fs->getDir($this->project);

			$dataModelFiles = $ds->getIncludedFiles();

			$platform = $this->getGeneratorConfig()->getConfiguredPlatform();

			// Make a transaction for each file
			foreach ($dataModelFiles as $dmFilename) {

				$this->log("Processing: ".$dmFilename);
				$xmlFile = new PhingFile($srcDir, $dmFilename);

				$dom = new DomDocument('1.0', 'UTF-8');
				$dom->load($xmlFile->getAbsolutePath());
				
				// modify schema to include any external schemas (and remove the external-schema nodes)
				$this->includeExternalSchemas($dom, $srcDir);
					
				// normalize (or transform) the XML document using XSLT
				if ($this->getGeneratorConfig()->getBuildProperty('schemaTransform') && $this->xslFile) {
					$this->log("Transforming " . $xmlFile->getPath() . " using stylesheet " . $this->xslFile->getPath(), Project::MSG_VERBOSE);
					if (!class_exists('XSLTProcessor')) {
						$this->log("Could not perform XLST transformation. Make sure PHP has been compiled/configured to support XSLT.", Project::MSG_ERR);
					} else {
						// normalize the document using normalizer stylesheet
						$xslDom = new DomDocument('1.0', 'UTF-8');
						$xslDom->load($this->xslFile->getAbsolutePath());
						$xsl = new XsltProcessor();
						$xsl->importStyleSheet($xslDom);
						$dom = $xsl->transformToDoc($dom);
					}
				}

				// validate the XML document using XSD schema
				if ($this->validate && $this->xsdFile) {
					$this->log("Validating XML doc (".$xmlFile->getPath().") using schema file " . $this->xsdFile->getPath(), Project::MSG_VERBOSE);
					if (!$dom->schemaValidate($this->xsdFile->getAbsolutePath())) {
						throw new EngineException("XML schema file (".$xmlFile->getPath().") does not validate. See warnings above for reasons validation failed (make sure error_reporting is set to show E_WARNING if you don't see any).", $this->getLocation());
					}
				}
				
				$xmlParser = new XmlToAppData($platform, $this->getTargetPackage(), $this->dbEncoding);
				$ad = $xmlParser->parseString($dom->saveXML(), $xmlFile->getAbsolutePath());
		
				$ad->setName($dmFilename);
				$ads[] = $ad;
			}
		}

		if (empty($ads)) {
			throw new BuildException("No schema files were found (matching your schema fileset definition).");
		}

		foreach ($ads as $ad) {
			// map schema filename with database name
			$this->dataModelDbMap[$ad->getName()] = $ad->getDatabase(null, false)->getName();
		}
		
		if (count($ads)>1 && $this->packageObjectModel) {
			$ad = $this->joinDataModels($ads);
			$this->dataModels = array($ad);
		} else {
			$this->dataModels = $ads;
		}
		
		foreach ($this->dataModels as &$ad) {
			$ad->doFinalInitialization();
		}
			
		$this->dataModelsLoaded = true;
	}

	/**
	 * Replaces all external-schema nodes with the content of xml schema that node refers to
	 *
	 * Recurses to include any external schema referenced from in an included xml (and deeper)
	 * Note: this function very much assumes at least a reasonable XML schema, maybe it'll proof
	 * users don't have those and adding some more informative exceptions would be better
	 *
	 * @param      DomDocument $dom
	 * @param      string $srcDir
	 * @return     void (objects, DomDocument, are references by default in PHP 5, so returning it is useless)
	 **/
	protected function includeExternalSchemas(DomDocument $dom, $srcDir) {
		$databaseNode = $dom->getElementsByTagName("database")->item(0);
		$externalSchemaNodes = $dom->getElementsByTagName("external-schema");
		$fs = FileSystem::getFileSystem();
		$nbIncludedSchemas = 0;
		while ($externalSchema = $externalSchemaNodes->item(0)) {
			$include = $externalSchema->getAttribute("filename");
			$this->log("Processing external schema: ".$include);
			$externalSchema->parentNode->removeChild($externalSchema);
			if ($fs->prefixLength($include) != 0) {
				$externalSchemaFile = new PhingFile($include);
			} else {
				$externalSchemaFile = new PhingFile($srcDir, $include);
			}
			$externalSchemaDom = new DomDocument('1.0', 'UTF-8');
			$externalSchemaDom->load($externalSchemaFile->getAbsolutePath());
			// The external schema may have external schemas of its own ; recurse
			$this->includeExternalSchemas($externalSchemaDom, $srcDir);
			foreach ($externalSchemaDom->getElementsByTagName("table") as $tableNode) { // see xsd, datatase may only have table or external-schema, the latter was just deleted so this should cover everything
				$databaseNode->appendChild($dom->importNode($tableNode, true));
			}
			$nbIncludedSchemas++;
		}
		return $nbIncludedSchemas;
	}
	
	/**
	 * Joins the datamodels collected from schema.xml files into one big datamodel.
	 *  We need to join the datamodels in this case to allow for foreign keys 
	 * that point to tables in different packages.
	 *
	 * @param      array[AppData] $ads The datamodels to join
	 * @return     AppData        The single datamodel with all other datamodels joined in
	 */
	protected function joinDataModels($ads)
	{
		$mainAppData = null;
		foreach ($ads as $appData) {
			if (null === $mainAppData) {
				$mainAppData = $appData;
				$appData->setName('JoinedDataModel');
				continue;
			}
			// merge subsequent schemas to the first one
			foreach ($appData->getDatabases(false) as $addDb) {
				$addDbName = $addDb->getName();
				if ($mainAppData->hasDatabase($addDbName)) {
					$db = $mainAppData->getDatabase($addDbName, false);
					// join tables
					foreach ($addDb->getTables() as $addTable) {
						if ($db->getTable($addTable->getName())) {
							throw new BuildException('Duplicate table found: ' . $addDbName . '.');
						}
						$db->addTable($addTable);
					}
					// join database behaviors
					foreach ($addDb->getBehaviors() as $addBehavior) {
						if (!$db->hasBehavior($addBehavior->getName())) {
							$db->addBehavior($addBehavior);
						}
					}
				} else {
					$mainAppData->addDatabase($addDb);
				}
			}
		}
		return $mainAppData;
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
	 * Checks this class against Basic requrements of any propel datamodel task.
	 *
	 * @throws     BuildException 	- if schema fileset was not defined
	 * 							- if no output directory was specified
	 */
	protected function validate()
	{
		if (empty($this->schemaFilesets)) {
			throw new BuildException("You must specify a fileset of XML schemas.", $this->getLocation());
		}

		// Make sure the output directory is set.
		if ($this->outputDirectory === null) {
			throw new BuildException("The output directory needs to be defined!", $this->getLocation());
		}

		if ($this->validate) {
			if (!$this->xsdFile) {
				throw new BuildException("'validate' set to TRUE, but no XSD specified (use 'xsd' attribute).", $this->getLocation());
			}
		}

	}

}

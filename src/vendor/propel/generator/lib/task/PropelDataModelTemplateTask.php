<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

require_once 'task/AbstractPropelDataModelTask.php';
include_once 'model/AppData.php';
include_once 'model/Database.php';
include_once 'builder/util/XmlToAppData.php';

/**
 * A generic class that simply loads the data model and parses a control template.
 *
 * This class exists largely for compatibility with early Propel where this was
 * a CapsuleTask subclass.  This class also makes it easy to quickly add some custom
 * datamodel-based transformations (by allowing you to put the logic in the templates).
 *
 * @author     Hans Lellelid <hans@xmpl.org>
 * @package    propel.generator.task
 * @version    $Revision: 1612 $
 */
class PropelDataModelTemplateTask extends AbstractPropelDataModelTask
{

	/**
	 * This is the file where the generated text
	 * will be placed.
	 * @var        string
	 */
	protected $outputFile;

	/**
	 * Path where Capsule looks for templates.
	 * @var        PhingFile
	 */
	protected $templatePath;

	/**
	 * This is the control template that governs the output.
	 * It may or may not invoke the services of worker
	 * templates.
	 * @var        string
	 */
	protected $controlTemplate;

	/**
	 * [REQUIRED] Set the output file for the
	 * generation process.
	 * @param      string $outputFile (TODO: change this to File)
	 * @return     void
	 */
	public function setOutputFile($outputFile) {
		$this->outputFile = $outputFile;
	}

	/**
	 * Get the output file for the
	 * generation process.
	 * @return     string
	 */
	public function getOutputFile() {
		return $this->outputFile;
	}

	/**
	 * [REQUIRED] Set the control template for the
	 * generating process.
	 * @param      string $controlTemplate
	 * @return     void
	 */
	public function setControlTemplate ($controlTemplate) {
		$this->controlTemplate = $controlTemplate;
	}

	/**
	 * Get the control template for the
	 * generating process.
	 * @return     string
	 */
	public function getControlTemplate() {
		return $this->controlTemplate;
	}

	/**
	 * [REQUIRED] Set the path where Capsule will look
	 * for templates using the file template
	 * loader.
	 * @return     void
	 * @throws     Exception
	 */
	public function setTemplatePath($templatePath) {
		$resolvedPath = "";
		$tok = strtok($templatePath, ",");
		while ( $tok ) {
			// resolve relative path from basedir and leave
			// absolute path untouched.
			$fullPath = $this->project->resolveFile($tok);
			$cpath = $fullPath->getCanonicalPath();
			if ($cpath === false) {
				$this->log("Template directory does not exist: " . $fullPath->getAbsolutePath());
			} else {
				$resolvedPath .= $cpath;
			}
			$tok = strtok(",");
			if ( $tok ) {
				$resolvedPath .= ",";
			}
		}
		$this->templatePath = $resolvedPath;
	 }

	/**
	 * Get the path where Velocity will look
	 * for templates using the file template
	 * loader.
	 * @return     string
	 */
	public function getTemplatePath() {
		return $this->templatePath;
	}

	/**
	 * Creates a new Capsule context with some basic properties set.
	 * (Capsule is a simple PHP encapsulation system -- aka a php "template" class.)
	 * @return     Capsule
	 */
	protected function createContext() {

		$context = new Capsule();

		// Make sure the output directory exists, if it doesn't
		// then create it.
		$outputDir = new PhingFile($this->outputDirectory);
		if (!$outputDir->exists()) {
			$this->log("Output directory does not exist, creating: " . $outputDir->getAbsolutePath());
			$outputDir->mkdirs();
		}

		// Place our set of data models into the context along
		// with the names of the databases as a convenience for now.
		$context->put("targetDatabase", $this->getTargetDatabase());
		$context->put("targetPackage", $this->getTargetPackage());
		$context->put("now", strftime("%c"));

		$this->log("Target database type: " . $this->getTargetDatabase());
		$this->log("Target package: " . $this->getTargetPackage());
		$this->log("Using template path: " . $this->templatePath);
		$this->log("Output directory: " . $this->getOutputDirectory());

		$context->setTemplatePath($this->templatePath);
		$context->setOutputDirectory($this->outputDirectory);

		$this->populateContextProperties($context);

		return $context;
	}

	/**
	 * Adds the propel build properties to the passed Capsule context.
	 *
	 * @param      Capsule $context
	 * @see        GeneratorConfig::getBuildProperties()
	 */
	public function populateContextProperties(Capsule $context)
	{
		foreach ($this->getGeneratorConfig()->getBuildProperties() as $key => $propValue) {
			$this->log('Adding property ${' . $key . '} to context', Project::MSG_DEBUG);
			$context->put($key, $propValue);
		}
	}

	/**
	 * Performs validation for single-file mode.
	 * @throws     BuildException - if there are any validation errors
	 */
	protected function singleFileValidate()
	{
		parent::validate();

		// Make sure the control template is set.
		if ($this->controlTemplate === null) {
			throw new BuildException("The control template needs to be defined!");
		}
		// Make sure there is an output file.
		if ($this->outputFile === null) {
			throw new BuildException("The output file needs to be defined!");
		}

	}

	/**
	 * Creates Capsule context and parses control template.
	 * @return     void
	 */
	public function main()
	{
		$this->singleFileValidate();
		$context = $this->createContext();

		$context->put("dataModels", $this->getDataModels());

		$path = $this->outputDirectory . DIRECTORY_SEPARATOR . $this->outputFile;
		$this->log("Generating to file " . $path);

		try {
			$this->log("Parsing control template: " . $this->controlTemplate);
			$context->parse($this->controlTemplate, $path);
		} catch (Exception $ioe) {
			throw new BuildException("Cannot write parsed template: ". $ioe->getMessage());
		}
	}
}

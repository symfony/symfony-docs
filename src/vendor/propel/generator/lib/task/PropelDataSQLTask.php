<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

require_once 'model/AppData.php';
require_once 'model/Database.php';
require_once 'builder/util/XmlToAppData.php';
require_once 'builder/util/XmlToDataSQL.php';

/**
 * Task that transforms XML datadump files into files containing SQL INSERT statements.
 *
 * @author     Hans Lellelid <hans@xmpl.org> (Propel)
 * @author     Jason van Zyl  <jvanzyl@periapt.com> (Torque)
 * @author     John McNally  <jmcnally@collab.net> (Torque)
 * @author     Fedor Karpelevitch  <fedor.karpelevitch@home.com> (Torque)
 * @version    $Revision: 1612 $
 * @package    propel.generator.task
 */
class PropelDataSQLTask extends AbstractPropelDataModelTask
{

	/**
	 * Properties file that maps an SQL file to a particular database.
	 * @var        PhingFile
	 */
	private $sqldbmap;

	/**
	 * Properties file that maps a data XML file to a particular database.
	 * @var        PhingFile
	 */
	private $datadbmap;

	/**
	 * The base directory in which to find data XML files.
	 * @var        PhingFile
	 */
	private $srcDir;

	/**
	 * Set the file that maps between SQL files and databases.
	 *
	 * @param      PhingFile $sqldbmap the sql -> db map.
	 * @return     void
	 */
	public function setSqlDbMap(PhingFile $sqldbmap)
	{
		$this->sqldbmap = $sqldbmap;
	}

	/**
	 * Get the file that maps between SQL files and databases.
	 *
	 * @return     PhingFile sqldbmap.
	 */
	public function getSqlDbMap()
	{
		return $this->sqldbmap;
	}

	/**
	 * Set the file that maps between data XML files and databases.
	 *
	 * @param      PhingFile $sqldbmap the db map
	 * @return     void
	 */
	public function setDataDbMap(PhingFile $datadbmap)
	{
		$this->datadbmap = $datadbmap;
	}

	/**
	 * Get the file that maps between data XML files and databases.
	 *
	 * @return     PhingFile $datadbmap.
	 */
	public function getDataDbMap()
	{
		return $this->datadbmap;
	}

	/**
	 * Set the src directory for the data xml files listed in the datadbmap file.
	 * @param      PhingFile $srcDir data xml source directory
	 */
	public function setSrcDir(PhingFile $srcDir)
	{
		$this->srcDir = $srcDir;
	}

	/**
	 * Get the src directory for the data xml files listed in the datadbmap file.
	 *
	 * @return     PhingFile data xml source directory
	 */
	public function getSrcDir()
	{
		return $this->srcDir;
	}

	/**
	 * Search through all data models looking for matching database.
	 * @return     Database or NULL if none found.
	 */
	private function getDatabase($name)
	{
		foreach ($this->getDataModels() as $dm) {
			foreach ($dm->getDatabases() as $db) {
				if ($db->getName() == $name) {
					return $db;
				}
			}
		}
	}

	/**
	 * Main method parses the XML files and creates SQL files.
	 *
	 * @return     void
	 * @throws     Exception If there is an error parsing the data xml.
	 */
	public function main()
	{
		$this->validate();

		$targetDatabase = $this->getTargetDatabase();

		$platform = $this->getGeneratorConfig()->getConfiguredPlatform();

		// Load the Data XML -> DB Name properties
		$map = new Properties();
		try {
			$map->load($this->getDataDbMap());
		} catch (IOException $ioe) {
			throw new BuildException("Cannot open and process the datadbmap!", $ioe);
		}

		// Parse each file in the data -> db map
		foreach ($map->keys() as $dataXMLFilename) {

			$dataXMLFile = new PhingFile($this->srcDir, $dataXMLFilename);

			// if file exists then proceed
			if ($dataXMLFile->exists()) {

				$dbname = $map->get($dataXMLFilename);

				$db = $this->getDatabase($dbname);

				if (!$db) {
					throw new BuildException("Cannot find instantiated Database for name '$dbname' from datadbmap file.");
				}

				$db->setPlatform($platform);

				$outFile = $this->getMappedFile($dataXMLFilename);
				$sqlWriter = new FileWriter($outFile);

				$this->log("Creating SQL from XML data dump file: " . $dataXMLFile->getAbsolutePath());

				try {
					$dataXmlParser = new XmlToDataSQL($db, $this->getGeneratorConfig(), $this->dbEncoding);
					$dataXmlParser->transform($dataXMLFile, $sqlWriter);
				} catch (Exception $e) {
					throw new BuildException("Exception parsing data XML: " . $e->getMessage(), $x);
				}

				// Place the generated SQL file(s)
				$p = new Properties();
				if ($this->getSqlDbMap()->exists()) {
					$p->load($this->getSqlDbMap());
				}

				$p->setProperty($outFile->getName(), $db->getName());
				$p->store($this->getSqlDbMap(), "Sqlfile -> Database map");

			} else {
				$this->log("File '" . $dataXMLFile->getAbsolutePath()
						. "' in datadbmap does not exist, so skipping it.", Project::MSG_WARN);
			}

		} // foreach data xml file

	} // main()

}

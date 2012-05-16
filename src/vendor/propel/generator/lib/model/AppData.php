<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

require_once 'exception/EngineException.php';
require_once 'model/Database.php';

/**
 * A class for holding application data structures.
 *
 * @author     Hans Lellelid <hans@xmpl.org> (Propel)
 * @author     Leon Messerschmidt <leon@opticode.co.za> (Torque)
 * @author     John McNally <jmcnally@collab.net> (Torque)
 * @author     Daniel Rall <dlr@finemaltcoding.com> (Torque)
 * @version    $Revision: 1640 $
 * @package    propel.generator.model
 */
class AppData
{

	/**
	 * The list of databases for this application.
	 * @var        array Database[]
	 */
	private $dbList = array();

	/**
	 * The platform class for our database(s).
	 * @var        string
	 */
	private $platform;

	/**
	 * Name of the database. Only one database definition
	 * is allowed in one XML descriptor.
	 */
	private $name;

	/**
	 * Flag to ensure that initialization is performed only once.
	 * @var        boolean
	 */
	private $isInitialized = false;

	/**
	 * Creates a new instance for the specified database type.
	 *
	 * @param      Platform $platform The platform object to use for any databases added to this application model.
	 */
	public function __construct(Platform $platform)
	{
		$this->platform = $platform;
	}
  
	/**
	 * Gets the platform object to use for any databases added to this application model. 
	 *
	 * @return Platform
	 */
	public function getPlatform()
	{
	  return $this->platform;
	}
	
	/**
	 * Set the name of the database.
	 *
	 * @param      name of the database.
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * Get the name of the database.
	 *
	 * @return     String name
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Get the short name of the database (without the '-schema' postfix).
	 *
	 * @return     String name
	 */
	public function getShortName()
	{
		return str_replace("-schema", "", $this->name);
	}

	/**
	 * Return an array of all databases
	 *
	 * @return     Array of Database objects
	 */
	public function getDatabases($doFinalInit = true)
	{
		// this is temporary until we'll have a clean solution
		// for packaging datamodels/requiring schemas
		if ($doFinalInit) {
			$this->doFinalInitialization();
		}
		return $this->dbList;
	}

	/**
	 * Returns whether this application has multiple databases.
	 *
	 * @return     boolean True if the application has multiple databases
	 */
	public function hasMultipleDatabases()
	{
		return (count($this->dbList) > 1);
	}

	/**
	 * Return the database with the specified name.
	 *
	 * @param      name database name
	 * @return     A Database object.  If it does not exist it returns null
	 */
	public function getDatabase($name = null, $doFinalInit = true)
	{
		// this is temporary until we'll have a clean solution
		// for packaging datamodels/requiring schemas
		if ($doFinalInit) {
			$this->doFinalInitialization();
		}

		if ($name === null) {
			return $this->dbList[0];
		}

		for ($i=0,$size=count($this->dbList); $i < $size; $i++) {
			$db = $this->dbList[$i];
			if ($db->getName() === $name) {
				return $db;
			}
		}
		return null;
	}
	
	/**
	 * Checks whether a database with the specified nam exists in this AppData
	 *
	 * @param      name database name
	 * @return     boolean
	 */
	public function hasDatabase($name)
	{
		foreach ($this->dbList as $db) {
			if ($db->getName() === $name) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Add a database to the list and sets the AppData property to this
	 * AppData
	 *
	 * @param      db the database to add
	 */
	public function addDatabase($db)
	{
		if ($db instanceof Database) {
			$db->setAppData($this);
			if ($db->getPlatform() === null) {
				$db->setPlatform($this->platform);
			}
			$this->dbList[] = $db;
			return $db;
		} else {
			// XML attributes array / hash
			$d = new Database();
			$d->setAppData($this);
			if ($d->getPlatform() === null) {
				$d->setPlatform($this->platform);
			}
			$d->loadFromXML($db);
			return $this->addDatabase($d); // calls self w/ different param type
		}

	}

	public function doFinalInitialization()
	{
		if (!$this->isInitialized) {
			for ($i=0, $size=count($this->dbList); $i < $size; $i++) {
				$this->dbList[$i]->doFinalInitialization();
			}
			$this->isInitialized = true;
		}
	}

	/**
	 * Creats a string representation of this AppData.
	 * The representation is given in xml format.
	 *
	 * @return     string Representation in xml format
	 */
	public function toString()
	{
		$result = "<app-data>\n";
		for ($i=0,$size=count($this->dbList); $i < $size; $i++) {
			$result .= $this->dbList[$i]->toString();
		}
		$result .= "</app-data>";
		return $result;
	}
}

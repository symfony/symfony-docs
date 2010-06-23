<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * Propel's main resource pool and initialization & configuration class.
 *
 * This static class is used to handle Propel initialization and to maintain all of the
 * open database connections and instantiated database maps.
 *
 * @author     Hans Lellelid <hans@xmpl.rg> (Propel)
 * @author     Daniel Rall <dlr@finemaltcoding.com> (Torque)
 * @author     Magnús Þór Torfason <magnus@handtolvur.is> (Torque)
 * @author     Jason van Zyl <jvanzyl@apache.org> (Torque)
 * @author     Rafal Krzewski <Rafal.Krzewski@e-point.pl> (Torque)
 * @author     Martin Poeschl <mpoeschl@marmot.at> (Torque)
 * @author     Henning P. Schmiedehausen <hps@intermeta.de> (Torque)
 * @author     Kurt Schrader <kschrader@karmalab.org> (Torque)
 * @version    $Revision: 1813 $
 * @package    propel.runtime
 */
class Propel
{
	/**
	 * The Propel version.
	 */
	const VERSION = '1.5.3-dev';
	
	/**
	 * A constant for <code>default</code>.
	 */
	const DEFAULT_NAME = "default";

	/**
	 * A constant defining 'System is unusuable' logging level
	 */
	const LOG_EMERG = 0;

	/**
	 * A constant defining 'Immediate action required' logging level
	 */
	const LOG_ALERT = 1;

	/**
	 * A constant defining 'Critical conditions' logging level
	 */
	const LOG_CRIT = 2;

	/**
	 * A constant defining 'Error conditions' logging level
	 */
	const LOG_ERR = 3;

	/**
	 * A constant defining 'Warning conditions' logging level
	 */
	const LOG_WARNING = 4;

	/**
	 * A constant defining 'Normal but significant' logging level
	 */
	const LOG_NOTICE = 5;

	/**
	 * A constant defining 'Informational' logging level
	 */
	const LOG_INFO = 6;

	/**
	 * A constant defining 'Debug-level messages' logging level
	 */
	const LOG_DEBUG = 7;

	/**
	 * The class name for a PDO object.
	 */
	const CLASS_PDO = 'PDO';

	/**
	 * The class name for a PropelPDO object.
	 */
	const CLASS_PROPEL_PDO = 'PropelPDO';

	/**
	 * The class name for a DebugPDO object.
	 */
	const CLASS_DEBUG_PDO = 'DebugPDO';

	/**
	 * Constant used to request a READ connection (applies to replication).
	 */
	const CONNECTION_READ = 'read';

	/**
	 * Constant used to request a WRITE connection (applies to replication).
	 */
	const CONNECTION_WRITE = 'write';

	/**
	 * @var        string The db name that is specified as the default in the property file
	 */
	private static $defaultDBName;

	/**
	 * @var        array The global cache of database maps
	 */
	private static $dbMaps = array();

	/**
	 * @var        array The cache of DB adapter keys
	 */
	private static $adapterMap = array();

	/**
	 * @var        array Cache of established connections (to eliminate overhead).
	 */
	private static $connectionMap = array();

	/**
	 * @var        PropelConfiguration Propel-specific configuration.
	 */
	private static $configuration;

	/**
	 * @var        bool flag to set to true once this class has been initialized
	 */
	private static $isInit = false;

	/**
	 * @var        Log optional logger
	 */
	private static $logger = null;

	/**
	 * @var        string The name of the database mapper class
	 */
	private static $databaseMapClass = 'DatabaseMap';

	/**
	 * @var        bool Whether the object instance pooling is enabled
	 */
	private static $instancePoolingEnabled = true;

	/**
	 * @var        bool For replication, whether to force the use of master connection.
	 */
	private static $forceMasterConnection = false;

	/**
	 * @var        string Base directory to use for autoloading. Initialized in self::initBaseDir()
	 */
  protected static $baseDir;
  
	/**
	 * @var        array A map of class names and their file paths for autoloading
	 */
	protected static $autoloadMap = array(

		'DBAdapter'           => 'adapter/DBAdapter.php',
		'DBMSSQL'             => 'adapter/DBMSSQL.php',
		'MssqlPropelPDO'      => 'adapter/MSSQL/MssqlPropelPDO.php',
		'MssqlDebugPDO'       => 'adapter/MSSQL/MssqlDebugPDO.php',	
		'MssqlDateTime'       => 'adapter/MSSQL/MssqlDateTime.class.php',
		'DBMySQL'             => 'adapter/DBMySQL.php',
		'DBMySQLi'            => 'adapter/DBMySQLi.php',
		'DBNone'              => 'adapter/DBNone.php',
		'DBOracle'            => 'adapter/DBOracle.php',
		'DBPostgres'          => 'adapter/DBPostgres.php',
		'DBSQLite'            => 'adapter/DBSQLite.php',
		'DBSybase'            => 'adapter/DBSybase.php',

		'PropelArrayCollection' => 'collection/PropelArrayCollection.php',
		'PropelCollection'    => 'collection/PropelCollection.php',
		'PropelObjectCollection' => 'collection/PropelObjectCollection.php',
		'PropelOnDemandCollection' => 'collection/PropelOnDemandCollection.php',
		'PropelOnDemandIterator' => 'collection/PropelOnDemandIterator.php',

		'PropelConfiguration' => 'config/PropelConfiguration.php',
		'PropelConfigurationIterator' => 'config/PropelConfigurationIterator.php',

		'PropelPDO'           => 'connection/PropelPDO.php',
		'DebugPDO'            => 'connection/DebugPDO.php',
		'DebugPDOStatement'   => 'connection/DebugPDOStatement.php',

		'PropelException'     => 'exception/PropelException.php',
		
		'ModelWith'           => 'formatter/ModelWith.php',
		'PropelArrayFormatter' => 'formatter/PropelArrayFormatter.php',
		'PropelFormatter'     => 'formatter/PropelFormatter.php',
		'PropelObjectFormatter' => 'formatter/PropelObjectFormatter.php',
		'PropelOnDemandFormatter' => 'formatter/PropelOnDemandFormatter.php',
		'PropelStatementFormatter' => 'formatter/PropelStatementFormatter.php',
		
		'BasicLogger'         => 'logger/BasicLogger.php',
		'MojaviLogAdapter'    => 'logger/MojaviLogAdapter.php',

		'ColumnMap'           => 'map/ColumnMap.php',
		'DatabaseMap'         => 'map/DatabaseMap.php',
		'TableMap'            => 'map/TableMap.php',
		'RelationMap'         => 'map/RelationMap.php',
		'ValidatorMap'        => 'map/ValidatorMap.php',

		'BaseObject'          => 'om/BaseObject.php',
		'NodeObject'          => 'om/NodeObject.php',
		'Persistent'          => 'om/Persistent.php',
		'PreOrderNodeIterator' => 'om/PreOrderNodeIterator.php',
		'NestedSetPreOrderNodeIterator' => 'om/NestedSetPreOrderNodeIterator.php',
		'NestedSetRecursiveIterator' => 'om/NestedSetRecursiveIterator.php',

		'Criteria'            => 'query/Criteria.php',
		'Criterion'           => 'query/Criterion.php',
		'CriterionIterator'   => 'query/CriterionIterator.php',
		'Join'                => 'query/Join.php',
		'ModelCriteria'       => 'query/ModelCriteria.php',
		'ModelCriterion'      => 'query/ModelCriterion.php',
		'ModelJoin'           => 'query/ModelJoin.php',
		'PropelQuery'         => 'query/PropelQuery.php',

		'BasePeer'            => 'util/BasePeer.php',
		'NodePeer'            => 'util/NodePeer.php',
		'PeerInfo'            => 'util/PeerInfo.php',
		'PropelAutoloader'    => 'util/PropelAutoloader.php',
		'PropelColumnTypes'   => 'util/PropelColumnTypes.php',
		'PropelConditionalProxy' => 'util/PropelConditionalProxy.php',
		'PropelModelPager'    => 'util/PropelModelPager.php',
		'PropelPager'         => 'util/PropelPager.php',
		'PropelDateTime'      => 'util/PropelDateTime.php',

		'BasicValidator'      => 'validator/BasicValidator.php',
		'MatchValidator'      => 'validator/MatchValidator.php',
		'MaxLengthValidator'  => 'validator/MaxLengthValidator.php',
		'MaxValueValidator'   => 'validator/MaxValueValidator.php',
		'MinLengthValidator'  => 'validator/MinLengthValidator.php',
		'MinValueValidator'   => 'validator/MinValueValidator.php',
		'NotMatchValidator'   => 'validator/NotMatchValidator.php',
		'RequiredValidator'   => 'validator/RequiredValidator.php',
		'UniqueValidator'     => 'validator/UniqueValidator.php',
		'ValidValuesValidator' => 'validator/ValidValuesValidator.php',
		'ValidationFailed'    => 'validator/ValidationFailed.php',
	);

	/**
	 * Initializes Propel
	 *
	 * @throws     PropelException Any exceptions caught during processing will be
	 *                             rethrown wrapped into a PropelException.
	 */
	public static function initialize()
	{
		if (self::$configuration === null) {
			throw new PropelException("Propel cannot be initialized without a valid configuration. Please check the log files for further details.");
		}

		self::configureLogging();

		// reset the connection map (this should enable runtime changes of connection params)
		self::$connectionMap = array();
		
		if (isset(self::$configuration['classmap']) && is_array(self::$configuration['classmap'])) {
		  PropelAutoloader::getInstance()->addClassPaths(self::$configuration['classmap']);
		  PropelAutoloader::getInstance()->register();
	  }
		
		self::$isInit = true;
	}

	/**
	 * Configure Propel a PHP (array) config file.
	 *
	 * @param      string Path (absolute or relative to include_path) to config file.
	 *
	 * @throws     PropelException If configuration file cannot be opened.
	 *                             (E_WARNING probably will also be raised by PHP)
	 */
	public static function configure($configFile)
	{
		$configuration = include($configFile);
		if ($configuration === false) {
			throw new PropelException("Unable to open configuration file: " . var_export($configFile, true));
		}
		self::setConfiguration($configuration);
	}

	/**
	 * Configure the logging system, if config is specified in the runtime configuration.
	 */
	protected static function configureLogging()
	{
		if (self::$logger === null) {
			if (isset(self::$configuration['log']) && is_array(self::$configuration['log']) && count(self::$configuration['log'])) {
				include_once 'Log.php'; // PEAR Log class
				$c = self::$configuration['log'];
				$type = isset($c['type']) ? $c['type'] : 'file';
				$name = isset($c['name']) ? $c['name'] : './propel.log';
				$ident = isset($c['ident']) ? $c['ident'] : 'propel';
				$conf = isset($c['conf']) ? $c['conf'] : array();
				$level = isset($c['level']) ? $c['level'] : PEAR_LOG_DEBUG;
				self::$logger = Log::singleton($type, $name, $ident, $conf, $level);
			} // if isset()
		}
	}

	/**
	 * Initialization of Propel a PHP (array) configuration file.
	 *
	 * @param      string $c The Propel configuration file path.
	 *
	 * @throws     PropelException Any exceptions caught during processing will be
	 *                             rethrown wrapped into a PropelException.
	 */
	public static function init($c)
	{
		self::configure($c);
		self::initialize();
	}

	/**
	 * Determine whether Propel has already been initialized.
	 *
	 * @return     bool True if Propel is already initialized.
	 */
	public static function isInit()
	{
		return self::$isInit;
	}

	/**
	 * Sets the configuration for Propel and all dependencies.
	 *
	 * @param      mixed The Configuration (array or PropelConfiguration)
	 */
	public static function setConfiguration($c)
	{
		if (is_array($c)) {
			if (isset($c['propel']) && is_array($c['propel'])) {
			  $c = $c['propel'];
		  }
			$c = new PropelConfiguration($c);
		}
		self::$configuration = $c;
	}

	/**
	 * Get the configuration for this component.
	 *
	 * @param      int - PropelConfiguration::TYPE_ARRAY: return the configuration as an array
	 *                   (for backward compatibility this is the default)
	 *                 - PropelConfiguration::TYPE_ARRAY_FLAT: return the configuration as a flat array
	 *                   ($config['name.space.item'])
	 *                 - PropelConfiguration::TYPE_OBJECT: return the configuration as a PropelConfiguration instance
	 * @return     mixed The Configuration (array or PropelConfiguration)
	 */
	public static function getConfiguration($type = PropelConfiguration::TYPE_ARRAY)
	{
		return self::$configuration->getParameters($type);
	}

	/**
	 * Override the configured logger.
	 *
	 * This is primarily for things like unit tests / debugging where
	 * you want to change the logger without altering the configuration file.
	 *
	 * You can use any logger class that implements the propel.logger.BasicLogger
	 * interface.  This interface is based on PEAR::Log, so you can also simply pass
	 * a PEAR::Log object to this method.
	 *
	 * @param      object The new logger to use. ([PEAR] Log or BasicLogger)
	 */
	public static function setLogger($logger)
	{
		self::$logger = $logger;
	}

	/**
	 * Returns true if a logger, for example PEAR::Log, has been configured,
	 * otherwise false.
	 *
	 * @return     bool True if Propel uses logging
	 */
	public static function hasLogger()
	{
		return (self::$logger !== null);
	}

	/**
	 * Get the configured logger.
	 *
	 * @return     object Configured log class ([PEAR] Log or BasicLogger).
	 */
	public static function logger()
	{
		return self::$logger;
	}

	/**
	 * Logs a message
	 * If a logger has been configured, the logger will be used, otherwrise the
	 * logging message will be discarded without any further action
	 *
	 * @param      string The message that will be logged.
	 * @param      string The logging level.
	 *
	 * @return     bool True if the message was logged successfully or no logger was used.
	 */
	public static function log($message, $level = self::LOG_DEBUG)
	{
		if (self::hasLogger()) {
			$logger = self::logger();
			switch ($level) {
				case self::LOG_EMERG:
					return $logger->log($message, $level);
				case self::LOG_ALERT:
					return $logger->alert($message);
				case self::LOG_CRIT:
					return $logger->crit($message);
				case self::LOG_ERR:
					return $logger->err($message);
				case self::LOG_WARNING:
					return $logger->warning($message);
				case self::LOG_NOTICE:
					return $logger->notice($message);
				case self::LOG_INFO:
					return $logger->info($message);
				default:
					return $logger->debug($message);
			}
		}
		return true;
	}

	/**
	 * Returns the database map information. Name relates to the name
	 * of the connection pool to associate with the map.
	 *
	 * The database maps are "registered" by the generated map builder classes.
	 *
	 * @param      string The name of the database corresponding to the DatabaseMap to retrieve.
	 *
	 * @return     DatabaseMap The named <code>DatabaseMap</code>.
	 *
	 * @throws     PropelException - if database map is null or propel was not initialized properly.
	 */
	public static function getDatabaseMap($name = null)
	{
		if ($name === null) {
			$name = self::getDefaultDB();
			if ($name === null) {
				throw new PropelException("DatabaseMap name is null!");
			}
		}

		if (!isset(self::$dbMaps[$name])) {
			$clazz = self::$databaseMapClass;
			self::$dbMaps[$name] = new $clazz($name);
		}

		return self::$dbMaps[$name];
	}

	/**
	 * Sets the database map object to use for specified datasource.
	 *
	 * @param      string $name The datasource name.
	 * @param      DatabaseMap $map The database map object to use for specified datasource.
	 */
	public static function setDatabaseMap($name, DatabaseMap $map)
	{
		if ($name === null) {
			$name = self::getDefaultDB();
		}
		self::$dbMaps[$name] = $map;
	}

	/**
	 * For replication, set whether to always force the use of a master connection.
	 *
	 * @param      boolean $bit True or False
	 */
	public static function setForceMasterConnection($bit)
	{
		self::$forceMasterConnection = (bool) $bit;
	}

	/**
	 * For replication, whether to always force the use of a master connection.
	 *
	 * @return     boolean
	 */
	public static function getForceMasterConnection()
	{
		return self::$forceMasterConnection;
	}

	/**
	 * Sets a Connection for specified datasource name.
	 *
	 * @param      string $name The datasource name for the connection being set.
	 * @param      PropelPDO $con The PDO connection.
	 * @param      string $mode Whether this is a READ or WRITE connection (Propel::CONNECTION_READ, Propel::CONNECTION_WRITE)
	 */
	public static function setConnection($name, PropelPDO $con, $mode = Propel::CONNECTION_WRITE)
	{
		if ($name === null) {
			$name = self::getDefaultDB();
		}
		if ($mode == Propel::CONNECTION_READ) {
			self::$connectionMap[$name]['slave'] = $con;
		} else {
			self::$connectionMap[$name]['master'] = $con;
		}
	}

	/**
	 * Gets an already-opened PDO connection or opens a new one for passed-in db name.
	 *
	 * @param      string $name The datasource name that is used to look up the DSN from the runtime configuation file.
	 * @param      string $mode The connection mode (this applies to replication systems).
	 *
	 * @return     PDO A database connection
	 *
	 * @throws     PropelException - if connection cannot be configured or initialized.
	 */
	public static function getConnection($name = null, $mode = Propel::CONNECTION_WRITE)
	{
		if ($name === null) {
			$name = self::getDefaultDB();
		}

		// IF a WRITE-mode connection was requested
		// or Propel is configured to always use the master connection
		// THEN return the master connection.
		if ($mode != Propel::CONNECTION_READ || self::$forceMasterConnection) {
			return self::getMasterConnection($name);
		} else {
			return self::getSlaveConnection($name);
		}

	} 
	
	/**
	 * Gets an already-opened write PDO connection or opens a new one for passed-in db name.
	 *
	 * @param      string $name The datasource name that is used to look up the DSN 
	 *                          from the runtime configuation file. Empty name not allowed.
	 *
	 * @return     PDO A database connection
	 *
	 * @throws     PropelException - if connection cannot be configured or initialized.
	 */
	public static function getMasterConnection($name)
	{
		if (!isset(self::$connectionMap[$name]['master'])) {
			// load connection parameter for master connection
			$conparams = isset(self::$configuration['datasources'][$name]['connection']) ? self::$configuration['datasources'][$name]['connection'] : null;
			if (empty($conparams)) {
				throw new PropelException('No connection information in your runtime configuration file for datasource ['.$name.']');
			}
			// initialize master connection
			$con = Propel::initConnection($conparams, $name);
			self::$connectionMap[$name]['master'] = $con;
		}

		return self::$connectionMap[$name]['master'];		
	}
	
	/**
	 * Gets an already-opened read PDO connection or opens a new one for passed-in db name.
	 *
	 * @param      string $name The datasource name that is used to look up the DSN 
	 *                          from the runtime configuation file. Empty name not allowed.
	 *
	 * @return     PDO A database connection
	 *
	 * @throws     PropelException - if connection cannot be configured or initialized.
	 */
	public static function getSlaveConnection($name)
	{
		if (!isset(self::$connectionMap[$name]['slave'])) {

			$slaveconfigs = isset(self::$configuration['datasources'][$name]['slaves']) ? self::$configuration['datasources'][$name]['slaves'] : null;

			if (empty($slaveconfigs)) { 
				// no slaves configured for this datasource
				// fallback to the master connection
				self::$connectionMap[$name]['slave'] = self::getMasterConnection($name);
			} else { 
				// Initialize a new slave
				if (isset($slaveconfigs['connection']['dsn'])) { 
					// only one slave connection configured
					$conparams = $slaveconfigs['connection'];
				} else {
					// more than one sleve connection configured
					// pickup a random one
					$randkey = array_rand($slaveconfigs['connection']);
					$conparams = $slaveconfigs['connection'][$randkey];
					if (empty($conparams)) {
						throw new PropelException('No connection information in your runtime configuration file for SLAVE ['.$randkey.'] to datasource ['.$name.']');
					}
				}

				// initialize slave connection
				$con = Propel::initConnection($conparams, $name);
				self::$connectionMap[$name]['slave'] = $con;
			}

		} // if datasource slave not set

		return self::$connectionMap[$name]['slave'];
	}
	
	/**
	 * Opens a new PDO connection for passed-in db name.
	 *
	 * @param      array $conparams Connection paramters.
	 * @param      string $name Datasource name.
	 * @param      string $defaultClass The PDO subclass to instantiate if there is no explicit classname
	 * 									specified in the connection params (default is Propel::CLASS_PROPEL_PDO)
	 *
	 * @return     PDO A database connection of the given class (PDO, PropelPDO, SlavePDO or user-defined)
	 *
	 * @throws     PropelException - if lower-level exception caught when trying to connect.
	 */
	public static function initConnection($conparams, $name, $defaultClass = Propel::CLASS_PROPEL_PDO)
	{

		$dsn = $conparams['dsn'];
		if ($dsn === null) {
			throw new PropelException('No dsn specified in your connection parameters for datasource ['.$name.']');
		}

		if (isset($conparams['classname']) && !empty($conparams['classname'])) {
			$classname = $conparams['classname'];
			if (!class_exists($classname)) {
				throw new PropelException('Unable to load specified PDO subclass: ' . $classname);
			}
		} else {
			$classname = $defaultClass;
		}

		$user = isset($conparams['user']) ? $conparams['user'] : null;
		$password = isset($conparams['password']) ? $conparams['password'] : null;

		// load any driver options from the config file
		// driver options are those PDO settings that have to be passed during the connection construction
		$driver_options = array();
		if ( isset($conparams['options']) && is_array($conparams['options']) ) {
			try {
				self::processDriverOptions( $conparams['options'], $driver_options );
			} catch (PropelException $e) {
				throw new PropelException('Error processing driver options for datasource ['.$name.']', $e);
			}
		}

		try {
			$con = new $classname($dsn, $user, $password, $driver_options);
			$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			if (Propel::getConfiguration(PropelConfiguration::TYPE_OBJECT)->getParameter('debugpdo.logging.enabled', false)) {
				$con->useLogging(true);
			}
		} catch (PDOException $e) {
			throw new PropelException("Unable to open PDO connection", $e);
		}

		// load any connection options from the config file
		// connection attributes are those PDO flags that have to be set on the initialized connection
		if (isset($conparams['attributes']) && is_array($conparams['attributes'])) {
			$attributes = array();
			try {
				self::processDriverOptions( $conparams['attributes'], $attributes );
			} catch (PropelException $e) {
				throw new PropelException('Error processing connection attributes for datasource ['.$name.']', $e);
			}
			foreach ($attributes as $key => $value) {
				$con->setAttribute($key, $value);
			}
		}

		// initialize the connection using the settings provided in the config file. this could be a "SET NAMES <charset>" query for MySQL, for instance
		$adapter = self::getDB($name);
		$adapter->initConnection($con, isset($conparams['settings']) && is_array($conparams['settings']) ? $conparams['settings'] : array());

		return $con;
	}

	/**
	 * Internal function to handle driver options or conneciton attributes in PDO.
	 *
	 * Process the INI file flags to be passed to each connection.
	 *
	 * @param      array Where to find the list of constant flags and their new setting.
	 * @param      array Put the data into here
	 *
	 * @throws     PropelException If invalid options were specified.
	 */
	private static function processDriverOptions($source, &$write_to)
	{
		foreach ($source as $option => $optiondata) {
			if (is_string($option) && strpos($option, '::') !== false) {
				$key = $option;
			} elseif (is_string($option)) {
				$key = 'PropelPDO::' . $option;
			}
			if (!defined($key)) {
				throw new PropelException("Invalid PDO option/attribute name specified: ".$key);
			}
			$key = constant($key);

			$value = $optiondata['value'];
			if (is_string($value) && strpos($value, '::') !== false) {
				if (!defined($value)) {
					throw new PropelException("Invalid PDO option/attribute value specified: ".$value);
				}
				$value = constant($value);
			}

			$write_to[$key] = $value;
		}
	}

	/**
	 * Returns database adapter for a specific datasource.
	 *
	 * @param      string The datasource name.
	 *
	 * @return     DBAdapter The corresponding database adapter.
	 *
	 * @throws     PropelException If unable to find DBdapter for specified db.
	 */
	public static function getDB($name = null)
	{
		if ($name === null) {
			$name = self::getDefaultDB();
		}

		if (!isset(self::$adapterMap[$name])) {
			if (!isset(self::$configuration['datasources'][$name]['adapter'])) {
				throw new PropelException("Unable to find adapter for datasource [" . $name . "].");
			}
			$db = DBAdapter::factory(self::$configuration['datasources'][$name]['adapter']);
			// register the adapter for this name
			self::$adapterMap[$name] = $db;
		}

		return self::$adapterMap[$name];
	}

	/**
	 * Sets a database adapter for specified datasource.
	 *
	 * @param      string $name The datasource name.
	 * @param      DBAdapter $adapter The DBAdapter implementation to use.
	 */
	public static function setDB($name, DBAdapter $adapter)
	{
		if ($name === null) {
			$name = self::getDefaultDB();
		}
		self::$adapterMap[$name] = $adapter;
	}

	/**
	 * Returns the name of the default database.
	 *
	 * @return     string Name of the default DB
	 */
	public static function getDefaultDB()
	{
		if (self::$defaultDBName === null) {
			// Determine default database name.
			self::$defaultDBName = isset(self::$configuration['datasources']['default']) ? self::$configuration['datasources']['default'] : self::DEFAULT_NAME;
		}
		return self::$defaultDBName;
	}

	/**
	 * Closes any associated resource handles.
	 *
	 * This method frees any database connection handles that have been
	 * opened by the getConnection() method.
	 */
	public static function close()
	{
		foreach (self::$connectionMap as $idx => $cons) {
			// Propel::log("Closing connections for " . $idx, Propel::LOG_DEBUG);
			unset(self::$connectionMap[$idx]);
		}
	}

	/**
	 * Autoload function for loading propel dependencies.
	 *
	 * @param      string The class name needing loading.
	 *
	 * @return     boolean TRUE if the class was loaded, false otherwise.
	 */
	public static function autoload($className)
	{
		if (isset(self::$autoloadMap[$className])) {
			require self::$baseDir . self::$autoloadMap[$className];
			return true;
		}
		return false;
	}
	
	/**
	 * Initialize the base directory for the autoloader.
	 * Avoids a call to dirname(__FILE__) each time self::autoload() is called.
	 * FIXME put in the constructor if the Propel class ever becomes a singleton
	 */
	public static function initBaseDir()
	{
		self::$baseDir = dirname(__FILE__) . '/';
	}

	/**
	 * Include once a file specified in DOT notation and return unqualified classname.
	 *
	 * Typically, Propel uses autoload is used to load classes and expects that all classes
	 * referenced within Propel are included in Propel's autoload map.  This method is only
	 * called when a specific non-Propel classname was specified -- for example, the
	 * classname of a validator in the schema.xml.  This method will attempt to include that
	 * class via autoload and then relative to a location on the include_path.
	 *
	 * @param      string $class dot-path to clas (e.g. path.to.my.ClassName).
	 * @return     string unqualified classname
	 */
	public static function importClass($path) {

		// extract classname
		if (($pos = strrpos($path, '.')) === false) {
			$class = $path;
		} else {
			$class = substr($path, $pos + 1);
		}

		// check if class exists, using autoloader to attempt to load it.
		if (class_exists($class, $useAutoload=true)) {
			return $class;
		}

		// turn to filesystem path
		$path = strtr($path, '.', DIRECTORY_SEPARATOR) . '.php';

		// include class
		$ret = include_once($path);
		if ($ret === false) {
			throw new PropelException("Unable to import class: " . $class . " from " . $path);
		}

		// return qualified name
		return $class;
	}

	/**
	 * Set your own class-name for Database-Mapping. Then
	 * you can change the whole TableMap-Model, but keep its
	 * functionality for Criteria.
	 *
	 * @param      string The name of the class.
	 */
	public static function setDatabaseMapClass($name)
	{
		self::$databaseMapClass = $name;
	}

	/**
	 * Disable instance pooling.
	 * 
	 * @return boolean true if the method changed the instance pooling state,
	 *                 false if it was already disabled
	 */
	public static function disableInstancePooling()
	{
		if (!self::$instancePoolingEnabled) {
			return false;
		}
		self::$instancePoolingEnabled = false;
		return true;
	}

	/**
	 * Enable instance pooling (enabled by default).
	 * 
	 * @return boolean true if the method changed the instance pooling state,
	 *                 false if it was already enabled
	 */
	public static function enableInstancePooling()
	{
		if (self::$instancePoolingEnabled) {
			return false;
		}
		self::$instancePoolingEnabled = true;
		return true;
	}

	/**
	 *  the instance pooling behaviour. True by default.
	 *
	 * @return     boolean Whether the pooling is enabled or not.
	 */
	public static function isInstancePoolingEnabled()
	{
		return self::$instancePoolingEnabled;
	}
}

// Since the Propel class is not a true singleton, this code cannot go into the __construct()
Propel::initBaseDir();
spl_autoload_register(array('Propel', 'autoload'));

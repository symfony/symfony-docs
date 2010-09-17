<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * PDO connection subclass that provides the basic fixes to PDO that are required by Propel.
 *
 * This class was designed to work around the limitation in PDO where attempting to begin
 * a transaction when one has already been begun will trigger a PDOException.  Propel
 * relies on the ability to create nested transactions, even if the underlying layer
 * simply ignores these (because it doesn't support nested transactions).
 *
 * The changes that this class makes to the underlying API include the addition of the
 * getNestedTransactionDepth() and isInTransaction() and the fact that beginTransaction()
 * will no longer throw a PDOException (or trigger an error) if a transaction is already
 * in-progress.
 *
 * @author     Cameron Brunner <cameron.brunner@gmail.com>
 * @author     Hans Lellelid <hans@xmpl.org>
 * @author     Christian Abegg <abegg.ch@gmail.com>
 * @since      2006-09-22
 * @package    propel.runtime.connection
 */
class PropelPDO extends PDO
{

	/**
	 * Attribute to use to set whether to cache prepared statements.
	 */
	const PROPEL_ATTR_CACHE_PREPARES = -1;
	
	const DEFAULT_SLOW_THRESHOLD        = 0.1;
	const DEFAULT_ONLYSLOW_ENABLED      = false;

	/**
	 * The current transaction depth.
	 * @var        int
	 */
	protected $nestedTransactionCount = 0;

	/**
	 * Cache of prepared statements (PDOStatement) keyed by md5 of SQL.
	 *
	 * @var        array [md5(sql) => PDOStatement]
	 */
	protected $preparedStatements = array();

	/**
	 * Whether to cache prepared statements.
	 *
	 * @var        boolean
	 */
	protected $cachePreparedStatements = false;
	
	/**
	 * Whether the final commit is possible
	 * Is false if a nested transaction is rolled back
	 */
	protected $isUncommitable = false;
	
	/**
	 * Count of queries performed.
	 * 
	 * @var        int
	 */
	protected $queryCount = 0;
	
	/**
	 * SQL code of the latest performed query.
	 * 
	 * @var        string
	 */
	protected $lastExecutedQuery;
	
	/**
	 * Whether or not the debug is enabled
	 * 
	 * @var        boolean
	 */
	public $useDebug = false;
	
	/**
	 * Configured BasicLogger (or compatible) logger.
	 *
	 * @var        BasicLogger
	 */
	protected $logger;

	/**
	 * The log level to use for logging.
	 *
	 * @var        int
	 */
	private $logLevel = Propel::LOG_DEBUG;
	
	/**
	 * The default value for runtime config item "debugpdo.logging.methods".
	 *
	 * @var        array
	 */
	protected static $defaultLogMethods = array(
		'PropelPDO::exec',
		'PropelPDO::query',
		'DebugPDOStatement::execute',
	);

	/**
	 * Creates a PropelPDO instance representing a connection to a database.
	 *.
	 * If so configured, specifies a custom PDOStatement class and makes an entry
	 * to the log with the state of this object just after its initialization.
	 * Add PropelPDO::__construct to $defaultLogMethods to see this message
	 *
	 * @param      string $dsn Connection DSN.
	 * @param      string $username (optional) The user name for the DSN string.
	 * @param      string $password (optional) The password for the DSN string.
	 * @param      array $driver_options (optional) A key=>value array of driver-specific connection options.
	 * @throws     PDOException if there is an error during connection initialization.
	 */
	public function __construct($dsn, $username = null, $password = null, $driver_options = array())
	{	
		if ($this->useDebug) {
			$debug = $this->getDebugSnapshot();
		}
		
		parent::__construct($dsn, $username, $password, $driver_options);
		
		if ($this->useDebug) {		
			$this->configureStatementClass('DebugPDOStatement', $suppress = true);
			$this->log('Opening connection', null, __METHOD__, $debug);
		}
	}
	
	/**
	 * Gets the current transaction depth.
	 * @return     int
	 */
	public function getNestedTransactionCount()
	{
		return $this->nestedTransactionCount;
	}

	/**
	 * Set the current transaction depth.
	 * @param      int $v The new depth.
	 */
	protected function setNestedTransactionCount($v)
	{
		$this->nestedTransactionCount = $v;
	}

	/**
	 * Is this PDO connection currently in-transaction?
	 * This is equivalent to asking whether the current nested transaction count
	 * is greater than 0.
	 * @return     boolean
	 */
	public function isInTransaction()
	{
		return ($this->getNestedTransactionCount() > 0);
	}
	
	/**
	 * Check whether the connection contains a transaction that can be committed.
	 * To be used in an evironment where Propelexceptions are caught.
	 *
	 * @return boolean True if the connection is in a committable transaction
	 */
	public function isCommitable()
	{
		return $this->isInTransaction() && !$this->isUncommitable;
	}
	
	/**
	 * Overrides PDO::beginTransaction() to prevent errors due to already-in-progress transaction.
	 */
	public function beginTransaction()
	{
		$return = true;
		if (!$this->nestedTransactionCount) {
			$return = parent::beginTransaction();
			if ($this->useDebug) {
				$this->log('Begin transaction', null, __METHOD__);
			}
			$this->isUncommitable = false;
		}
		$this->nestedTransactionCount++;
		return $return;
	}

	/**
	 * Overrides PDO::commit() to only commit the transaction if we are in the outermost
	 * transaction nesting level.
	 */
	public function commit()
	{
		$return = true;
		$opcount = $this->nestedTransactionCount;
		if ($opcount > 0) {
			if ($opcount === 1) {
				if ($this->isUncommitable) {
					throw new PropelException('Cannot commit because a nested transaction was rolled back');
				} else {
					$return = parent::commit();
					if ($this->useDebug) {		
				  	$this->log('Commit transaction', null, __METHOD__);
					}
				}
			}
		$this->nestedTransactionCount--;
		}
		return $return;
	}

	/**
	 * Overrides PDO::rollBack() to only rollback the transaction if we are in the outermost
	 * transaction nesting level
	 * @return     boolean Whether operation was successful.
	 */
	public function rollBack()
	{
		$return = true;
		$opcount = $this->nestedTransactionCount;
		if ($opcount > 0) {
			if ($opcount === 1) {
				$return = parent::rollBack();
				if ($this->useDebug) {
					$this->log('Rollback transaction', null, __METHOD__);
				}
			} else {
				$this->isUncommitable = true;
			}
			$this->nestedTransactionCount--;
		}
		return $return;
	}

	/**
	* Rollback the whole transaction, even if this is a nested rollback
	* and reset the nested transaction count to 0.
	* @return     boolean Whether operation was successful.
	*/
	public function forceRollBack()
	{
		$return = true;
		if ($this->nestedTransactionCount) {
			// If we're in a transaction, always roll it back
			// regardless of nesting level.
			$return = parent::rollBack();

			// reset nested transaction count to 0 so that we don't
			// try to commit (or rollback) the transaction outside this scope.
			$this->nestedTransactionCount = 0;

			if ($this->useDebug) {
				$this->log('Rollback transaction', null, __METHOD__);
			}
		}
		return $return;
	}
  
	/**
	 * Sets a connection attribute.
	 *
	 * This is overridden here to provide support for setting Propel-specific attributes
	 * too.
	 *
	 * @param      int $attribute The attribute to set (e.g. PropelPDO::PROPEL_ATTR_CACHE_PREPARES).
	 * @param      mixed $value The attribute value.
	 */
	public function setAttribute($attribute, $value)
	{
		switch($attribute) {
			case self::PROPEL_ATTR_CACHE_PREPARES:
				$this->cachePreparedStatements = $value;
				break;
			default:
				parent::setAttribute($attribute, $value);
		}
	}

	/**
	 * Gets a connection attribute.
	 *
	 * This is overridden here to provide support for setting Propel-specific attributes
	 * too.
	 *
	 * @param      int $attribute The attribute to get (e.g. PropelPDO::PROPEL_ATTR_CACHE_PREPARES).
	 */
	public function getAttribute($attribute)
	{
		switch($attribute) {
			case self::PROPEL_ATTR_CACHE_PREPARES:
				return $this->cachePreparedStatements;
				break;
			default:
				return parent::getAttribute($attribute);
		}
	}

	/**
	 * Prepares a statement for execution and returns a statement object.
	 * 
	 * Overrides PDO::prepare() in order to:
	 *  - Add logging and query counting if logging is true.
	 *  - Add query caching support if the PropelPDO::PROPEL_ATTR_CACHE_PREPARES was set to true.
	 * 
	 * @param      string $sql This must be a valid SQL statement for the target database server.
	 * @param      array One or more key => value pairs to set attribute values
	 *             for the PDOStatement object that this method returns.
	 * @return     PDOStatement
	 */
	public function prepare($sql, $driver_options = array())
	{	
		if ($this->useDebug) {
			$debug = $this->getDebugSnapshot();
		}
		
		if ($this->cachePreparedStatements) {
			if (!isset($this->preparedStatements[$sql])) {
				$return = parent::prepare($sql, $driver_options);
				$this->preparedStatements[$sql] = $return;
			} else {
				$return = $this->preparedStatements[$sql];
			}
		} else {
			$return = parent::prepare($sql, $driver_options);
		}
		
		if ($this->useDebug) {
			$this->log($sql, null, __METHOD__, $debug);
		}
		
		return $return;
	}

	/**
	 * Execute an SQL statement and return the number of affected rows.
	 * 
	 * Overrides PDO::exec() to log queries when required
	 * 
	 * @return     int
	 */
	public function exec($sql)
	{
	  if ($this->useDebug) {
			$debug = $this->getDebugSnapshot();
		}
		
		$return = parent::exec($sql);
		
		if ($this->useDebug) {
			$this->log($sql, null, __METHOD__, $debug);
			$this->setLastExecutedQuery($sql); 	
			$this->incrementQueryCount();
		}
		
		return $return;
	}

	/**
	 * Executes an SQL statement, returning a result set as a PDOStatement object.
	 * Despite its signature here, this method takes a variety of parameters.
	 * 
	 * Overrides PDO::query() to log queries when required
	 * 
	 * @see        http://php.net/manual/en/pdo.query.php for a description of the possible parameters.
	 * @return     PDOStatement
	 */
	public function query()
	{
	  if ($this->useDebug) {
			$debug = $this->getDebugSnapshot();
		}
		
		$args = func_get_args();
		if (version_compare(PHP_VERSION, '5.3', '<')) {
			$return = call_user_func_array(array($this, 'parent::query'), $args);
		} else {
			$return = call_user_func_array('parent::query', $args);
		}
		
		if ($this->useDebug) {
			$sql = $args[0];
			$this->log($sql, null, __METHOD__, $debug);
			$this->setLastExecutedQuery($sql);
			$this->incrementQueryCount();
		}
		
		return $return;
	}
	
	/**
	 * Clears any stored prepared statements for this connection.
	 */
	public function clearStatementCache()
	{
		$this->preparedStatements = array();
	}

	/**
	 * Configures the PDOStatement class for this connection.
	 * 
	 * @param      boolean $suppressError Whether to suppress an exception if the statement class cannot be set.
	 * @throws     PropelException if the statement class cannot be set (and $suppressError is false).
	 */
	protected function configureStatementClass($class = 'PDOStatement', $suppressError = true)
	{
		// extending PDOStatement is only supported with non-persistent connections
		if (!$this->getAttribute(PDO::ATTR_PERSISTENT)) {
			$this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array($class, array($this)));
		} elseif (!$suppressError) {
			throw new PropelException('Extending PDOStatement is not supported with persistent connections.');
		}
	}

	/**
	 * Returns the number of queries this DebugPDO instance has performed on the database connection.
	 * 
	 * When using DebugPDOStatement as the statement class, any queries by DebugPDOStatement instances
	 * are counted as well.
	 * 
	 * @return     int
	 * @throws     PropelException if persistent connection is used (since unable to override PDOStatement in that case).
	 */
	public function getQueryCount()
	{
		// extending PDOStatement is not supported with persistent connections
		if ($this->getAttribute(PDO::ATTR_PERSISTENT)) {
			throw new PropelException('Extending PDOStatement is not supported with persistent connections. Count would be inaccurate, because we cannot count the PDOStatment::execute() calls. Either don\'t use persistent connections or don\'t call PropelPDO::getQueryCount()');
		}
		return $this->queryCount;
	}

	/**
	 * Increments the number of queries performed by this DebugPDO instance.
	 * 
	 * Returns the original number of queries (ie the value of $this->queryCount before calling this method).
	 * 
	 * @return     int
	 */
	public function incrementQueryCount()
	{
		$this->queryCount++;
	}

	/**
	 * Get the SQL code for the latest query executed by Propel
	 * 
	 * @return string Executable SQL code
	 */
	public function getLastExecutedQuery() 
	{ 
		return $this->lastExecutedQuery; 
	}
	
	/**
	 * Set the SQL code for the latest query executed by Propel
	 * 
	 * @param string $query Executable SQL code
	 */
	public function setLastExecutedQuery($query) 
	{ 
		$this->lastExecutedQuery = $query; 
	}
	
	/**
	 * Enable or disable the query debug features
	 *
	 * @var        boolean $value True to enable debug (default), false to disable it
	 */
	public function useDebug($value = true)
	{
		if ($value) {
			$this->configureStatementClass('DebugPDOStatement', $suppress = true);
		} else {
			// reset query logging
			$this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('PDOStatement'));
			$this->setLastExecutedQuery('');
			$this->queryCount = 0;
		}
		$this->clearStatementCache();
		$this->useDebug = $value;
	}
	
	/**
	 * Sets the logging level to use for logging method calls and SQL statements.
	 *
	 * @param      int $level Value of one of the Propel::LOG_* class constants.
	 */
	public function setLogLevel($level)
	{
		$this->logLevel = $level;
	}

	/**
	 * Sets a logger to use.
	 * 
	 * The logger will be used by this class to log various method calls and their properties.
	 * 
	 * @param      BasicLogger $logger A Logger with an API compatible with BasicLogger (or PEAR Log).
	 */
	public function setLogger($logger)
	{
		$this->logger = $logger;
	}

	/**
	 * Gets the logger in use.
	 * 
	 * @return      BasicLogger $logger A Logger with an API compatible with BasicLogger (or PEAR Log).
	 */
	public function getLogger()
	{
		return $this->logger;
	}

	/**
	 * Logs the method call or SQL using the Propel::log() method or a registered logger class.
	 * 
	 * @uses       self::getLogPrefix()
	 * @see        self::setLogger()
	 * 
	 * @param      string $msg Message to log.
	 * @param      int $level (optional) Log level to use; will use self::setLogLevel() specified level by default.
	 * @param      string $methodName (optional) Name of the method whose execution is being logged.
	 * @param      array $debugSnapshot (optional) Previous return value from self::getDebugSnapshot().
	 */
	public function log($msg, $level = null, $methodName = null, array $debugSnapshot = null)
	{
		// If logging has been specifically disabled, this method won't do anything
		if (!$this->getLoggingConfig('enabled', true)) {
			return;
		}
		
		// If the method being logged isn't one of the ones to be logged, bail
		if (!in_array($methodName, $this->getLoggingConfig('methods', self::$defaultLogMethods))) {
			return;
		}
		
		// If a logging level wasn't provided, use the default one
		if ($level === null) {
			$level = $this->logLevel;
		}

		// Determine if this query is slow enough to warrant logging
		if ($this->getLoggingConfig("onlyslow", self::DEFAULT_ONLYSLOW_ENABLED)) {
			$now = $this->getDebugSnapshot();
			if ($now['microtime'] - $debugSnapshot['microtime'] < $this->getLoggingConfig("details.slow.threshold", self::DEFAULT_SLOW_THRESHOLD)) return;
		}
		
		// If the necessary additional parameters were given, get the debug log prefix for the log line
		if ($methodName && $debugSnapshot) {
			$msg = $this->getLogPrefix($methodName, $debugSnapshot) . $msg;
		}
		
		// We won't log empty messages
		if (!$msg) {
			return;
		}
		
		// Delegate the actual logging forward
		if ($this->logger) {
			$this->logger->log($msg, $level);
		} else {
			Propel::log($msg, $level);
		}
	}
	
	/**
	 * Returns a snapshot of the current values of some functions useful in debugging.
	 *
	 * @return     array
	 */
	public function getDebugSnapshot()
	{
	  if ($this->useDebug) {
			return array(
				'microtime'             => microtime(true),
				'memory_get_usage'      => memory_get_usage($this->getLoggingConfig('realmemoryusage', false)),
				'memory_get_peak_usage' => memory_get_peak_usage($this->getLoggingConfig('realmemoryusage', false)),
				);
		} else {
		  throw new PropelException('Should not get debug snapshot when not debugging');
		}
	}
	
	/**
	 * Returns a named configuration item from the Propel runtime configuration, from under the
	 * 'debugpdo.logging' prefix.  If such a configuration setting hasn't been set, the given default
	 * value will be returned. 
	 *
	 * @param      string $key Key for which to return the value.
	 * @param      mixed $defaultValue Default value to apply if config item hasn't been set.
	 * @return     mixed
	 */
	protected function getLoggingConfig($key, $defaultValue)
	{
		return Propel::getConfiguration(PropelConfiguration::TYPE_OBJECT)->getParameter("debugpdo.logging.$key", $defaultValue);
	}
	
	/**
	 * Returns a prefix that may be prepended to a log line, containing debug information according
	 * to the current configuration.
	 * 
	 * Uses a given $debugSnapshot to calculate how much time has passed since the call to self::getDebugSnapshot(),
	 * how much the memory consumption by PHP has changed etc.
	 *
	 * @see        self::getDebugSnapshot()
	 * 
	 * @param      string $methodName Name of the method whose execution is being logged.
	 * @param      array $debugSnapshot A previous return value from self::getDebugSnapshot().
	 * @return     string
	 */
	protected function getLogPrefix($methodName, $debugSnapshot)
	{
		$prefix		  = '';
		$now		    = $this->getDebugSnapshot();
		$logDetails	= array_keys($this->getLoggingConfig('details', array()));
		$innerGlue	= $this->getLoggingConfig('innerglue', ': ');
		$outerGlue	= $this->getLoggingConfig('outerglue', ' | ');
		
		// Iterate through each detail that has been configured to be enabled
		foreach ($logDetails as $detailName) {
			
			if (!$this->getLoggingConfig("details.$detailName.enabled", false))
				continue;
			
			switch ($detailName) {
				
				case 'slow';
					$value = $now['microtime'] - $debugSnapshot['microtime'] >= $this->getLoggingConfig("details.$detailName.threshold", self::DEFAULT_SLOW_THRESHOLD) ? 'YES' : ' NO';
					break;
				
				case 'time':
					$value = number_format($now['microtime'] - $debugSnapshot['microtime'], $this->getLoggingConfig("details.$detailName.precision", 3)) . ' sec';
					$value = str_pad($value, $this->getLoggingConfig("details.$detailName.pad", 10), ' ', STR_PAD_LEFT);
					break;
				
				case 'mem':
					$value = self::getReadableBytes($now['memory_get_usage'], $this->getLoggingConfig("details.$detailName.precision", 1));
					$value = str_pad($value, $this->getLoggingConfig("details.$detailName.pad", 9), ' ', STR_PAD_LEFT);
					break;
				
				case 'memdelta':
					$value = $now['memory_get_usage'] - $debugSnapshot['memory_get_usage'];
					$value = ($value > 0 ? '+' : '') . self::getReadableBytes($value, $this->getLoggingConfig("details.$detailName.precision", 1));
					$value = str_pad($value, $this->getLoggingConfig("details.$detailName.pad", 10), ' ', STR_PAD_LEFT);
					break;
				
				case 'mempeak':
					$value = self::getReadableBytes($now['memory_get_peak_usage'], $this->getLoggingConfig("details.$detailName.precision", 1));
					$value = str_pad($value, $this->getLoggingConfig("details.$detailName.pad", 9), ' ', STR_PAD_LEFT);
					break;
				
				case 'querycount':
					$value = $this->getQueryCount();
					$value = str_pad($value, $this->getLoggingConfig("details.$detailName.pad", 2), ' ', STR_PAD_LEFT);
					break;
				
				case 'method':
					$value = $methodName;
					$value = str_pad($value, $this->getLoggingConfig("details.$detailName.pad", 28), ' ', STR_PAD_RIGHT);
					break;
				
				default:
					$value = 'n/a';
					break;
				
			}
			
			$prefix .= $detailName . $innerGlue . $value . $outerGlue;
			
		}
		
		return $prefix;
	}
	
	/**
	 * Returns a human-readable representation of the given byte count.
	 *
	 * @param      int $bytes Byte count to convert.
	 * @param      int $precision How many decimals to include.
	 * @return     string
	 */
	protected function getReadableBytes($bytes, $precision)
	{
		$suffix	= array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
	    $total	= count($suffix);
	    
	    for ($i = 0; $bytes > 1024 && $i < $total; $i++)
	    	$bytes /= 1024;
	    
	    return number_format($bytes, $precision) . ' ' . $suffix[$i];
	}
	
	/**
	 * If so configured, makes an entry to the log of the state of this object just prior to its destruction.
	 * Add PropelPDO::__destruct to $defaultLogMethods to see this message
	 *
	 * @see        self::log()
	 */
	public function __destruct()
	{
	  if ($this->useDebug) {
			$this->log('Closing connection', null, __METHOD__, $this->getDebugSnapshot());
		}
	}

}

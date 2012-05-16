<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

require_once 'phing/Task.php';

/**
 * Executes all SQL files referenced in the sqldbmap file against their mapped databases.
 *
 * This task uses an SQL -> Database map in the form of a properties
 * file to insert each SQL file listed into its designated database.
 *
 * @author     Hans Lellelid <hans@xmpl.org>
 * @author     Dominik del Bondio
 * @author     Jeff Martin <jeff@custommonkey.org> (Torque)
 * @author     Michael McCallum <gholam@xtra.co.nz> (Torque)
 * @author     Tim Stephenson <tim.stephenson@sybase.com> (Torque)
 * @author     Jason van Zyl <jvanzyl@apache.org> (Torque)
 * @author     Martin Poeschl <mpoeschl@marmot.at> (Torque)
 * @version    $Revision: 1612 $
 * @package    propel.generator.task
 */
class PropelSQLExec extends Task
{

	private $goodSql = 0;
	private $totalSql = 0;

	const DELIM_ROW = "row";
	const DELIM_NORMAL = "normal";

	/**
	 * The delimiter type indicating whether the delimiter will
	 * only be recognized on a line by itself
	 */
	private $delimiterType = "normal"; // can't use constant just defined

	//private static $delimiterTypes = array(DELIM_NORMAL, DELIM_ROW);
	//private static $errorActions = array("continue", "stop", "abort");

	/** PDO Database connection */
	private $conn = null;

	/** Autocommit flag. Default value is false */
	private $autocommit = false;

	/** DB url. */
	private $url = null;

	/** User name. */
	private $userId = null;

	/** Password */
	private $password = null;

	/** SQL input command */
	private $sqlCommand = "";

	/** SQL transactions to perform */
	private $transactions = array();

	/** SQL Statement delimiter */
	private $delimiter = ";";

	/** Print SQL results. */
	private $print = false;

	/** Print header columns. */
	private $showheaders = true;

	/** Results Output file. */
	private $output = null;

	/** RDBMS Product needed for this SQL. */
	private $rdbms = null;

	/** RDBMS Version needed for this SQL. */
	private $version = null;

	/** Action to perform if an error is found */
	private $onError = "abort";

	/** Encoding to use when reading SQL statements from a file */
	private $encoding = null;

	/** Src directory for the files listed in the sqldbmap. */
	private $srcDir;

	/** Properties file that maps an individual SQL file to a database. */
	private $sqldbmap;

	/**
	 * Set the sqldbmap properties file.
	 *
	 * @param      sqldbmap filename for the sqldbmap
	 */
	public function setSqlDbMap($sqldbmap)
	{
		$this->sqldbmap = $this->project->resolveFile($sqldbmap);
	}

	/**
	 * Get the sqldbmap properties file.
	 *
	 * @return     filename for the sqldbmap
	 */
	public function getSqlDbMap()
	{
		return $this->sqldbmap;
	}

	/**
	 * Set the src directory for the sql files listed in the sqldbmap file.
	 *
	 * @param      PhingFile $srcDir sql source directory
	 */
	public function setSrcDir(PhingFile $srcDir)
	{
		$this->srcDir = $srcDir;
	}

	/**
	 * Get the src directory for the sql files listed in the sqldbmap file.
	 *
	 * @return     PhingFile SQL Source directory
	 */
	public function getSrcDir()
	{
		return $this->srcDir;
	}

	/**
	 * Set the sql command to execute
	 *
	 * @param      sql sql command to execute
	 */
	public function addText($sql)
	{
		$this->sqlCommand .= $sql;
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
	 * @deprecated Specify userid in the DSN URL.
	 */
	public function setUserid($userId)
	{
		$this->userId = $userId;
	}

	/**
	 * Set the password for the DB connection.
	 *
	 * @param      string $password database password
	 * @deprecated Specify password in the DSN URL.
	 */
	public function setPassword($password)
	{
		$this->password = $password;
	}

	/**
	 * Set the autocommit flag for the DB connection.
	 *
	 * @param      boolean $autocommit the autocommit flag
	 */
	public function setAutoCommit($autocommit)
	{
		$this->autocommit = (boolean) $autocommit;
	}

	/**
	 * Set the statement delimiter.
	 *
	 * <p>For example, set this to "go" and delimitertype to "ROW" for
	 * Sybase ASE or MS SQL Server.</p>
	 *
	 * @param      string $delimiter
	 */
	public function setDelimiter($delimiter)
	{
		$this->delimiter = $delimiter;
	}

	/**
	 * Set the Delimiter type for this sql task. The delimiter type takes two
	 * values - normal and row. Normal means that any occurence of the delimiter
	 * terminate the SQL command whereas with row, only a line containing just
	 * the delimiter is recognized as the end of the command.
	 *
	 * @param      string $delimiterType
	 */
	public function setDelimiterType($delimiterType)
	{
		$this->delimiterType = $delimiterType;
	}

	/**
	 * Set the print flag.
	 *
	 * @param      boolean $print
	 */
	public function setPrint($print)
	{
		$this->print = (boolean) $print;
	}

	/**
	 * Set the showheaders flag.
	 *
	 * @param      boolean $showheaders
	 */
	public function setShowheaders($showheaders)
	{
		$this->showheaders = (boolean) $showheaders;
	}

	/**
	 * Set the output file.
	 *
	 * @param      PhingFile $output
	 */
	public function setOutput(PhingFile $output)
	{
		$this->output = $output;
	}

	/**
	 * Set the action to perform onerror
	 *
	 * @param      string $action
	 */
	public function setOnerror($action)
	{
		$this->onError = $action;
	}

	/**
	 * Load the sql file and then execute it
	 *
	 * @throws     BuildException
	 */
	public function main()
	{
		$this->sqlCommand = trim($this->sqlCommand);

		if ($this->sqldbmap === null || $this->getSqlDbMap()->exists() === false) {
			throw new BuildException("You haven't provided an sqldbmap, or "
					. "the one you specified doesn't exist: " . $this->sqldbmap->getPath());
		}

		if ($this->url === null) {
			throw new BuildException("DSN url attribute must be set!");
		}

		$map = new Properties();

		try {
			$map->load($this->getSqlDbMap());
		} catch (IOException $ioe) {
			throw new BuildException("Cannot open and process the sqldbmap!");
		}

		$databases = array();

		foreach ($map->keys() as $sqlfile) {

			$database = $map->getProperty($sqlfile);

			// Q: already there?
			if (!isset($databases[$database])) {
			// A: No.
				$databases[$database] = array();
			}

			// We want to make sure that the base schemas
			// are inserted first.
			if (strpos($sqlfile, "schema.sql") !== false) {
				// add to the beginning of the array
				array_unshift($databases[$database], $sqlfile);
			} else {
				array_push($databases[$database], $sqlfile);
			}
		}

		foreach ($databases as $db => $files) {
			$transactions = array();

			foreach ($files as $fileName) {

				$file = new PhingFile($this->srcDir, $fileName);

				if ($file->exists()) {
					$this->log("Executing statements in file: " . $file->__toString());
					$transaction = new PropelSQLExecTransaction($this);
					$transaction->setSrc($file);
					$transactions[] = $transaction;
				} else {
					$this->log("File '" . $file->__toString()
							. "' in sqldbmap does not exist, so skipping it.");
				}
			}
			$this->insertDatabaseSqlFiles($this->url, $db, $transactions);
		}
	}

	/**
	 * Take the base url, the target database and insert a set of SQL
	 * files into the target database.
	 *
	 * @param      string $url
	 * @param      string $database
	 * @param      array $transactions
	 */
	private function insertDatabaseSqlFiles($url, $database, $transactions)
	{
		$url = str_replace("@DB@", $database, $url);
		$this->log("Our new url -> " . $url);

		try {

			$buf = "Database settings:" . PHP_EOL
			. " URL: " . $url . PHP_EOL
			. ($this->userId ? " user: " . $this->userId . PHP_EOL : "")
			. ($this->password ? " password: " . $this->password . PHP_EOL : "");

			$this->log($buf, Project::MSG_VERBOSE);

			// Set user + password to null if they are empty strings
			if (!$this->userId) { $this->userId = null; }

			if (!$this->password) { $this->password = null; }

			$this->conn = new PDO($url, $this->userId, $this->password);
			$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			// $this->conn->setAutoCommit($this->autocommit);
			// $this->statement = $this->conn->createStatement();

			$out = null;

			try {
				if ($this->output !== null) {
					$this->log("Opening PrintStream to output file " . $this->output->__toString(), Project::MSG_VERBOSE);
					$out = new FileWriter($this->output);
				}

				// Process all transactions
				for ($i=0,$size=count($transactions); $i < $size; $i++) {
					$transactions[$i]->runTransaction($out);
					if (!$this->autocommit) {
						$this->log("Commiting transaction", Project::MSG_VERBOSE);
						$this->conn->commit();
					}
				}
			} catch (Exception $e) {
				if ($out) $out->close();
			}

		} catch (IOException $e) {

			if (!$this->autocommit && $this->conn !== null && $this->onError == "abort") {
				try {
					$this->conn->rollBack();
				} catch (PDOException $ex) {
					// do nothing.
					System::println("Rollback failed.");
				}
			}
			if ($this->statement) $this->statement = null; // close
			throw new BuildException($e);
		} catch (PDOException $e) {
			if (!$this->autocommit && $this->conn !== null && $this->onError == "abort") {
				try {
					$this->conn->rollBack();
				} catch (PDOException $ex) {
					// do nothing.
					System::println("Rollback failed");
				}
			}
			if ($this->statement) $this->statement = null; // close
			throw new BuildException($e);
		}

		   $this->statement = null; // close

		$this->log($this->goodSql . " of " . $this->totalSql
				. " SQL statements executed successfully");
	}

	/**
	 * Read the statements from the .sql file and execute them.
	 * Lines starting with '//', '--' or 'REM ' are ignored.
	 *
	 * Developer note:  must be public in order to be called from
	 * sudo-"inner" class PropelSQLExecTransaction.
	 *
	 * @param      Reader $reader
	 * @param      $out Optional output stream.
	 * @throws     PDOException
	 * @throws     IOException
	 */
	public function runStatements(Reader $reader, $out = null)
	{
		$sql = "";
		$line = "";
		$sqlBacklog = "";
		$hasQuery = false;

		$in = new BufferedReader($reader);

		$parser['pointer'] = 0;
		$parser['isInString'] = false;
		$parser['stringQuotes'] = "";
		$parser['backslashCount'] = 0;
		$parser['parsedString'] = "";

		$sqlParts = array();

		while (($line = $in->readLine()) !== null) {

			$line = trim($line);
			$line = ProjectConfigurator::replaceProperties($this->project, $line,
			$this->project->getProperties());

			if (StringHelper::startsWith("//", $line)
				|| StringHelper::startsWith("--", $line)
		 		|| StringHelper::startsWith("#", $line)) {
				continue;
			}

			if (strlen($line) > 4 && strtoupper(substr($line,0, 4)) == "REM ") {
				continue;
			}

			if ($sqlBacklog !== "") {
				$sql = $sqlBacklog;
				$sqlBacklog = "";
			}

			$sql .= " " . $line . PHP_EOL;

			// SQL defines "--" as a comment to EOL
			// and in Oracle it may contain a hint
			// so we cannot just remove it, instead we must end it
			if (strpos($line, "--") !== false) {
				$sql .= PHP_EOL;
			}

			// DELIM_ROW doesn't need this (as far as i can tell)
			if ($this->delimiterType == self::DELIM_NORMAL) {

				// old regex, being replaced due to segfaults:
				// See: http://propel.phpdb.org/trac/ticket/294
				//$reg = "#((?:\"(?:\\\\.|[^\"])*\"?)+|'(?:\\\\.|[^'])*'?|" . preg_quote($this->delimiter) . ")#";
				//$sqlParts = preg_split($reg, $sql, 0, PREG_SPLIT_DELIM_CAPTURE);

				$i = $parser['pointer'];
				$c = strlen($sql);
				while ($i < $c) {

					$char = $sql[$i];

					switch($char) {
						case "\\":
							$parser['backslashCount']++;
							$this->log("c$i: found ".$parser['backslashCount']." backslash(es)", Project::MSG_VERBOSE);
							break;
						case "'":
						case "\"":
							if ($parser['isInString'] && $parser['stringQuotes'] == $char) {
								if (($parser['backslashCount'] & 1) == 0) {
									#$this->log("$i: out of string", Project::MSG_VERBOSE);
									$parser['isInString'] = false;
								} else {
									$this->log("c$i: rejected quoted delimiter", Project::MSG_VERBOSE);
								}

							} elseif (!$parser['isInString']) {
								$parser['stringQuotes']	= $char;
								$parser['isInString'] = true;
								#$this->log("$i: into string with $parser['stringQuotes']", Project::MSG_VERBOSE);
							}
							break;
					}

					if ($char == $this->delimiter && !$parser['isInString']) {
						$this->log("c$i: valid end of command found!", Project::MSG_VERBOSE);
						$sqlParts[] = $parser['parsedString'];
						$sqlParts[] = $this->delimiter;
						break;
					}
					$parser['parsedString'] .= $char;
					if ($char !== "\\") {
						if ($parser['backslashCount']) $this->log("$i: backslash reset", Project::MSG_VERBOSE);
						$parser['backslashCount'] = 0;
					}
					$i++;
					$parser['pointer']++;
				}

				$sqlBacklog = "";
				foreach ($sqlParts as $sqlPart) {
					// we always want to append, even if it's a delim (which will be stripped off later)
					$sqlBacklog .= $sqlPart;

					// we found a single (not enclosed by ' or ") delimiter, so we can use all stuff before the delim as the actual query
					if ($sqlPart === $this->delimiter) {
						$sql = $sqlBacklog;
						$sqlBacklog = "";
						$hasQuery = true;
					}
				}
			}

			if ($hasQuery || ($this->delimiterType == self::DELIM_ROW && $line == $this->delimiter)) {
				// this assumes there is always a delimter on the end of the SQL statement.
				$sql = StringHelper::substring($sql, 0, strlen($sql) - 1 - strlen($this->delimiter));
				$this->log("SQL: " . $sql, Project::MSG_VERBOSE);
				$this->execSQL($sql, $out);
				$sql = "";
				$hasQuery = false;

				$parser['pointer'] = 0;
				$parser['isInString'] = false;
				$parser['stringQuotes'] = "";
				$parser['backslashCount'] = 0;
				$parser['parsedString'] = "";
				$sqlParts = array();
			}
		}

		// Catch any statements not followed by ;
		if ($sql !== "") {
			$this->execSQL($sql, $out);
		}
	}

	/**
	 * Exec the sql statement.
	 *
	 * @param      sql
	 * @param      out
	 * @throws     PDOException
	 */
	protected function execSQL($sql, $out = null)
	{
		// Check and ignore empty statements
		if (trim($sql) == "") {
			return;
		}

		try {
			$this->totalSql++;

			if (!$this->autocommit) $this->conn->beginTransaction();

			$stmt = $this->conn->prepare($sql);
			$stmt->execute();
			$this->log($stmt->rowCount() . " rows affected", Project::MSG_VERBOSE);

			if (!$this->autocommit) $this->conn->commit();

			$this->goodSql++;
		} catch (PDOException $e) {
			$this->log("Failed to execute: " . $sql, Project::MSG_ERR);
			if ($this->onError != "continue") {
				throw $e;
			}
			$this->log($e->getMessage(), Project::MSG_ERR);
		}
	}

	/**
	 * print any results in the statement.
	 *
	 * @param      out
	 * @throws     PDOException
	 */
	protected function printResults($out = null)
	{
		$rs = null;

		do {
			$rs = $this->statement->getResultSet();

			if ($rs !== null) {

				$this->log("Processing new result set.", Project::MSG_VERBOSE);

				$line = "";

				$colsprinted = false;

				while ($rs->next()) {

					if (!$colsprinted && $this->showheaders) {
						$first = true;
						foreach ($this->fields as $fieldName => $ignore) {
							if ($first) $first = false; else $line .= ",";
							$line .= $fieldName;
						}
					} // if show headers

					$first = true;
					foreach ($rs->fields as $columnValue) {

						if ($columnValue != null) {
							$columnValue = trim($columnValue);
						}

						if ($first) {
							$first = false;
						} else {
							$line .= ",";
						}
						$line .= $columnValue;
					}

					if ($out !== null) {
						$out->write($line);
						$out->newLine();
					}

					System::println($line);
					$line = "";
				} // while rs->next()
			}
		} while ($this->statement->getMoreResults());
		System::println();
		if ($out !== null) $out->newLine();
	}

}

/**
 * "Inner" class that contains the definition of a new transaction element.
 * Transactions allow several files or blocks of statements
 * to be executed using the same Propel connection and commit
 * operation in between.
 * @package    propel.generator.task
 */
class PropelSQLExecTransaction
{

	private $tSrcFile = null;
	private $tSqlCommand = "";
	private $parent;

	function __construct($parent)
	{
		// Parent is required so that we can log things ...
		$this->parent = $parent;
	}

	public function setSrc(PhingFile $src)
	{
		$this->tSrcFile = $src;
	}

	public function addText($sql)
	{
		$this->tSqlCommand .= $sql;
	}

	/**
	 * @throws     IOException, PDOException
	 */
	public function runTransaction($out = null)
	{
		if (!empty($this->tSqlCommand)) {
			$this->parent->log("Executing commands", Project::MSG_INFO);
			$this->parent->runStatements($this->tSqlCommand, $out);
		}

		if ($this->tSrcFile !== null) {
			$this->parent->log("Executing file: " . $this->tSrcFile->getAbsolutePath(), Project::MSG_INFO);
			$reader = new FileReader($this->tSrcFile);
			$this->parent->runStatements($reader, $out);
			$reader->close();
		}
	}
}

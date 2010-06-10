<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

require_once 'reverse/BaseSchemaParser.php';

/**
 * Postgresql database schema parser.
 *
 * @author     Hans Lellelid <hans@xmpl.org>
 * @version    $Revision: 1667 $
 * @package    propel.generator.reverse.pgsql
 */
class PgsqlSchemaParser extends BaseSchemaParser
{

	/**
	 * Map PostgreSQL native types to Propel types.
	 * @var        array
	 */
	 /** Map MySQL native types to Propel (JDBC) types. */
	private static $pgsqlTypeMap = array(
				'bool' => PropelTypes::BOOLEAN,
				'boolean' => PropelTypes::BOOLEAN,
				'tinyint' => PropelTypes::TINYINT,
				'smallint' => PropelTypes::SMALLINT,
				'mediumint' => PropelTypes::SMALLINT,
				'int' => PropelTypes::INTEGER,
				'int4' => PropelTypes::INTEGER,
				'integer' => PropelTypes::INTEGER,
				'int8' => PropelTypes::BIGINT,
				'bigint' => PropelTypes::BIGINT,
				'int24' => PropelTypes::BIGINT,
				'real' => PropelTypes::REAL,
				'float' => PropelTypes::FLOAT,
				'decimal' => PropelTypes::DECIMAL,
				'numeric' => PropelTypes::NUMERIC,
				'double' => PropelTypes::DOUBLE,
				'char' => PropelTypes::CHAR,
				'varchar' => PropelTypes::VARCHAR,
				'date' => PropelTypes::DATE,
				'time' => PropelTypes::TIME,
				'timetz' => PropelTypes::TIME,
				//'year' => PropelTypes::YEAR,  PropelTypes::YEAR does not exist... does this need to be mapped to a different propel type?
				'datetime' => PropelTypes::TIMESTAMP,
				'timestamp' => PropelTypes::TIMESTAMP,
				'timestamptz' => PropelTypes::TIMESTAMP,
				'bytea' => PropelTypes::BLOB,
				'text' => PropelTypes::LONGVARCHAR,
	);

	/**
	 * Gets a type mapping from native types to Propel types
	 *
	 * @return     array
	 */
	protected function getTypeMapping()
	{
		return self::$pgsqlTypeMap;
	}

	/**
	 *
	 */
	public function parse(Database $database, PDOTask $task = null)
	{
		$stmt = $this->dbh->query("SELECT version() as ver");
		$nativeVersion = $stmt->fetchColumn();

		if (!$nativeVersion) {
			throw new EngineException("Failed to get database version");
		}

		$arrVersion = sscanf ($nativeVersion, '%*s %d.%d');
		$version = sprintf ("%d.%d", $arrVersion[0], $arrVersion[1]);

		// Clean up
		$stmt = null;

		$stmt = $this->dbh->query("SELECT c.oid,
								    case when n.nspname='public' then c.relname else n.nspname||'.'||c.relname end as relname
								    FROM pg_class c join pg_namespace n on (c.relnamespace=n.oid)
								    WHERE c.relkind = 'r'
								      AND n.nspname NOT IN ('information_schema','pg_catalog')
								      AND n.nspname NOT LIKE 'pg_temp%'
								      AND n.nspname NOT LIKE 'pg_toast%'
								    ORDER BY relname");

		$tableWraps = array();

		// First load the tables (important that this happen before filling out details of tables)
		$task->log("Reverse Engineering Tables");
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$name = $row['relname'];
			$task->log("  Adding table '" . $name . "'");
			$oid = $row['oid'];
			$table = new Table($name);
			$database->addTable($table);

			// Create a wrapper to hold these tables and their associated OID
			$wrap = new stdClass;
			$wrap->table = $table;
			$wrap->oid = $oid;
			$tableWraps[] = $wrap;
		}

		// Now populate only columns.
		$task->log("Reverse Engineering Columns");
		foreach ($tableWraps as $wrap) {
			$task->log("  Adding columns for table '" . $wrap->table->getName() . "'");
			$this->addColumns($wrap->table, $wrap->oid, $version);
		}

		// Now add indexes and constraints.
		$task->log("Reverse Engineering Indices And Constraints");
		foreach ($tableWraps as $wrap) {
			$task->log("  Adding indices and constraints for table '" . $wrap->table->getName() . "'");
			$this->addForeignKeys($wrap->table, $wrap->oid, $version);
			$this->addIndexes($wrap->table, $wrap->oid, $version);
			$this->addPrimaryKey($wrap->table, $wrap->oid, $version);
		}

		// TODO - Handle Sequences ...
		
		return count($tableWraps);

	}


	 /**
	 * Adds Columns to the specified table.
	 *
	 * @param      Table $table The Table model class to add columns to.
	 * @param      int $oid The table OID
	 * @param      string $version The database version.
	 */
	protected function addColumns(Table $table, $oid, $version)
	{

		// Get the columns, types, etc.
		// Based on code from pgAdmin3 (http://www.pgadmin.org/)
		$stmt = $this->dbh->prepare("SELECT
								        att.attname,
								        att.atttypmod,
								        att.atthasdef,
								        att.attnotnull,
								        def.adsrc,
								        CASE WHEN att.attndims > 0 THEN 1 ELSE 0 END AS isarray,
								        CASE
								            WHEN ty.typname = 'bpchar'
								                THEN 'char'
								            WHEN ty.typname = '_bpchar'
								                THEN '_char'
								            ELSE
								                ty.typname
								        END AS typname,
								        ty.typtype
								    FROM pg_attribute att
								        JOIN pg_type ty ON ty.oid=att.atttypid
								        LEFT OUTER JOIN pg_attrdef def ON adrelid=att.attrelid AND adnum=att.attnum
								    WHERE att.attrelid = ? AND att.attnum > 0
								        AND att.attisdropped IS FALSE
								    ORDER BY att.attnum");

		$stmt->bindValue(1, $oid, PDO::PARAM_INT);
		$stmt->execute();

		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

			$size = null;
			$precision = null;
			$scale = null;

			// Check to ensure that this column isn't an array data type
			if (((int) $row['isarray']) === 1) {
				throw new EngineException (sprintf ("Array datatypes are not currently supported [%s.%s]", $this->name, $row['attname']));
			} // if (((int) $row['isarray']) === 1)

			$name = $row['attname'];

			// If they type is a domain, Process it
			if (strtolower ($row['typtype']) == 'd') {
				$arrDomain = $this->processDomain ($row['typname']);
				$type = $arrDomain['type'];
				$size = $arrDomain['length'];
				$precision = $size;
				$scale = $arrDomain['scale'];
				$boolHasDefault = (strlen (trim ($row['atthasdef'])) > 0) ? $row['atthasdef'] : $arrDomain['hasdefault'];
				$default = (strlen (trim ($row['adsrc'])) > 0) ? $row['adsrc'] : $arrDomain['default'];
				$is_nullable = (strlen (trim ($row['attnotnull'])) > 0) ? $row['attnotnull'] : $arrDomain['notnull'];
				$is_nullable = (($is_nullable == 't') ? false : true);
			} else {
				$type = $row['typname'];
				$arrLengthPrecision = $this->processLengthScale ($row['atttypmod'], $type);
				$size = $arrLengthPrecision['length'];
				$precision = $size;
				$scale = $arrLengthPrecision['scale'];
				$boolHasDefault = $row['atthasdef'];
				$default = $row['adsrc'];
				$is_nullable = (($row['attnotnull'] == 't') ? false : true);
			} // else (strtolower ($row['typtype']) == 'd')

			$autoincrement = null;

			// if column has a default
			if (($boolHasDefault == 't') && (strlen (trim ($default)) > 0)) {
				if (!preg_match('/^nextval\(/', $default)) {
					$strDefault= preg_replace ('/::[\W\D]*/', '', $default);
					$default = str_replace ("'", '', $strDefault);
				} else {
					$autoincrement = true;
					$default = null;
				}
			} else {
				$default = null;
			}

			$propelType = $this->getMappedPropelType($type);
			if (!$propelType) {
				$propelType = Column::DEFAULT_TYPE;
				$this->warn("Column [" . $table->getName() . "." . $name. "] has a column type (".$type.") that Propel does not support.");
			}

			$column = new Column($name);
			$column->setTable($table);
			$column->setDomainForType($propelType);
			// We may want to provide an option to include this:
			// $column->getDomain()->replaceSqlType($type);
			$column->getDomain()->replaceSize($size);
			$column->getDomain()->replaceScale($scale);
			if ($default !== null) {
				if (in_array($default, array('now()'))) {
					$type = ColumnDefaultValue::TYPE_EXPR;
				} else {
					$type = ColumnDefaultValue::TYPE_VALUE;
				}
				$column->getDomain()->setDefaultValue(new ColumnDefaultValue($default, $type));
			}
			$column->setAutoIncrement($autoincrement);
			$column->setNotNull(!$is_nullable);

			$table->addColumn($column);
		}


	} // addColumn()

	private function processLengthScale($intTypmod, $strName)
	{
		// Define the return array
		$arrRetVal = array ('length'=>null, 'scale'=>null);

		// Some datatypes don't have a Typmod
		if ($intTypmod == -1)
		{
			return $arrRetVal;
		} // if ($intTypmod == -1)

		// Numeric Datatype?
		if ($strName == $this->getMappedNativeType(PropelTypes::NUMERIC)) {
			$intLen = ($intTypmod - 4) >> 16;
			$intPrec = ($intTypmod - 4) & 0xffff;
			$intLen = sprintf ("%ld", $intLen);
			if ($intPrec)
			{
				$intPrec = sprintf ("%ld", $intPrec);
			} // if ($intPrec)
			$arrRetVal['length'] = $intLen;
			$arrRetVal['scale'] = $intPrec;
		} // if ($strName == $this->getMappedNativeType(PropelTypes::NUMERIC))
		elseif ($strName == $this->getMappedNativeType(PropelTypes::TIME) || $strName == 'timetz'
			|| $strName == $this->getMappedNativeType(PropelTypes::TIMESTAMP) || $strName == 'timestamptz'
			|| $strName == 'interval' || $strName == 'bit')
		{
			$arrRetVal['length'] = sprintf ("%ld", $intTypmod);
		} // elseif (TIME, TIMESTAMP, INTERVAL, BIT)
		else
		{
			$arrRetVal['length'] = sprintf ("%ld", ($intTypmod - 4));
		} // else
		return $arrRetVal;
	} // private function processLengthScale ($intTypmod, $strName)

	private function processDomain($strDomain)
	{
		if (strlen(trim ($strDomain)) < 1) {
			throw new EngineException ("Invalid domain name [" . $strDomain . "]");
		}

		$stmt = $this->dbh->prepare("SELECT
								        d.typname as domname,
								        b.typname as basetype,
								        d.typlen,
								        d.typtypmod,
								        d.typnotnull,
								        d.typdefault
								    FROM pg_type d
								        INNER JOIN pg_type b ON b.oid = CASE WHEN d.typndims > 0 then d.typelem ELSE d.typbasetype END
								    WHERE
								        d.typtype = 'd'
								        AND d.typname = ?
								    ORDER BY d.typname");
		$stmt->bindValue(1, $strDomain);
		$stmt->execute();

		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!$row) {
			throw new EngineException ("Domain [" . $strDomain . "] not found.");
		}

		$arrDomain = array ();
		$arrDomain['type'] = $row['basetype'];
		$arrLengthPrecision = $this->processLengthScale($row['typtypmod'], $row['basetype']);
		$arrDomain['length'] = $arrLengthPrecision['length'];
		$arrDomain['scale'] = $arrLengthPrecision['scale'];
		$arrDomain['notnull'] = $row['typnotnull'];
		$arrDomain['default'] = $row['typdefault'];
		$arrDomain['hasdefault'] = (strlen (trim ($row['typdefault'])) > 0) ? 't' : 'f';

		$stmt = null; // cleanup
		return $arrDomain;
	} // private function processDomain($strDomain)

	/**
	 * Load foreign keys for this table.
	 */
	protected function addForeignKeys(Table $table, $oid, $version)
	{
		$database = $table->getDatabase();
		$stmt = $this->dbh->prepare("SELECT
								          conname,
								          confupdtype,
								          confdeltype,
								          CASE nl.nspname WHEN 'public' THEN cl.relname ELSE nl.nspname||'.'||cl.relname END as fktab,
								          a2.attname as fkcol,
								          CASE nr.nspname WHEN 'public' THEN cr.relname ELSE nr.nspname||'.'||cr.relname END as reftab,
								          a1.attname as refcol
								    FROM pg_constraint ct
								         JOIN pg_class cl ON cl.oid=conrelid
								         JOIN pg_class cr ON cr.oid=confrelid
								         JOIN pg_namespace nl ON nl.oid = cl.relnamespace
								         JOIN pg_namespace nr ON nr.oid = cr.relnamespace
								         LEFT JOIN pg_catalog.pg_attribute a1 ON a1.attrelid = ct.confrelid
								         LEFT JOIN pg_catalog.pg_attribute a2 ON a2.attrelid = ct.conrelid
								    WHERE
								         contype='f'
								         AND conrelid = ?
								         AND a2.attnum = ct.conkey[1]
								         AND a1.attnum = ct.confkey[1]
								    ORDER BY conname");
		$stmt->bindValue(1, $oid);
		$stmt->execute();

		$foreignKeys = array(); // local store to avoid duplicates

		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

			$name = $row['conname'];
			$local_table = $row['fktab'];
			$local_column = $row['fkcol'];
			$foreign_table = $row['reftab'];
			$foreign_column = $row['refcol'];

			// On Update
			switch ($row['confupdtype']) {
			  case 'c':
				$onupdate = ForeignKey::CASCADE; break;
			  case 'd':
				$onupdate = ForeignKey::SETDEFAULT; break;
			  case 'n':
				$onupdate = ForeignKey::SETNULL; break;
			  case 'r':
				$onupdate = ForeignKey::RESTRICT; break;
			  default:
			  case 'a':
				//NOACTION is the postgresql default
				$onupdate = ForeignKey::NONE; break;
			}
			// On Delete
			switch ($row['confdeltype']) {
			  case 'c':
				$ondelete = ForeignKey::CASCADE; break;
			  case 'd':
				$ondelete = ForeignKey::SETDEFAULT; break;
			  case 'n':
				$ondelete = ForeignKey::SETNULL; break;
			  case 'r':
				$ondelete = ForeignKey::RESTRICT; break;
			  default:
			  case 'a':
				//NOACTION is the postgresql default
				$ondelete = ForeignKey::NONE; break;
			}

			$foreignTable = $database->getTable($foreign_table);
			$foreignColumn = $foreignTable->getColumn($foreign_column);

			$localTable   = $database->getTable($local_table);
			$localColumn   = $localTable->getColumn($local_column);

			if (!isset($foreignKeys[$name])) {
				$fk = new ForeignKey($name);
				$fk->setForeignTableName($foreignTable->getName());
				$fk->setOnDelete($ondelete);
				$fk->setOnUpdate($onupdate);
				$table->addForeignKey($fk);
				$foreignKeys[$name] = $fk;
			}

			$foreignKeys[$name]->addReference($localColumn, $foreignColumn);
		}
	}

	/**
	 * Load indexes for this table
	 */
	protected function addIndexes(Table $table, $oid, $version)
	{
		$stmt = $this->dbh->prepare("SELECT
										DISTINCT ON(cls.relname)
										cls.relname as idxname,
								        indkey,
								        indisunique
								    FROM pg_index idx
								         JOIN pg_class cls ON cls.oid=indexrelid
								    WHERE indrelid = ? AND NOT indisprimary
								    ORDER BY cls.relname");

		$stmt->bindValue(1, $oid);
		$stmt->execute();

		$stmt2 = $this->dbh->prepare("SELECT a.attname
										FROM pg_catalog.pg_class c JOIN pg_catalog.pg_attribute a ON a.attrelid = c.oid
										WHERE c.oid = ? AND a.attnum = ? AND NOT a.attisdropped
										ORDER BY a.attnum");

		$indexes = array();

		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$name = $row["idxname"];
			$unique = ($row["indisunique"] == 't') ? true : false;
			if (!isset($indexes[$name])) {
				if ($unique) {
					$indexes[$name] = new Unique($name);
				} else {
					$indexes[$name] = new Index($name);
				}
				$table->addIndex($indexes[$name]);
			}

			$arrColumns = explode (' ', $row['indkey']);
			foreach ($arrColumns as $intColNum)
			{
			   	$stmt2->bindValue(1, $oid);
			   	$stmt2->bindValue(2, $intColNum);
			   	$stmt2->execute();

				$row2 = $stmt2->fetch(PDO::FETCH_ASSOC);

				$indexes[$name]->addColumn($table->getColumn($row2['attname']));

			} // foreach ($arrColumns as $intColNum)

		}

	}

	/**
	 * Loads the primary key for this table.
	 */
	protected function addPrimaryKey(Table $table, $oid, $version)
	{

		$stmt = $this->dbh->prepare("SELECT
										DISTINCT ON(cls.relname)
										cls.relname as idxname,
										indkey,
										indisunique
									FROM pg_index idx
										JOIN pg_class cls ON cls.oid=indexrelid
									WHERE indrelid = ? AND indisprimary
									ORDER BY cls.relname");
		$stmt->bindValue(1, $oid);
		$stmt->execute();

		// Loop through the returned results, grouping the same key_name together
		// adding each column for that key.

		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$arrColumns = explode (' ', $row['indkey']);
			foreach ($arrColumns as $intColNum) {
				$stmt2 = $this->dbh->prepare("SELECT a.attname
												FROM pg_catalog.pg_class c JOIN pg_catalog.pg_attribute a ON a.attrelid = c.oid
												WHERE c.oid = ? AND a.attnum = ? AND NOT a.attisdropped
												ORDER BY a.attnum");
				$stmt2->bindValue(1, $oid);
				$stmt2->bindValue(2, $intColNum);
				$stmt2->execute();

				$row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
				$table->getColumn($row2['attname'])->setPrimaryKey(true);

			} // foreach ($arrColumns as $intColNum)
		}

	}

	/**
	 * Adds the sequences for this database.
	 *
	 * @return     void
	 * @throws     SQLException
	 */
	protected function addSequences(Database $database)
	{
		/*
		-- WE DON'T HAVE ANY USE FOR THESE YET IN REVERSE ENGINEERING ...
		$this->sequences = array();
		$result = pg_query($this->conn->getResource(), "SELECT c.oid,
								                        case when n.nspname='public' then c.relname else n.nspname||'.'||c.relname end as relname
								                        FROM pg_class c join pg_namespace n on (c.relnamespace=n.oid)
								                        WHERE c.relkind = 'S'
								                          AND n.nspname NOT IN ('information_schema','pg_catalog')
								                          AND n.nspname NOT LIKE 'pg_temp%'
								                          AND n.nspname NOT LIKE 'pg_toast%'
								                        ORDER BY relname");

		if (!$result) {
			throw new SQLException("Could not list sequences", pg_last_error($this->dblink));
		}

		while ($row = pg_fetch_assoc($result)) {
			// FIXME -- decide what info we need for sequences & then create a SequenceInfo object (if needed)
			$obj = new stdClass;
			$obj->name = $row['relname'];
			$obj->oid = $row['oid'];
			$this->sequences[strtoupper($row['relname'])] = $obj;
		}
		*/
	}

}

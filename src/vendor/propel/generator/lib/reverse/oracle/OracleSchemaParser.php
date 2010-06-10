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
 * Oracle database schema parser.
 *
 * @author     Hans Lellelid <hans@xmpl.org>
 * @author     Guillermo Gutierrez <ggutierrez@dailycosas.net> (Adaptation)
 * @version    $Revision: 1612 $
 * @package    propel.generator.reverse.oracle
 */
class OracleSchemaParser extends BaseSchemaParser
{

	/**
	 * Map Oracle native types to Propel types.
	 *
	 * There really aren't any Oracle native types, so we're just
	 * using the MySQL ones here.
	 * 
	 * Left as unsupported: 
	 *   BFILE, 
	 *   RAW, 
	 *   ROWID
	 * 
	 * Supported but non existant as a specific type in Oracle: 
	 *   DECIMAL (NUMBER with scale), 
	 *   DOUBLE (FLOAT with precision = 126) 
	 *
	 * @var        array
	 */
	private static $oracleTypeMap = array(
		'BLOB'		=> PropelTypes::BLOB,
		'CHAR'		=> PropelTypes::CHAR,
		'CLOB'		=> PropelTypes::CLOB,
		'DATE'		=> PropelTypes::DATE,
		'DECIMAL'	=> PropelTypes::DECIMAL,
		'DOUBLE'	=> PropelTypes::DOUBLE,
		'FLOAT'		=> PropelTypes::FLOAT,
		'LONG'		=> PropelTypes::LONGVARCHAR,
		'NCHAR'		=> PropelTypes::CHAR,
		'NCLOB'		=> PropelTypes::CLOB,
		'NUMBER'	=> PropelTypes::BIGINT,
		'NVARCHAR2'	=> PropelTypes::VARCHAR,
		'TIMESTAMP'	=> PropelTypes::TIMESTAMP,
		'VARCHAR2'	=> PropelTypes::VARCHAR,
	);

	/**
	 * Gets a type mapping from native types to Propel types
	 *
	 * @return     array
	 */
	protected function getTypeMapping()
	{
		return self::$oracleTypeMap;
	}

	/**
	 * Searches for tables in the database. Maybe we want to search also the views.
	 * @param	Database $database The Database model class to add tables to.
	 */
	public function parse(Database $database, PDOTask $task = null)
	{
		$tables = array();
		$stmt = $this->dbh->query("SELECT OBJECT_NAME FROM USER_OBJECTS WHERE OBJECT_TYPE = 'TABLE'");
		
		$task->log("Reverse Engineering Table Structures");
		// First load the tables (important that this happen before filling out details of tables)
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			if (strpos($row['OBJECT_NAME'], '$') !== false) {
				// this is an Oracle internal table or materialized view - prune
				continue;
			}
			$table = new Table($row['OBJECT_NAME']);
			$task->log("Adding table '" . $table->getName() . "'");
			$database->addTable($table);
			// Add columns, primary keys and indexes.
			$this->addColumns($table);
			$this->addPrimaryKey($table);
			$this->addIndexes($table);
			$tables[] = $table;
		}

		$task->log("Reverse Engineering Foreign Keys");
		
		foreach ($tables as $table) {
			$task->log("Adding foreign keys for table '" . $table->getName() . "'");
			$this->addForeignKeys($table);
		}
		
		return count($tables);
	}

	/**
	 * Adds Columns to the specified table.
	 *
	 * @param      Table $table The Table model class to add columns to.
	 */
	protected function addColumns(Table $table)
	{
		$stmt = $this->dbh->query("SELECT COLUMN_NAME, DATA_TYPE, NULLABLE, DATA_LENGTH, DATA_SCALE, DATA_DEFAULT FROM USER_TAB_COLS WHERE TABLE_NAME = '" . $table->getName() . "'");
		/* @var stmt PDOStatement */
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			if (strpos($row['COLUMN_NAME'], '$') !== false) {
				// this is an Oracle internal column - prune
				continue;
			}
			$size = $row["DATA_LENGTH"];
			$scale = $row["DATA_SCALE"];
			$default = $row['DATA_DEFAULT'];
			$type = $row["DATA_TYPE"];
			$isNullable = ($row['NULLABLE'] == 'Y');
			if ($type == "NUMBER" && $row["DATA_SCALE"] > 0) {
				$type = "DECIMAL";
			}
			if ($type == "FLOAT"&& $row["DATA_PRECISION"] == 126) {
				$type = "DOUBLE";
			}
			if (strpos($type, 'TIMESTAMP(') !== false) {
				$type = substr($type, 0, strpos($type, '('));
				$default = "0000-00-00 00:00:00";
				$size = null;
				$scale = null;
			}
			if ($type == "DATE") {
				$default = "0000-00-00";
				$size = null;
				$scale = null;
			}
				
			$propelType = $this->getMappedPropelType($type);
			if (!$propelType) {
				$propelType = Column::DEFAULT_TYPE;
				$this->warn("Column [" . $table->getName() . "." . $row['COLUMN_NAME']. "] has a column type (".$row["DATA_TYPE"].") that Propel does not support.");
			}

			$column = new Column($row['COLUMN_NAME']);
			$column->setPhpName(); // Prevent problems with strange col names
			$column->setTable($table);
			$column->setDomainForType($propelType);
			$column->getDomain()->replaceSize($size);
			$column->getDomain()->replaceScale($scale);
			if ($default !== null) {
				$column->getDomain()->setDefaultValue(new ColumnDefaultValue($default, ColumnDefaultValue::TYPE_VALUE));
			}
			$column->setAutoIncrement(false); // Not yet supported
			$column->setNotNull(!$isNullable);
			$table->addColumn($column);
		}
		
	} // addColumn()

	/**
	 * Adds Indexes to the specified table.
	 *
	 * @param      Table $table The Table model class to add columns to.
	 */
	protected function addIndexes(Table $table)
	{
		$stmt = $this->dbh->query("SELECT COLUMN_NAME, INDEX_NAME FROM USER_IND_COLUMNS WHERE TABLE_NAME = '" . $table->getName() . "' ORDER BY COLUMN_NAME");
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		
		$indices = array();
		foreach ($rows as $row) {
			$indices[$row['INDEX_NAME']][]= $row['COLUMN_NAME'];
		}
		
		foreach ($indices as $indexName => $columnNames) {
			$index = new Index($indexName);
			foreach($columnNames AS $columnName) {
				// Oracle deals with complex indices using an internal reference, so... 
				// let's ignore this kind of index
				if ($table->hasColumn($columnName)) {
					$index->addColumn($table->getColumn($columnName));
				}
			}
			// since some of the columns are pruned above, we must only add an index if it has columns
			if ($index->hasColumns()) {
				$table->addIndex($index);
			}
		}
	}
	
	/**
	 * Load foreign keys for this table.
	 * 
	 * @param      Table $table The Table model class to add FKs to
	 */
	protected function addForeignKeys(Table $table)
	{	
		// local store to avoid duplicates
		$foreignKeys = array(); 
		
		$stmt = $this->dbh->query("SELECT CONSTRAINT_NAME, DELETE_RULE, R_CONSTRAINT_NAME FROM USER_CONSTRAINTS WHERE CONSTRAINT_TYPE = 'R' AND TABLE_NAME = '" . $table->getName(). "'");
		/* @var stmt PDOStatement */
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			// Local reference
			$stmt2 = $this->dbh->query("SELECT COLUMN_NAME FROM USER_CONS_COLUMNS WHERE CONSTRAINT_NAME = '".$row['CONSTRAINT_NAME']."' AND TABLE_NAME = '" . $table->getName(). "'");
			/* @var stmt2 PDOStatement */
			$localReferenceInfo = $stmt2->fetch(PDO::FETCH_ASSOC);
			
			// Foreign reference
			$stmt2 = $this->dbh->query("SELECT TABLE_NAME, COLUMN_NAME FROM USER_CONS_COLUMNS WHERE CONSTRAINT_NAME = '".$row['R_CONSTRAINT_NAME']."'");
			$foreignReferenceInfo = $stmt2->fetch(PDO::FETCH_ASSOC);
						
			if (!isset($foreignKeys[$row["CONSTRAINT_NAME"]])) {
				$fk = new ForeignKey($row["CONSTRAINT_NAME"]);
				$fk->setForeignTableName($foreignReferenceInfo['TABLE_NAME']);
				$onDelete = ($row["DELETE_RULE"] == 'NO ACTION') ? 'NONE' : $row["DELETE_RULE"];
				$fk->setOnDelete($onDelete);
				$fk->setOnUpdate($onDelete);
				$fk->addReference(array("local" => $localReferenceInfo['COLUMN_NAME'], "foreign" => $foreignReferenceInfo['COLUMN_NAME']));
				$table->addForeignKey($fk);
				$foreignKeys[$row["CONSTRAINT_NAME"]] = $fk;
			}
		}
	}

	/**
	 * Loads the primary key for this table.
	 * 
	 * @param      Table $table The Table model class to add PK to. 
	 */
	protected function addPrimaryKey(Table $table)
	{
		$stmt = $this->dbh->query("SELECT COLS.COLUMN_NAME FROM USER_CONSTRAINTS CONS, USER_CONS_COLUMNS COLS WHERE CONS.CONSTRAINT_NAME = COLS.CONSTRAINT_NAME AND CONS.TABLE_NAME = '".$table->getName()."' AND CONS.CONSTRAINT_TYPE = 'P'");
		/* @var stmt PDOStatement */
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			// This fixes a strange behavior by PDO. Sometimes the
			// row values are inside an index 0 of an array
			if (array_key_exists(0, $row)) {
				$row = $row[0];
			}
			$table->getColumn($row['COLUMN_NAME'])->setPrimaryKey(true);
		}	
	}

}


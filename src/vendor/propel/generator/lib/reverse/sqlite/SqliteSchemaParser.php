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
 * SQLite database schema parser.
 *
 * @author     Hans Lellelid <hans@xmpl.org>
 * @version    $Revision: 1612 $
 * @package    propel.generator.reverse.sqlite
 */
class SqliteSchemaParser extends BaseSchemaParser
{

	/**
	 * Map Sqlite native types to Propel types.
	 *
	 * There really aren't any SQLite native types, so we're just
	 * using the MySQL ones here.
	 *
	 * @var        array
	 */
	private static $sqliteTypeMap = array(
		'tinyint' => PropelTypes::TINYINT,
		'smallint' => PropelTypes::SMALLINT,
		'mediumint' => PropelTypes::SMALLINT,
		'int' => PropelTypes::INTEGER,
		'integer' => PropelTypes::INTEGER,
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
		'year' => PropelTypes::INTEGER,
		'datetime' => PropelTypes::TIMESTAMP,
		'timestamp' => PropelTypes::TIMESTAMP,
		'tinyblob' => PropelTypes::BINARY,
		'blob' => PropelTypes::BLOB,
		'mediumblob' => PropelTypes::BLOB,
		'longblob' => PropelTypes::BLOB,
		'longtext' => PropelTypes::CLOB,
		'tinytext' => PropelTypes::VARCHAR,
		'mediumtext' => PropelTypes::LONGVARCHAR,
		'text' => PropelTypes::LONGVARCHAR,
		'enum' => PropelTypes::CHAR,
		'set' => PropelTypes::CHAR,
	);

	/**
	 * Gets a type mapping from native types to Propel types
	 *
	 * @return     array
	 */
	protected function getTypeMapping()
	{
		return self::$sqliteTypeMap;
	}

	/**
	 *
	 */
	public function parse(Database $database, PDOTask $task = null)
	{
		$stmt = $this->dbh->query("SELECT name FROM sqlite_master WHERE type='table' UNION ALL SELECT name FROM sqlite_temp_master WHERE type='table' ORDER BY name;");

		// First load the tables (important that this happen before filling out details of tables)
		$tables = array();
		while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$name = $row[0];
			$table = new Table($name);
			$database->addTable($table);
			$tables[] = $table;
		}

		// Now populate only columns.
		foreach ($tables as $table) {
			$this->addColumns($table);
		}

		// Now add indexes and constraints.
		foreach ($tables as $table) {
			$this->addIndexes($table);
		}
		
		return count($tables);

	}


	/**
	 * Adds Columns to the specified table.
	 *
	 * @param      Table $table The Table model class to add columns to.
	 * @param      int $oid The table OID
	 * @param      string $version The database version.
	 */
	protected function addColumns(Table $table)
	{
		$stmt = $this->dbh->query("PRAGMA table_info('" . $table->getName() . "')");

		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

			$name = $row['name'];

			$fulltype = $row['type'];
			$size = null;
			$precision = null;
			$scale = null;

			if (preg_match('/^([^\(]+)\(\s*(\d+)\s*,\s*(\d+)\s*\)$/', $fulltype, $matches)) {
				$type = $matches[1];
				$precision = $matches[2];
				$scale = $matches[3]; // aka precision
			} elseif (preg_match('/^([^\(]+)\(\s*(\d+)\s*\)$/', $fulltype, $matches)) {
				$type = $matches[1];
				$size = $matches[2];
			} else {
				$type = $fulltype;
			}
			// If column is primary key and of type INTEGER, it is auto increment
			// See: http://sqlite.org/faq.html#q1
			$autoincrement = ($row['pk'] == 1 && strtolower($type) == 'integer');
			$not_null = $row['notnull'];
			$default = $row['dflt_value'];


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
				$column->getDomain()->setDefaultValue(new ColumnDefaultValue($default, ColumnDefaultValue::TYPE_VALUE));
			}
			$column->setAutoIncrement($autoincrement);
			$column->setNotNull($not_null);


			if (($row['pk'] == 1) || (strtolower($type) == 'integer')) {
				$column->setPrimaryKey(true);
			}

			$table->addColumn($column);

		}


	} // addColumn()

	/**
	 * Load indexes for this table
	 */
	protected function addIndexes(Table $table)
	{
		$stmt = $this->dbh->query("PRAGMA index_list('" . $table->getName() . "')");

		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

			$name = $row['name'];
			$index = new Index($name);

			$stmt2 = $this->dbh->query("PRAGMA index_info('".$name."')");
			while ($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)) {
				$colname = $row2['name'];
				$index->addColumn($table->getColumn($colname));
			}

			$table->addIndex($index);

		}
	}

}

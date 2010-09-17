<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

require_once 'builder/sql/DDLBuilder.php';

/**
 * DDL Builder class for MySQL.
 *
 * @author     David Z�lke
 * @author     Hans Lellelid <hans@xmpl.org>
 * @package    propel.generator.builder.sql.mysql
 */
class MysqlDDLBuilder extends DDLBuilder
{

	/**
	 * Returns some header SQL that disables foreign key checking.
	 * @return     string DDL
	 */
	public static function getDatabaseStartDDL()
	{
		$ddl = "
# This is a fix for InnoDB in MySQL >= 4.1.x
# It \"suspends judgement\" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;
";
		return $ddl;
	}

	/**
	 * Returns some footer SQL that re-enables foreign key checking.
	 * @return     string DDL
	 */
	public static function getDatabaseEndDDL()
	{
		$ddl = "
# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
";
		return $ddl;
	}


	/**
	 *
	 * @see        parent::addDropStatement()
	 */
	protected function addDropStatements(&$script)
	{
		$script .= "
DROP TABLE IF EXISTS ".$this->quoteIdentifier($this->getTable()->getName()).";
";
	}

	/**
	 * Builds the SQL for current table and returns it as a string.
	 *
	 * This is the main entry point and defines a basic structure that classes should follow.
	 * In most cases this method will not need to be overridden by subclasses.
	 *
	 * @return     string The resulting SQL DDL.
	 */
	public function build()
	{
		$script = "";
		$this->addTable($script);
		return $script;
	}

	/**
	 *
	 * @see        parent::addColumns()
	 */
	protected function addTable(&$script)
	{
		$table = $this->getTable();
		$platform = $this->getPlatform();

		$script .= "
#-----------------------------------------------------------------------------
#-- ".$table->getName()."
#-----------------------------------------------------------------------------
";

		$this->addDropStatements($script);

		$script .= "

CREATE TABLE ".$this->quoteIdentifier($table->getName())."
(
	";

		$lines = array();

		$databaseType = $this->getPlatform()->getDatabaseType();

		foreach ($table->getColumns() as $col) {
			$entry = $this->getColumnDDL($col);
			$colinfo = $col->getVendorInfoForType($databaseType);
			if ( $colinfo->hasParameter('Charset') ) {
				$entry .= ' CHARACTER SET '.$platform->quote($colinfo->getParameter('Charset'));
			}
			if ( $colinfo->hasParameter('Collation') ) {
				$entry .= ' COLLATE '.$platform->quote($colinfo->getParameter('Collation'));
			} elseif ( $colinfo->hasParameter('Collate') ) {
				$entry .= ' COLLATE '.$platform->quote($colinfo->getParameter('Collate'));
			}
			if ($col->getDescription()) {
				$entry .= " COMMENT ".$platform->quote($col->getDescription());
			}
			$lines[] = $entry;
		}

		if ($table->hasPrimaryKey()) {
			$lines[] = "PRIMARY KEY (".$this->getColumnList($table->getPrimaryKey()).")";
		}

		$this->addIndicesLines($lines);
		$this->addForeignKeysLines($lines);

		$sep = ",
	";
		$script .= implode($sep, $lines);

		$script .= "
)";

		$vendorSpecific = $table->getVendorInfoForType($this->getPlatform()->getDatabaseType());
		if ($vendorSpecific->hasParameter('Type')) {
			$mysqlTableType = $vendorSpecific->getParameter('Type');
		} elseif ($vendorSpecific->hasParameter('Engine')) {
			$mysqlTableType = $vendorSpecific->getParameter('Engine');
		} else {
			$mysqlTableType = $this->getBuildProperty("mysqlTableType");
		}

		$script .= sprintf(' %s=%s', $this->getBuildProperty("mysqlTableEngineKeyword"), $mysqlTableType);

		$dbVendorSpecific = $table->getDatabase()->getVendorInfoForType($databaseType);
		$tableVendorSpecific = $table->getVendorInfoForType($databaseType);
		$vendorSpecific = $dbVendorSpecific->getMergedVendorInfo($tableVendorSpecific);

		if ( $vendorSpecific->hasParameter('Charset') ) {
			$script .= ' CHARACTER SET '.$platform->quote($vendorSpecific->getParameter('Charset'));
		}
		if ( $vendorSpecific->hasParameter('Collate') ) {
			$script .= ' COLLATE '.$platform->quote($vendorSpecific->getParameter('Collate'));
		}
		if ( $vendorSpecific->hasParameter('Checksum') ) {
			$script .= ' CHECKSUM='.$platform->quote($vendorSpecific->getParameter('Checksum'));
		}
		if ( $vendorSpecific->hasParameter('Pack_Keys') ) {
			$script .= ' PACK_KEYS='.$platform->quote($vendorSpecific->getParameter('Pack_Keys'));
		}
		if ( $vendorSpecific->hasParameter('Delay_key_write') ) {
			$script .= ' DELAY_KEY_WRITE='.$platform->quote($vendorSpecific->getParameter('Delay_key_write'));
		}

		if ($table->getDescription()) {
			$script .= " COMMENT=".$platform->quote($table->getDescription());
		}
		$script .= ";
";
	}

	/**
	 * Creates a comma-separated list of column names for the index.
	 * For MySQL unique indexes there is the option of specifying size, so we cannot simply use
	 * the getColumnsList() method.
	 * @param      Index $index
	 * @return     string
	 */
	private function getIndexColumnList(Index $index)
	{
		$platform = $this->getPlatform();

		$cols = $index->getColumns();
		$list = array();
		foreach ($cols as $col) {
			$list[] = $this->quoteIdentifier($col) . ($index->hasColumnSize($col) ? '(' . $index->getColumnSize($col) . ')' : '');
		}
		return implode(', ', $list);
	}

	/**
	 * Adds indexes
	 */
	protected function addIndicesLines(&$lines)
	{
		$table = $this->getTable();
		$platform = $this->getPlatform();

		foreach ($table->getUnices() as $unique) {
			$lines[] = "UNIQUE KEY ".$this->quoteIdentifier($unique->getName())." (".$this->getIndexColumnList($unique).")";
		}

		foreach ($table->getIndices() as $index ) {
			$vendorInfo = $index->getVendorInfoForType($platform->getDatabaseType());
			$lines[] .= (($vendorInfo && $vendorInfo->getParameter('Index_type') == 'FULLTEXT') ? 'FULLTEXT ' : '') . "KEY " . $this->quoteIdentifier($index->getName()) . "(" . $this->getIndexColumnList($index) . ")";
		}

	}

	/**
	 * Adds foreign key declarations & necessary indexes for mysql (if they don't exist already).
	 * @see        parent::addForeignKeys()
	 */
	protected function addForeignKeysLines(&$lines)
	{
		$table = $this->getTable();
		$platform = $this->getPlatform();


		/**
		 * A collection of indexed columns. The keys is the column name
		 * (concatenated with a comma in the case of multi-col index), the value is
		 * an array with the names of the indexes that index these columns. We use
		 * it to determine which additional indexes must be created for foreign
		 * keys. It could also be used to detect duplicate indexes, but this is not
		 * implemented yet.
		 * @var array
		 */
		$_indices = array();
		
		$this->collectIndexedColumns('PRIMARY', $table->getPrimaryKey(), $_indices, 'getName');
		
		$_tableIndices = array_merge($table->getIndices(), $table->getUnices());
		foreach ($_tableIndices as $_index) {
		  $this->collectIndexedColumns($_index->getName(), $_index->getColumns(), $_indices);
		}

		// we're determining which tables have foreign keys that point to this table, since MySQL needs an index on
		// any column that is referenced by another table (yep, MySQL _is_ a PITA)
		$counter = 0;
		$allTables = $table->getDatabase()->getTables();
		foreach ($allTables as $_table) {
			foreach ($_table->getForeignKeys() as $_foreignKey) {
				if ($_foreignKey->getForeignTableName() == $table->getName()) {
				  $referencedColumns = $_foreignKey->getForeignColumns();
				  $referencedColumnsHash = $this->getColumnList($referencedColumns);
				  if (!array_key_exists($referencedColumnsHash, $_indices)) {
						// no matching index defined in the schema, so we have to create one
						$indexName = "I_referenced_".$_foreignKey->getName()."_".(++$counter);
						$lines[] = "INDEX ".$this->quoteIdentifier($indexName)." (" .$referencedColumnsHash.")";
						// Add this new index to our collection, otherwise we might add it again (bug #725)
						$this->collectIndexedColumns($indexName, $referencedColumns, $_indices);
					}
				}
			}
		}

		foreach ($table->getForeignKeys() as $fk) {

			$indexName = $this->quoteIdentifier(substr_replace($fk->getName(), 'FI_',  strrpos($fk->getName(), 'FK_'), 3));
			
			$localColumns = $fk->getLocalColumns();
			$localColumnsHash = $this->getColumnList($localColumns);

			if (!array_key_exists($localColumnsHash, $_indices)) {
				// no matching index defined in the schema, so we have to create one. MySQL needs indices on any columns that serve as foreign keys. these are not auto-created prior to 4.1.2
				$lines[] = "INDEX $indexName (".$localColumnsHash.")";
				$this->collectIndexedColumns($indexName, $localColumns, $_indices);
			}
			$str = "CONSTRAINT ".$this->quoteIdentifier($fk->getName())."
		FOREIGN KEY (".$this->getColumnList($fk->getLocalColumns()).")
		REFERENCES ".$this->quoteIdentifier($fk->getForeignTableName()) . " (".$this->getColumnList($fk->getForeignColumns()).")";
			if ($fk->hasOnUpdate()) {
				$str .= "
		ON UPDATE ".$fk->getOnUpdate();
			}
			if ($fk->hasOnDelete()) {
				$str .= "
		ON DELETE ".$fk->getOnDelete();
			}
			$lines[] = $str;
		}
	}
	
	/**
	 * Helper function to collect indexed columns.
	 * @param array $columns The column names, or objects with a $callback method
	 * @param array $indexedColumns The collected indexes
	 * @param string $callback The name of a method to call on each of $columns to get the column name, if needed.
	 * @return unknown_type
	 */
	private function collectIndexedColumns($indexName, $columns, &$collectedIndexes, $callback = null)
	{
	  // Get the actual column names, using the callback if needed.
	  // DDLBuilder::getColumnList tests $col instanceof Column, and no callback - maybe we should too?
	  $colnames = $columns;
	  if ($callback) {
	    $colnames = array();
	    foreach ($columns as $col) {
	      $colnames[] = $col->$callback();
	    }
	  }
	  
	  /**
	   * "If the table has a multiple-column index, any leftmost prefix of the
	   * index can be used by the optimizer to find rows. For example, if you
	   * have a three-column index on (col1, col2, col3), you have indexed search
	   * capabilities on (col1), (col1, col2), and (col1, col2, col3)."
	   * @link http://dev.mysql.com/doc/refman/5.5/en/mysql-indexes.html
	   */
	  $indexedColumns = array();
	  foreach ($colnames as $colname) {
	    $indexedColumns[] = $this->quoteIdentifier($colname);
	    $indexedColumnsHash = implode(',', $indexedColumns);
	    if (!array_key_exists($indexedColumnsHash, $collectedIndexes)) {
	      $collectedIndexes[$indexedColumnsHash] = array();
	    }
	    $collectedIndexes[$indexedColumnsHash][] = $indexName;
	  }
	}

	/**
	 * Checks whether passed-in array of Column objects contains a column with specified name.
	 * @param      array Column[] or string[]
	 * @param      string $searchcol Column name to search for
	 */
	private function containsColname($columns, $searchcol)
	{
		foreach ($columns as $col) {
			if ($col instanceof Column) {
				$col = $col->getName();
			}
			if ($col == $searchcol) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Not used for MySQL since foreign keys are declared inside table declaration.
	 * @see        addForeignKeysLines()
	 */
	protected function addForeignKeys(&$script)
	{
	}

	/**
	 * Not used for MySQL since indexes are declared inside table declaration.
	 * @see        addIndicesLines()
	 */
	protected function addIndices(&$script)
	{
	}

	/**
	 * Builds the DDL SQL for a Column object.
	 * @return     string
	 */
	public function getColumnDDL(Column $col)
	{
		$platform = $this->getPlatform();
		$domain = $col->getDomain();
		$sqlType = $domain->getSqlType();
		$notNullString = $col->getNotNullString();
		$defaultSetting = $col->getDefaultSetting();

		// Special handling of TIMESTAMP/DATETIME types ...
		// See: http://propel.phpdb.org/trac/ticket/538
		if ($sqlType == 'DATETIME') {
			$def = $domain->getDefaultValue();
			if ($def && $def->isExpression()) { // DATETIME values can only have constant expressions
				$sqlType = 'TIMESTAMP';
			}
		} elseif ($sqlType == 'DATE') {
			$def = $domain->getDefaultValue();
			if ($def && $def->isExpression()) {
				throw new EngineException("DATE columns cannot have default *expressions* in MySQL.");
			}
		} elseif ($sqlType == 'TEXT' || $sqlType == 'BLOB') {
			if ($domain->getDefaultValue()) {
				throw new EngineException("BLOB and TEXT columns cannot have DEFAULT values. in MySQL.");
			}
		}

		$sb = "";
		$sb .= $this->quoteIdentifier($col->getName()) . " ";
		$sb .= $sqlType;
		if ($platform->hasSize($sqlType)) {
			$sb .= $domain->printSize();
		}
		$sb .= " ";

		if ($sqlType == 'TIMESTAMP') {
			$notNullString = $col->getNotNullString();
			$defaultSetting = $col->getDefaultSetting();
			if ($notNullString == '') {
				$notNullString = 'NULL';
			}
			if ($defaultSetting == '' && $notNullString == 'NOT NULL') {
				$defaultSetting = 'DEFAULT CURRENT_TIMESTAMP';
			}
			$sb .= $notNullString . " " . $defaultSetting . " ";
		} else {
			$sb .= $defaultSetting . " ";
			$sb .= $notNullString . " ";
		}
		$sb .= $col->getAutoIncrementString();

		return trim($sb);
	}
}

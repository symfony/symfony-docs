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
 * The SQL DDL-building class for Oracle.
 *
 *
 * @author     Hans Lellelid <hans@xmpl.org>
 * @package    propel.generator.builder.sql.pgsql
 */
class OracleDDLBuilder extends DDLBuilder
{

	/**
	 * This function adds any _database_ start/initialization SQL.
	 * This is designed to be called for a database, not a specific table, hence it is static.
	 * @see        parent::getDatabaseStartDDL()
	 *
	 * @return     string The DDL is returned as astring.
	 */
	public static function getDatabaseStartDDL()
	{
		return "
ALTER SESSION SET NLS_DATE_FORMAT='YYYY-MM-DD';
ALTER SESSION SET NLS_TIMESTAMP_FORMAT='YYYY-MM-DD HH24:MI:SS';
";
	}
	
	/**
	 *
	 * @see        parent::addDropStatement()
	 */
	protected function addDropStatements(&$script)
	{
		$table = $this->getTable();
		$platform = $this->getPlatform();
		$script .= "
DROP TABLE ".$this->quoteIdentifier($table->getName())." CASCADE CONSTRAINTS;
";
		if ($table->getIdMethod() == "native") {
			$script .= "
DROP SEQUENCE ".$this->quoteIdentifier($this->getSequenceName()).";
";
		}
	}

	/**
	 *
	 * @see        parent::addColumns()
	 */
	protected function addTable(&$script)
	{
		$table = $this->getTable();
		$script .= "

-----------------------------------------------------------------------
-- ".$table->getName()."
-----------------------------------------------------------------------
";

		$this->addDropStatements($script);

		$script .= "
CREATE TABLE ".$this->quoteIdentifier($table->getName())."
(
	";

		$lines = array();

		foreach ($table->getColumns() as $col) {
			$lines[] = $this->getColumnDDL($col);
		}

		$sep = ",
	";
		$script .= implode($sep, $lines);
		$script .= "
);
";
		$this->addPrimaryKey($script);
		$this->addSequences($script);

	}

	/**
	 *
	 *
	 */
	protected function addPrimaryKey(&$script)
	{
		$table = $this->getTable();
		$platform = $this->getPlatform();
		$tableName = $table->getName();
		$length = strlen($tableName);
		if ($length > 27) {
			$length = 27;
		}
		if ( is_array($table->getPrimaryKey()) && count($table->getPrimaryKey()) ) {
			$script .= "
ALTER TABLE ".$this->quoteIdentifier($table->getName())."
	ADD CONSTRAINT ".$this->quoteIdentifier(substr($tableName,0,$length)."_PK")."
	PRIMARY KEY (";
			$delim = "";
			foreach ($table->getPrimaryKey() as $col) {
				$script .= $delim . $this->quoteIdentifier($col->getName());
				$delim = ",";
			}
	$script .= ");
";
		}
	}

	/**
	 * Adds CREATE SEQUENCE statements for this table.
	 *
	 */
	protected function addSequences(&$script)
	{
		$table = $this->getTable();
		$platform = $this->getPlatform();
		if ($table->getIdMethod() == "native") {
			$script .= "
CREATE SEQUENCE ".$this->quoteIdentifier($this->getSequenceName())."
	INCREMENT BY 1 START WITH 1 NOMAXVALUE NOCYCLE NOCACHE ORDER;
";
		}
	}


	/**
	 * Adds CREATE INDEX statements for this table.
	 * @see        parent::addIndices()
	 */
	protected function addIndices(&$script)
	{
		$table = $this->getTable();
		$platform = $this->getPlatform();
		foreach ($table->getIndices() as $index) {
			$script .= "
CREATE ";
			if ($index->getIsUnique()) {
				$script .= "UNIQUE";
			}
			$script .= "INDEX ".$this->quoteIdentifier($index->getName()) ." ON ".$this->quoteIdentifier($table->getName())." (".$this->getColumnList($index->getColumns()).");
";
		}
	}

	/**
	 *
	 * @see        parent::addForeignKeys()
	 */
	protected function addForeignKeys(&$script)
	{
		$table = $this->getTable();
		$platform = $this->getPlatform();
		foreach ($table->getForeignKeys() as $fk) {
			$script .= "
ALTER TABLE ".$this->quoteIdentifier($table->getName())." 
	ADD CONSTRAINT ".$this->quoteIdentifier($fk->getName())."
	FOREIGN KEY (".$this->getColumnList($fk->getLocalColumns()) .") REFERENCES ".$this->quoteIdentifier($fk->getForeignTableName())." (".$this->getColumnList($fk->getForeignColumns()).")";
			if ($fk->hasOnUpdate()) {
				$this->warn("ON UPDATE not yet implemented for Oracle builder.(ignoring for ".$this->getColumnList($fk->getLocalColumns())." fk).");
				//$script .= " ON UPDATE ".$fk->getOnUpdate();
			}
			if ($fk->hasOnDelete()) {
				$script .= " 
	ON DELETE ".$fk->getOnDelete();
			}
			$script .= ";
";
		}
	}


}

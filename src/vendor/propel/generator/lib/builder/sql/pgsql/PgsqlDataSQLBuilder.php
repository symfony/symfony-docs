<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

require_once 'builder/sql/DataSQLBuilder.php';

/**
 * PostgreSQL class for building data dump SQL.
 *
 * @author     Hans Lellelid <hans@xmpl.org>
 * @package    propel.generator.builder.sql.pgsql
 */
class PgsqlDataSQLBuilder extends DataSQLBuilder
{

	/**
	 * The largets serial value encountered this far.
	 *
	 * @var        int
	 */
	private $maxSeqVal;

	/**
	 * Construct a new PgsqlDataSQLBuilder object.
	 *
	 * @param      Table $table
	 */
	public function __construct(Table $table)
	{
		parent::__construct($table);
	}

	/**
	 * The main method in this class, returns the SQL for INSERTing data into a row.
	 * @param      DataRow $row The row to process.
	 * @return     string
	 */
	public function buildRowSql(DataRow $row)
	{
		$sql = parent::buildRowSql($row);

		$table = $this->getTable();

		if ($table->hasAutoIncrementPrimaryKey() && $table->getIdMethod() == IDMethod::NATIVE) {
			foreach ($row->getColumnValues() as $colValue) {
				if ($colValue->getColumn()->isAutoIncrement()) {
					if ($colValue->getValue() > $this->maxSeqVal) {
						$this->maxSeqVal = $colValue->getValue();
					}
				}
			}
		}

		return $sql;
	}

	public function getTableEndSql()
	{
		$table = $this->getTable();
		$sql = "";
		if ($table->hasAutoIncrementPrimaryKey() && $table->getIdMethod() == IDMethod::NATIVE) {
			$seqname = $this->getDDLBuilder()->getSequenceName();
			$sql .= "SELECT pg_catalog.setval('$seqname', ".((int)$this->maxSeqVal).");
";
		}
		return $sql;
	}

	/**
	 * Get SQL value to insert for Postgres BOOLEAN column.
	 * @param      boolean $value
	 * @return     string The representation of boolean for Postgres ('t' or 'f').
	 */
	protected function getBooleanSql($value)
	{
		if ($value === 'f' || $value === 'false' || $value === "0") {
			$value = false;
		}
		return ($value ? "'t'" : "'f'");
	}

	/**
	 *
	 * @param      mixed $blob Blob object or string containing data.
	 * @return     string
	 */
	protected function getBlobSql($blob)
	{
		// they took magic __toString() out of PHP5.0.0; this sucks
		if (is_object($blob)) {
			$blob = $blob->__toString();
		}
		return "'" . pg_escape_bytea($blob) . "'";
	}

}

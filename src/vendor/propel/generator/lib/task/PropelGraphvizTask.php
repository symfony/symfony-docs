<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

require_once 'task/AbstractPropelDataModelTask.php';
require_once 'model/AppData.php';

/**
 * A task to generate Graphviz dot files from Propel datamodel.
 *
 * @author     Mark Kimsal
 * @version    $Revision: 1612 $
 * @package    propel.generator.task
 */
class PropelGraphvizTask extends AbstractPropelDataModelTask
{

	/**
	 * The properties file that maps an SQL file to a particular database.
	 * @var        PhingFile
	 */
	private $sqldbmap;

	/**
	 * Name of the database.
	 */
	private $database;

	/**
	 * Name of the output directory.
	 */
	private $outDir;


	/**
	 * Set the sqldbmap.
	 * @param      PhingFile $sqldbmap The db map.
	 */
	public function setOutputDirectory(PhingFile $out)
	{
		if (!$out->exists()) {
			$out->mkdirs();
		}
		$this->outDir = $out;
	}


	/**
	 * Set the sqldbmap.
	 * @param      PhingFile $sqldbmap The db map.
	 */
	public function setSqlDbMap(PhingFile $sqldbmap)
	{
		$this->sqldbmap = $sqldbmap;
	}

	/**
	 * Get the sqldbmap.
	 * @return     PhingFile $sqldbmap.
	 */
	public function getSqlDbMap()
	{
		return $this->sqldbmap;
	}

	/**
	 * Set the database name.
	 * @param      string $database
	 */
	public function setDatabase($database)
	{
		$this->database = $database;
	}

	/**
	 * Get the database name.
	 * @return     string
	 */
	public function getDatabase()
	{
		return $this->database;
	}


	public function main()
	{

		$count = 0;

		$dotSyntax = '';

		// file we are going to create

		$dbMaps = $this->getDataModelDbMap();

		foreach ($this->getDataModels() as $dataModel) {

			$dotSyntax .= "digraph G {\n";
			foreach ($dataModel->getDatabases() as $database) {

				$this->log("db: " . $database->getName());

				//print the tables
				foreach ($database->getTables() as $tbl) {

					$this->log("\t+ " . $tbl->getName());

					++$count;
					$dotSyntax .= 'node'.$tbl->getName().' [label="{<table>'.$tbl->getName().'|<cols>';

					foreach ($tbl->getColumns() as $col) {
						$dotSyntax .= $col->getName() . ' (' . $col->getType()  . ')';
						if (count($col->getForeignKeys()) > 0) {
							$dotSyntax .= ' [FK]';
						} elseif ($col->isPrimaryKey()) {
							$dotSyntax .= ' [PK]';
						}
						$dotSyntax .= '\l';
					}
					$dotSyntax .= '}", shape=record];';
					$dotSyntax .= "\n";
				}

				//print the relations

				$count = 0;
				$dotSyntax .= "\n";
				foreach ($database->getTables() as $tbl) {
					++$count;

					foreach ($tbl->getColumns() as $col) {
						$fk = $col->getForeignKeys();
						if ( count($fk) == 0 or $fk === null ) continue;
						if ( count($fk) > 1 ) throw( new Exception("not sure what to do here...") );
						$fk = $fk[0];   // try first one
						$dotSyntax .= 'node'.$tbl->getName() .':cols -> node'.$fk->getForeignTableName() . ':table [label="' . $col->getName() . '=' . implode(',', $fk->getForeignColumns()) . ' "];';
						$dotSyntax .= "\n";
					}
				}



			} // foreach database
			$dotSyntax .= "}\n";

			$this->writeDot($dotSyntax,$this->outDir,$database->getName());

		$dotSyntax = '';

		} //foreach datamodels

	} // main()


	/**
	 * probably insecure
	 */
	function writeDot($dotSyntax, PhingFile $outputDir, $baseFilename) {
		$file = new PhingFile($outputDir, $baseFilename . '.schema.dot');
		$this->log("Writing dot file to " . $file->getAbsolutePath());
		file_put_contents($file->getAbsolutePath(), $dotSyntax);
	}

}

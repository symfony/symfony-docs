<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

require_once 'task/PropelDataModelTemplateTask.php';
require_once 'builder/om/ClassTools.php';

/**
 * This Task creates the OM classes based on the XML schema file.
 *
 * @author     Hans Lellelid <hans@xmpl.org>
 * @package    propel.generator.task
 */
class PropelDataDTDTask extends PropelDataModelTemplateTask
{

	public function main() 
	{
		// check to make sure task received all correct params
		$this->validate();

		if (!$this->mapperElement) {
			throw new BuildException("You must use a <mapper/> element to describe how names should be transformed.");
		}

		$basepath = $this->getOutputDirectory();

		// Get new Capsule context
		$generator = $this->createContext();
		$generator->put("basepath", $basepath); // make available to other templates

		// we need some values that were loaded into the template context
		$basePrefix = $generator->get('basePrefix');
		$project = $generator->get('project');

		foreach ($this->getDataModels() as $dataModel) {

			$this->log("Processing Datamodel : " . $dataModel->getName());

			foreach ($dataModel->getDatabases() as $database) {

				$outFile = $this->getMappedFile($dataModel->getName());

				$generator->put("tables", $database->getTables());
				$generator->parse("data/dtd/dataset.tpl", $outFile->getAbsolutePath());

				$this->log("Generating DTD for database: " . $database->getName());
				$this->log("Creating DTD file: " . $outFile->getPath());

				foreach ($database->getTables() as $tbl) {
					$this->log("\t + " . $tbl->getName());
					$generator->put("table", $tbl);
					$generator->parse("data/dtd/table.tpl", $outFile->getAbsolutePath(), true);
				}

			} // foreach database

		} // foreach dataModel


	} // main()
}

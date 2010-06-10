<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

require_once 'task/AbstractPropelDataModelTask.php';
require_once 'builder/om/ClassTools.php';
require_once 'builder/om/OMBuilder.php';

/**
 * This Task creates the OM classes based on the XML schema file.
 *
 * @author     Hans Lellelid <hans@xmpl.org>
 * @package    propel.generator.task
 */
class PropelOMTask extends AbstractPropelDataModelTask
{

	/**
	 * The platform (php4, php5, etc.) for which the om is being built.
	 * @var        string
	 */
	private $targetPlatform;

	/**
	 * Sets the platform (php4, php5, etc.) for which the om is being built.
	 * @param      string $v
	 */
	public function setTargetPlatform($v) {
		$this->targetPlatform = $v;
	}

	/**
	 * Gets the platform (php4, php5, etc.) for which the om is being built.
	 * @return     string
	 */
	public function getTargetPlatform() {
		return $this->targetPlatform;
	}

	/**
	 * Utility method to create directory for package if it doesn't already exist.
	 * @param      string $path The [relative] package path.
	 * @throws     BuildException - if there is an error creating directories
	 */
	protected function ensureDirExists($path)
	{
		$f = new PhingFile($this->getOutputDirectory(), $path);
		if (!$f->exists()) {
			if (!$f->mkdirs()) {
				throw new BuildException("Error creating directories: ". $f->getPath());
			}
		}
	}

	/**
	 * Uses a builder class to create the output class.
	 * This method assumes that the DataModelBuilder class has been initialized with the build properties.
	 * @param      OMBuilder $builder
	 * @param      boolean $overwrite Whether to overwrite existing files with te new ones (default is YES).
	 * @todo       -cPropelOMTask Consider refactoring build() method into AbstractPropelDataModelTask (would need to be more generic).
	 */
	protected function build(OMBuilder $builder, $overwrite = true)
	{
		$path = $builder->getClassFilePath();
		$this->ensureDirExists(dirname($path));

		$_f = new PhingFile($this->getOutputDirectory(), $path);
		
		// skip files already created once
		if ($_f->exists() && !$overwrite) {
			$this->log("\t\t-> (exists) " . $builder->getClassname(), Project::MSG_VERBOSE);
			return 0;
		}
		
		$script = $builder->build();
		foreach ($builder->getWarnings() as $warning) {
			$this->log($warning, Project::MSG_WARN);
		}
		
		// skip unchanged files
		if ($_f->exists() && $script == $_f->contents()) {
			$this->log("\t\t-> (unchanged) " . $builder->getClassname(), Project::MSG_VERBOSE);
			return 0;
		}
		
		// write / overwrite new / changed files
		$this->log("\t\t-> " . $builder->getClassname() . " [builder: " . get_class($builder) . "]");
		file_put_contents($_f->getAbsolutePath(), $script);
		return 1;
	}

	/**
	 * Main method builds all the targets for a typical propel project.
	 */
	public function main()
	{
		// check to make sure task received all correct params
		$this->validate();

		$generatorConfig = $this->getGeneratorConfig();
		$totalNbFiles = 0;
		
		foreach ($this->getDataModels() as $dataModel) {
			$this->log("Processing Datamodel : " . $dataModel->getName());

			foreach ($dataModel->getDatabases() as $database) {

				$this->log("  - processing database : " . $database->getName());

				foreach ($database->getTables() as $table) {

					if (!$table->isForReferenceOnly()) {
						
						$nbWrittenFiles = 0;

						$this->log("\t+ " . $table->getName());

						// -----------------------------------------------------------------------------------------
						// Create Peer, Object, and TableMap classes
						// -----------------------------------------------------------------------------------------

						// these files are always created / overwrite any existing files
						foreach (array('peer', 'object', 'tablemap', 'query') as $target) {
							$builder = $generatorConfig->getConfiguredBuilder($table, $target);
							$nbWrittenFiles += $this->build($builder);
						}

						// -----------------------------------------------------------------------------------------
						// Create [empty] stub Peer and Object classes if they don't exist
						// -----------------------------------------------------------------------------------------

						// these classes are only generated if they don't already exist
						foreach (array('peerstub', 'objectstub', 'querystub') as $target) {
							$builder = $generatorConfig->getConfiguredBuilder($table, $target);
							$nbWrittenFiles += $this->build($builder, $overwrite=false);
						}

						// -----------------------------------------------------------------------------------------
						// Create [empty] stub child Object classes if they don't exist
						// -----------------------------------------------------------------------------------------

						// If table has enumerated children (uses inheritance) then create the empty child stub classes if they don't already exist.
						if ($table->getChildrenColumn()) {
							$col = $table->getChildrenColumn();
							if ($col->isEnumeratedClasses()) {
								foreach ($col->getChildren() as $child) {
									foreach (array('queryinheritance') as $target) {
										if (!$child->getAncestor()) {
											continue;
										}
										$builder = $generatorConfig->getConfiguredBuilder($table, $target);
										$builder->setChild($child);
										$nbWrittenFiles += $this->build($builder, $overwrite=true);
									}
									foreach (array('objectmultiextend', 'queryinheritancestub') as $target) {
										$builder = $generatorConfig->getConfiguredBuilder($table, $target);
										$builder->setChild($child);
										$nbWrittenFiles += $this->build($builder, $overwrite=false);
									}
								} // foreach
							} // if col->is enumerated
						} // if tbl->getChildrenCol


						// -----------------------------------------------------------------------------------------
						// Create [empty] Interface if it doesn't exist
						// -----------------------------------------------------------------------------------------

						// Create [empty] interface if it does not already exist
						if ($table->getInterface()) {
							$builder = $generatorConfig->getConfiguredBuilder($table, 'interface');
							$nbWrittenFiles += $this->build($builder, $overwrite=false);
						}

						// -----------------------------------------------------------------------------------------
						// Create tree Node classes
						// -----------------------------------------------------------------------------------------

						if ($table->treeMode()) {
							switch($table->treeMode()) {
								case 'NestedSet':
									foreach (array('nestedsetpeer', 'nestedset') as $target) {
										$builder = $generatorConfig->getConfiguredBuilder($table, $target);
										$nbWrittenFiles += $this->build($builder);
									}
								break;

								case 'MaterializedPath':
									foreach (array('nodepeer', 'node') as $target) {
										$builder = $generatorConfig->getConfiguredBuilder($table, $target);
										$nbWrittenFiles += $this->build($builder);
									}

									foreach (array('nodepeerstub', 'nodestub') as $target) {
										$builder = $generatorConfig->getConfiguredBuilder($table, $target);
										$nbWrittenFiles += $this->build($builder, $overwrite=false);
									}
								break;

								case 'AdjacencyList':
									// No implementation for this yet.
								default:
								break;
							}

						} // if Table->treeMode()

						$totalNbFiles += $nbWrittenFiles;
						if ($nbWrittenFiles == 0) {
							$this->log("\t\t(no change)");
						}
					} // if !$table->isForReferenceOnly()

				} // foreach table

			} // foreach database

		} // foreach dataModel
		if ($totalNbFiles) {
			$this->log(sprintf("Object model generation complete - %d files written", $totalNbFiles));
		} else {
			$this->log("Object model generation complete - All files already up to date");
		}
	} // main()
}

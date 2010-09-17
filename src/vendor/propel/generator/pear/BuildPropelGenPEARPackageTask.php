<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */
/*
 *  $Id: BuildPropelGenPEARPackageTask.php 1681 2010-04-16 20:03:57Z francois $
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://phing.info>.
 */

require_once 'phing/tasks/system/MatchingTask.php';
include_once 'phing/types/FileSet.php';
include_once 'phing/tasks/ext/pearpackage/Fileset.php';

/**
 *
 * @author     Hans Lellelid <hans@xmpl.org>
 * @package    phing.tasks.ext
 * @version    $Revision: 1681 $
 */
class BuildPropelGenPEARPackageTask extends MatchingTask
{

	/** Base directory for reading files. */
	private $dir;

	private $version;
	private $state = 'stable';
	private $notes;

	private $filesets = array();

	/** Package file */
	private $packageFile;

	public function init()
	{
		include_once 'PEAR/PackageFileManager2.php';
		if (!class_exists('PEAR_PackageFileManager2')) {
			throw new BuildException("You must have installed PEAR_PackageFileManager2 (PEAR_PackageFileManager >= 1.6.0) in order to create a PEAR package.xml file.");
		}
	}

	private function setOptions($pkg)
	{
		$options['baseinstalldir'] = 'propel';
		$options['packagedirectory'] = $this->dir->getAbsolutePath();

		if (empty($this->filesets)) {
			throw new BuildException("You must use a <fileset> tag to specify the files to include in the package.xml");
		}

		$options['filelistgenerator'] = 'Fileset';

		// Some PHING-specific options needed by our Fileset reader
		$options['phing_project'] = $this->getProject();
		$options['phing_filesets'] = $this->filesets;

		if ($this->packageFile !== null) {
			// create one w/ full path
			$f = new PhingFile($this->packageFile->getAbsolutePath());
			$options['packagefile'] = $f->getName();
			// must end in trailing slash
			$options['outputdirectory'] = $f->getParent() . DIRECTORY_SEPARATOR;
			$this->log("Creating package file: " . $f->getPath(), Project::MSG_INFO);
		} else {
			$this->log("Creating [default] package.xml file in base directory.", Project::MSG_INFO);
		}

		// add baseinstalldir exceptions
		$options['installexceptions'] = array(
			'pear-propel-gen' => '/',
			'pear-propel-gen.bat' => '/',
		);

		$options['dir_roles'] = array(
			'lib' => 'data',
			'resources' => 'data'
		);
		
		$options['exceptions'] = array(
			'pear-propel-gen.bat' => 'script', 
			'pear-propel-gen' => 'script',
		);

		$pkg->setOptions($options);

	}

	/**
	 * Main entry point.
	 * @return     void
	 */
	public function main()
	{
		if ($this->dir === null) {
			throw new BuildException("You must specify the \"dir\" attribute for PEAR package task.");
		}

		if ($this->version === null) {
			throw new BuildException("You must specify the \"version\" attribute for PEAR package task.");
		}

		$package = new PEAR_PackageFileManager2();

		$this->setOptions($package);

		// the hard-coded stuff
		$package->setPackage('propel_generator');
		$package->setSummary('Generator component of the Propel PHP object persistence layer');
		$package->setDescription('Propel is an object persistence layer for PHP5 based on Apache Torque. This package provides the generator engine that builds PHP classes and SQL DDL based on an XML representation of your data model.');
		$package->setChannel('pear.propelorm.org');
		$package->setPackageType('php');

		$package->setReleaseVersion($this->version);
		$package->setAPIVersion($this->version);

		$package->setReleaseStability($this->state);
		$package->setAPIStability($this->state);

		$package->setNotes($this->notes);

		$package->setLicense('MIT', 'http://www.opensource.org/licenses/mit-license.php');

		// Add package maintainers
		$package->addMaintainer('lead', 'hans', 'Hans Lellelid', 'hans@xmpl.org');
		$package->addMaintainer('lead', 'david', 'David Zuelke', 'dz@bitxtender.com');
		$package->addMaintainer('lead', 'francois', 'Francois Zaninotto', 'fzaninotto@[gmail].com');

		// creating a sub-section for 'windows'
		$package->addRelease();
		$package->setOSInstallCondition('windows');
		$package->addInstallAs('pear-propel-gen.bat', 'propel-gen.bat');
		$package->addIgnoreToRelease('pear-propel-gen');

		// creating a sub-section for non-windows
		$package->addRelease();
		$package->addInstallAs('pear-propel-gen', 'propel-gen');
		$package->addIgnoreToRelease('pear-propel-gen.bat');

		// "core" dependencies
		$package->setPhpDep('5.2.4');
		$package->setPearinstallerDep('1.4.0');

		// "package" dependencies
		$package->addPackageDepWithChannel('required', 'phing', 'pear.phing.info', '2.3.0');

		$package->addExtensionDep('required', 'pdo');
		$package->addExtensionDep('required', 'xml');
		$package->addExtensionDep('required', 'xsl');

		// now add the replacements ....
		$package->addReplacement('pear-propel-gen.bat', 'pear-config', '@DATA-DIR@', 'data_dir');
		$package->addReplacement('pear-propel-gen', 'pear-config', '@DATA-DIR@', 'data_dir');

		// now we run this weird generateContents() method that apparently
		// is necessary before we can add replacements ... ?
		$package->generateContents();

		$e = $package->writePackageFile();

		if (PEAR::isError($e)) {
			throw new BuildException("Unable to write package file.", new Exception($e->getMessage()));
		}

	}

	/**
	 * Used by the PEAR_PackageFileManager_PhingFileSet lister.
	 * @return     array FileSet[]
	 */
	public function getFileSets()
	{
		return $this->filesets;
	}

	// -------------------------------
	// Set properties from XML
	// -------------------------------

	/**
	 * Nested creator, creates a FileSet for this task
	 *
	 * @return     FileSet The created fileset object
	 */
	function createFileSet()
	{
		$num = array_push($this->filesets, new FileSet());
		return $this->filesets[$num-1];
	}

	/**
	 * Set the version we are building.
	 * @param      string $v
	 * @return     void
	 */
	public function setVersion($v)
	{
		$this->version = $v;
	}

	/**
	 * Set the state we are building.
	 * @param      string $v
	 * @return     void
	 */
	public function setState($v)
	{
		$this->state = $v;
	}

	/**
	 * Sets release notes field.
	 * @param      string $v
	 * @return     void
	 */
	public function setNotes($v)
	{
		$this->notes = $v;
	}
	/**
	 * Sets "dir" property from XML.
	 * @param      PhingFile $f
	 * @return     void
	 */
	public function setDir(PhingFile $f)
	{
		$this->dir = $f;
	}

	/**
	 * Sets the file to use for generated package.xml
	 */
	public function setDestFile(PhingFile $f)
	{
		$this->packageFile = $f;
	}

}

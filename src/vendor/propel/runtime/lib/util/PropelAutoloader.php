<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * Simple autoloader for Propel generated model classes.
 * This class implements the singleton pattern.
 *
 * @author     Prancois Zaninotto
 * @author     Fabien Potencier
 * @version    $Revision: 1773 $
 * @package    propel.util
 */
class PropelAutoloader
{

	static protected $instance = null;

	protected $classes = array();

	/**
	 * Retrieves the singleton instance of this class.
	 *
	 * @return PropelAutoloader A PropelAutoloader instance.
	 */
	static public function getInstance()
	{
		if (!isset(self::$instance)) {
			self::$instance = new PropelAutoloader();
		}

		return self::$instance;
	}

	/**
	 * Register PropelAutoloader in spl autoloader.
	 *
	 * @return void
	 */
	static public function register()
	{
		ini_set('unserialize_callback_func', 'spl_autoload_call');

		if (false === spl_autoload_register(array(self::getInstance(), 'autoload'))) {
			throw new Exception(sprintf('Unable to register %s::autoload as an autoloading method.', get_class(self::getInstance())));
		}
	}

	/**
	 * Unregister PropelAutoloader from spl autoloader.
	 *
	 * @return void
	 */
	static public function unregister()
	{
		spl_autoload_unregister(array(self::getInstance(), 'autoload'));
	}

	/**
	 * Sets the path for a list of classes.
	 *
	 * @param array $classMap An associative array $className => $classPath
	 */
	public function addClassPaths($classMap)
	{
		$this->classes = array_merge($this->classes, $classMap);
	}
	
	/**
	 * Sets the path for a particular class.
	 *
	 * @param string $class A PHP class name
	 * @param string $path  A path (absolute or relative to the include path)
	 */
	public function addClassPath($class, $path)
	{
		$this->classes[$class] = $path;
	}

	/**
	 * Returns the path where a particular class can be found.
	 *
	 * @param string $class A PHP class name
	 *
	 * @return string|null A path (absolute or relative to the include path)
	 */
	public function getClassPath($class)
	{
		return isset($this->classes[$class]) ? $this->classes[$class] : null;
	}

	/**
	 * Handles autoloading of classes that have been registered in this instance
	 *
	 * @param  string  $class  A class name.
	 *
	 * @return boolean Returns true if the class has been loaded
	 */
	public function autoload($class)
	{
		if (isset($this->classes[$class])) {
			require $this->classes[$class];
			return true;
		}
		return false;
	}
}

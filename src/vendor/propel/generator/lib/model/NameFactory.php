<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

require_once 'exception/EngineException.php';
require_once 'model/NameGenerator.php';
require_once 'model/PhpNameGenerator.php';
require_once 'model/ConstraintNameGenerator.php';

/**
 * A name generation factory.
 *
 * @author     Hans Lellelid <hans@xmpl.org> (Propel)
 * @author     Daniel Rall <dlr@finemaltcoding.com> (Torque)
 * @version    $Revision: 1612 $
 * @package    propel.generator.model
 */
class NameFactory 
{

	/**
	 * The class name of the PHP name generator.
	 */
	const PHP_GENERATOR = 'PhpNameGenerator';

	/**
	 * The fully qualified class name of the constraint name generator.
	 */
	const CONSTRAINT_GENERATOR = 'ConstraintNameGenerator';

	/**
	 * The single instance of this class.
	 */
	private static $instance;

	/**
	 * The cache of <code>NameGenerator</code> algorithms in use for
	 * name generation, keyed by fully qualified class name.
	 */
	private static $algorithms = array();

	/**
	 * Factory method which retrieves an instance of the named generator.
	 *
	 * @param      name The fully qualified class name of the name
	 * generation algorithm to retrieve.
	 */
	protected static function getAlgorithm($name)
	{
		if (!isset(self::$algorithms[$name])) {
			self::$algorithms[$name] = new $name();
		}
		return self::$algorithms[$name];
	}

	/**
	 * Given a list of <code>String</code> objects, implements an
	 * algorithm which produces a name.
	 *
	 * @param      string $algorithmName The fully qualified class name of the {@link NameGenerator}
	 *             implementation to use to generate names.
	 * @param      array $inputs Inputs used to generate a name.
	 * @return     The generated name.
	 * @throws     EngineException
	 */
	public static function generateName($algorithmName, $inputs)
	{
		$algorithm = self::getAlgorithm($algorithmName);
		return $algorithm->generateName($inputs);
	}
}

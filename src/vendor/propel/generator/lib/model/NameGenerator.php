<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * The generic interface to a name generation algorithm.
 *
 * @author     Hans Lellelid <hans@xmpl.org> (Propel)
 * @author     Daniel Rall <dlr@finemaltcoding.com> (Torque)
 * @author     Byron Foster <byron_foster@yahoo.com> (Torque)
 * @version    $Revision: 1612 $
 * @package    propel.generator.model
 */
interface NameGenerator
{

	/**
	 * The character used by most implementations as the separator
	 * between name elements.
	 */
	const STD_SEPARATOR_CHAR = '_';

	/**
	 * Traditional method for converting schema table and column names
	 * to PHP names.  The <code>CONV_METHOD_XXX</code> constants
	 * define how names for columns and tables in the database schema
	 * will be converted to PHP source names.
	 *
	 * @see        PhpNameGenerator::underscoreMethod()
	 */
	const CONV_METHOD_UNDERSCORE = "underscore";

	/**
	 * Heavier method for converting schema table and column names 
	 * to PHP names. Similar to {@link #CONV_METHOD_UNDERSCORE} but
	 * this one will pass only letters and numbers through and will
	 * use as separator any character that is not a letter or a number
	 * inside the string to be converted. The <code>CONV_METHOD_XXX</code> 
	 * constants define how names for columns and tales in the 
	 * database schema will be converted to PHP source names. 
	 */
	const CONV_METHOD_CLEAN = "clean";
	
	/**
	 * Similar to {@link #CONV_METHOD_UNDERSCORE} except nothing is
	 * converted to lowercase.
	 *
	 * @see        PhpNameGenerator::phpnameMethod()
	 */
	const CONV_METHOD_PHPNAME = "phpname";

	/**
	 * Specifies no modification when converting from a schema column
	 * or table name to a PHP name.
	 */
	const CONV_METHOD_NOCHANGE = "nochange";

	/**
	 * Given a list of <code>String</code> objects, implements an
	 * algorithm which produces a name.
	 *
	 * @param      inputs Inputs used to generate a name.
	 * @return     The generated name.
	 * @throws     EngineException
	 */
	public function generateName($inputs);
}

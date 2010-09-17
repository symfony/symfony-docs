<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * Tools to support class & package inclusion and referencing.
 *
 * @author     Hans Lellelid <hans@xmpl.org>
 * @version    $Revision: 1612 $
 * @package    propel.generator.builder.om
 */
class ClassTools
{

	/**
	 * Gets just classname, given a dot-path to class.
	 * @param      string $qualifiedName
	 * @return     string
	 */
	public static function classname($qualifiedName)
	{
		$pos = strrpos($qualifiedName, '.');
		if ($pos === false) {
			return $qualifiedName;  // there is no '.' in the qualifed name
		} else {
			return substr($qualifiedName, $pos + 1); // start just after '.'
		}
	}

	/**
	 * Gets the path to be used in include()/require() statement.
	 *
	 * Supports multiple function signatures:
	 *
	 * (1) getFilePath($dotPathClass);
	 * (2) getFilePath($dotPathPrefix, $className);
	 * (3) getFilePath($dotPathPrefix, $className, $extension);
	 *
	 * @param      string $path dot-path to class or to package prefix.
	 * @param      string $classname class name
	 * @param      string $extension The extension to use on the file.
	 * @return     string The constructed file path.
	 */
	public static function getFilePath($path, $classname = null, $extension = '.php')
	{
		$path = strtr(ltrim($path, '.'), '.', '/');
		if ($classname !== null) {
			if ($path !== "") { $path .= '/'; }
			return $path . $classname . $extension;
		} else {
			return $path . $extension;
		}
	}

	/**
	 * Gets the basePeer path if specified for table/db.
	 * If not, will return 'propel.util.BasePeer'
	 * @return     string
	 */
	public static function getBasePeer(Table $table) {
		$class = $table->getBasePeer();
		if ($class === null) {
			$class = "propel.util.BasePeer";
		}
		return $class;
	}

	/**
	 * Gets the baseClass path if specified for table/db.
	 * If not, will return 'propel.om.BaseObject'
	 * @return     string
	 */
	public static function getBaseClass(Table $table) {
		$class = $table->getBaseClass();
		if ($class === null) {
			$class = "propel.om.BaseObject";
		}
		return $class;
	}

	/**
	 * Gets the interface path if specified for table.
	 * If not, will return 'propel.om.Persistent'.
	 * @return     string
	 */
	public static function getInterface(Table $table) {
		$interface = $table->getInterface();
		if ($interface === null && !$table->isReadOnly()) {
			$interface = "propel.om.Persistent";
		}
		return $interface;
	}

	/**
	 * Gets a list of PHP reserved words.
	 *
	 * @return     array string[]
	 */
	public static function getPhpReservedWords()
	{
		return array(
			'and', 'or', 'xor', 'exception', '__FILE__', '__LINE__',
			'array', 'as', 'break', 'case', 'class', 'const', 'continue',
			'declare', 'default', 'die', 'do', 'echo', 'else', 'elseif', 'empty',
			'enddeclare', 'endfor', 'endforeach', 'endif', 'endswitch', 'endwhile',
			'eval', 'exit', 'extends', 'for', 'foreach', 'function', 'global',
			'if', 'include', 'include_once', 'isset', 'list', 'new', 'print', 'require',
			'require_once', 'return', 'static', 'switch', 'unset', 'use', 'var', 'while',
			'__FUNCTION__', '__CLASS__', '__METHOD__', 'final', 'php_user_filter', 'interface',
			'implements', 'extends', 'public', 'protected', 'private', 'abstract', 'clone', 'try', 'catch',
			'throw', 'this', 'namespace'
		);
	}
}

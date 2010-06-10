<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * A validator for validating the (PHP) type of the value submitted.
 *
 * <code>
 *   <column name="some_int" type="INTEGER" required="true"/>
 *
 *   <validator column="some_int">
 *     <rule name="type" value="integer" message="Please specify an integer value for some_int column." />
 *   </validator>
 * </code>
 *
 * @author     Hans Lellelid <hans@xmpl.org>
 * @version    $Revision: 1612 $
 * @package    propel.runtime.validator
 */
class TypeValidator implements BasicValidator
{
	public function isValid(ValidatorMap $map, $value)
	{
		switch ($map->getValue()) {
			case 'array':
				return is_array($value);
				break;
			case 'bool':
			case 'boolean':
				return is_bool($value);
				break;
			case 'float':
				return is_float($value);
				break;
			case 'int':
			case 'integer':
				return is_int($value);
				break;
			case 'numeric':
				return is_numeric($value);
				break;
			case 'object':
				return is_object($value);
				break;
			case 'resource':
				return is_resource($value);
				break;
			case 'scalar':
				return is_scalar($value);
				break;
			case 'string':
				return is_string($value);
				break;
			case 'function':
				return function_exists($value);
				break;
			default:
				throw new PropelException('Unkonwn type ' . $map->getValue());
				break;
		}
	}
}

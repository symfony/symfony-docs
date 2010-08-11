<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * A validator for maximum string length.
 *
 * Below is an example usage for your Propel xml schema file.
 *
 * Note that if you have specified the size attribute in the column tag
 * you do not have to specify it as value in the validator rule again as
 * this is done automatically.
 *
 * <code>
 *   <column name="username" type="VARCHAR" size="25" required="true" />
 *
 *   <validator column="username">
 *     <rule name="maxLength" message="Passwort must be at least ${value} characters !" />
 *   </validator>
 * </code>
 *
 * @author     Michael Aichler <aichler@mediacluster.de>
 * @version    $Revision: 1849 $
 * @package    propel.runtime.validator
 */
class MaxLengthValidator implements BasicValidator
{

	public function isValid (ValidatorMap $map, $str)
	{
		$len = function_exists('mb_strlen') ? mb_strlen($str) : strlen($str);
		return $len <= intval($map->getValue());
	}
}

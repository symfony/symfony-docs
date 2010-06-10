<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * A validator for maximum values.
 *
 * Below is an example usage for your Propel xml schema file.
 *
 * <code>
 *   <column name="articles" type="INTEGER" required="true" />
 *
 *   <validator column="articles">
 *     <rule name="minValue" value="1"  message="Minimum value for selected articles is ${value} !" />
 *     <rule name="maxValue" value="10"  message="Maximum value for selected articles is ${value} !" />
 *   </validator>
 * </code>
 *
 * @author     Michael Aichler <aichler@mediacluster.de>
 * @version    $Revision: 1612 $
 * @package    propel.runtime.validator
 */
class MaxValueValidator implements BasicValidator
{

	/**
	 * @see        BasicValidator::isValid()
	 */
	public function isValid (ValidatorMap $map, $value)
	{
		if (is_null($value) == false && is_numeric($value) == true) {
			return intval($value) <= intval($map->getValue());
		}

		return false;
	}
}

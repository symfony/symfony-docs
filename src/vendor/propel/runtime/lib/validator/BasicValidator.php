<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * Basic Validator interface.
 *
 * BasicValidator objects perform validation without any knowledge of column/table
 * context.  They are simply given an input and some value and asked whether the input
 * is valid.
 *
 * @author     Michael Aichler <aichler@mediacluster.de>
 * @version    $Revision: 1612 $
 * @package    propel.runtime.validator
 */
interface BasicValidator
{

	/**
	 * Determine whether a value meets the criteria specified
	 *
	 * @param      ValidatorMap $map A column map object for the column to be validated.
	 * @param      string $str a <code>String</code> to be tested
	 *
	 * @return     mixed TRUE if valid, error message otherwise
	 */
	public function isValid(ValidatorMap $map, $str);

}

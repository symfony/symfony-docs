<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * A validator for unique column names.
 *
 * <code>
 *   <column name="username" type="VARCHAR" size="25" required="true" />
 *
 *   <validator column="username">
 *     <rule name="unique" message="Username already exists !" />
 *   </validator>
 * </code>
 *
 * @author     Michael Aichler <aichler@mediacluster.de>
 * @version    $Revision: 1612 $
 * @package    propel.runtime.validator
 */
class UniqueValidator implements BasicValidator
{

	/**
	 * @see        BasicValidator::isValid()
	 */
	public function isValid (ValidatorMap $map, $str)
	{
		$column = $map->getColumn();

		$c = new Criteria();
		$c->add($column->getFullyQualifiedName(), $str, Criteria::EQUAL);

		$table = $column->getTable()->getClassName();

		$clazz = $table . 'Peer';
		$count = call_user_func(array($clazz, 'doCount'), $c);

		$isValid = ($count === 0);

		return $isValid;
	}
}

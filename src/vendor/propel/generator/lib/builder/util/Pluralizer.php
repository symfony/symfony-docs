<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * The generic interface to create a plural form of a name.
 *
 * @author     Hans Lellelid <hans@xmpl.org>
 * @version    $Revision: 1612 $
 * @package    propel.generator.builder.util
 */
interface Pluralizer
{

	/**
	 * Generate a plural name based on the passed in root.
	 * @param      string $root The root that needs to be pluralized (e.g. Author)
	 * @return     string The plural form of $root.
	 */
	public function getPluralForm($root);

}

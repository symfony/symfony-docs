<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

require_once 'model/Index.php';

/**
 * Information about unique columns of a table.  This class assumes
 * that in the underlying RDBMS, unique constraints and unique indices
 * are roughly equivalent.  For example, adding a unique constraint to
 * a column also creates an index on that column (this is known to be
 * true for MySQL and Oracle).
 *
 * @author     Hans Lellelid <hans@xmpl.org> (Propel)
 * @author     Jason van Zyl <jvanzyl@apache.org> (Torque)
 * @author     Daniel Rall <dlr@collab.net> (Torque)
 * @version    $Revision: 1612 $
 * @package    propel.generator.model
 */
class Unique extends Index
{

	/**
	 * Returns <code>true</code>.
	 */
	public function isUnique()
	{
		return true;
	}

	/**
	 * @see        XMLElement::appendXml(DOMNode)
	 */
	public function appendXml(DOMNode $node)
	{
		$doc = ($node instanceof DOMDocument) ? $node : $node->ownerDocument;

		$uniqueNode = $node->appendChild($doc->createElement('unique'));
		$uniqueNode->setAttribute('name', $this->getName());
		$columns = $this->getColumns();
		foreach ($this->getColumns() as $colname) {
			$uniqueColNode = $uniqueNode->appendChild($doc->createElement('unique-column'));
			$uniqueColNode->setAttribute('name', $colname);
		}

		foreach ($this->vendorInfos as $vi) {
			$vi->appendXml($uniqueNode);
		}
	}


}

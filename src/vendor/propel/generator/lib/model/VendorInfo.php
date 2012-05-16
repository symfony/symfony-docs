<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

require_once 'model/XMLElement.php';
require_once 'exception/EngineException.php';

/**
 * Object to hold vendor-specific info.
 *
 * @author     Hans Lellelid <hans@xmpl.org>
 * @version    $Revision: 1612 $
 * @package    propel.generator.model
 */
class VendorInfo extends XMLElement
{

	/**
	 * The vendor RDBMS type.
	 *
	 * @var        string
	 */
	private $type;

	/**
	 * Vendor parameters.
	 *
	 * @var        array
	 */
	private $parameters = array();

	/**
	 * Creates a new VendorInfo instance.
	 *
	 * @param      string $type RDBMS type (optional)
	 */
	public function __construct($type = null)
	{
		$this->type = $type;
	}

	/**
	 * Sets up this object based on the attributes that were passed to loadFromXML().
	 * @see        parent::loadFromXML()
	 */
	protected function setupObject()
	{
		$this->type = $this->getAttribute("type");
	}

	/**
	 * Set RDBMS type for this vendor-specific info.
	 *
	 * @param      string $v
	 */
	public function setType($v)
	{
		$this->type = $v;
	}

	/**
	 * Get RDBMS type for this vendor-specific info.
	 *
	 * @return     string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Adds a new vendor parameter to this object.
	 * @param      array $attrib Attributes from XML.
	 */
	public function addParameter($attrib)
	{
		$name = $attrib["name"];
		$this->parameters[$name] = $attrib["value"];
	}

	/**
	 * Sets parameter value.
	 *
	 * @param      string $name
	 * @param      mixed $value The value for the parameter.
	 */
	public function setParameter($name, $value)
	{
		$this->parameters[$name] = $value;
	}

	/**
	 * Gets parameter value.
	 *
	 * @param      string $name
	 * @return     mixed Paramter value.
	 */
	public function getParameter($name)
	{
		if (isset($this->parameters[$name])) {
			return $this->parameters[$name];
		}
		return null; // just to be explicit
	}

	/**
	 * Whether parameter exists.
	 *
	 * @param      string $name
	 */
	public function hasParameter($name)
	{
		return isset($this->parameters[$name]);
	}

	/**
	 * Sets assoc array of parameters for venfor specific info.
	 *
	 * @param      array $params Paramter data.
	 */
	public function setParameters(array $params = array())
	{
		$this->parameters = $params;
	}

	/**
	 * Gets assoc array of parameters for venfor specific info.
	 *
	 * @return     array
	 */
	public function getParameters()
	{
		return $this->parameters;
	}

	/**
	 * Gets a new merged VendorInfo object.
	 * @param      VendorInfo $info
	 * @return     VendorInfo new object with merged parameters
	 */
	public function getMergedVendorInfo(VendorInfo $merge)
	{
		$newParams = array_merge($this->getParameters(), $merge->getParameters());
		$newInfo = new VendorInfo($this->getType());
		$newInfo->setParameters($newParams);
		return $newInfo;
	}

	/**
	 * @see        XMLElement::appendXml(DOMNode)
	 */
	public function appendXml(DOMNode $node)
	{
		$doc = ($node instanceof DOMDocument) ? $node : $node->ownerDocument;

		$vendorNode = $node->appendChild($doc->createElement("vendor"));
		$vendorNode->setAttribute("type", $this->getType());

		foreach ($this->parameters as $key => $value) {
			$parameterNode = $doc->createElement("parameter");
			$parameterNode->setAttribute("name", $key);
			$parameterNode->setAttribute("value", $value);
			$vendorNode->appendChild($parameterNode);
		}
	}
}

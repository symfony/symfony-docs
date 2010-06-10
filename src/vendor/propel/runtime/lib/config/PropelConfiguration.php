<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * PropelConfiguration is a container for all Propel's configuration data.
 *
 * PropelConfiguration implements ArrayAccess interface so the configuration
 * can be accessed as an array or using a simple getter and setter. The whole
 * configuration can also be retrieved as a nested arrays, flat array or as a
 * PropelConfiguration instance.
 *
 * @author     Veikko Mäkinen <veikko@veikko.fi>
 * @version    $Revision: 1612 $
 * @package    propel.runtime.config
 */
class PropelConfiguration implements ArrayAccess
{
	const TYPE_ARRAY = 1;

	const TYPE_ARRAY_FLAT = 2;

	const TYPE_OBJECT = 3;

	/**
	* @var        array An array of parameters
	*/
	protected $parameters = array();

	/**
	 * Construct a new configuration container
	 *
	 * @param      array $parameters
	 */
	public function __construct(array $parameters = array())
	{
		$this->parameters = $parameters;
	}

	/**
	 * @see        http://www.php.net/ArrayAccess
	 */
	public function offsetExists($offset)
	{
		return array_key_exists($offset, $this->parameters);
	}

	/**
	 * @see        http://www.php.net/ArrayAccess
	 */
	public function offsetSet($offset, $value)
	{
		$this->parameter[$offset] = $value;
	}

	/**
	 * @see        http://www.php.net/ArrayAccess
	 */
	public function offsetGet($offset)
	{
		return $this->parameters[$offset];
	}

	/**
	 * @see        http://www.php.net/ArrayAccess
	 */
	public function offsetUnset($offset)
	{
		unset($this->parameters[$offset]);
	}

	/**
	 * Get parameter value from the container
	 *
	 * @param      string $name    Parameter name
	 * @param      mixed  $default Default value to be used if the
	 *                             requested value is not found
	 * @return     mixed           Parameter value or the default
	 */
	public function getParameter($name, $default = null)
	{
		$ret = $this->parameters;
		$parts = explode('.', $name); //name.space.name
		while ($part = array_shift($parts)) {
			if (isset($ret[$part])) {
				$ret = $ret[$part];
			} else {
				return $default;
			}
		}
		return $ret;
	}

	/**
	 * Store a value to the container
	 *
	 * @param      string $name Configuration item name (name.space.name)
	 * @param      mixed $value Value to be stored
	 */
	public function setParameter($name, $value)
	{
		$param = &$this->parameters;
		$parts = explode('.', $name); //name.space.name
		while ($part = array_shift($parts)) {
			$param = &$param[$part];
		}
		$param = $value;
	}

	/**
	 *
	 *
	 * @param      int $type
	 * @return     mixed
	 */
	public function getParameters($type = PropelConfiguration::TYPE_ARRAY)
	{
		switch ($type) {
			case PropelConfiguration::TYPE_ARRAY:
				return $this->parameters;
			case PropelConfiguration::TYPE_ARRAY_FLAT:
				return $this->toFlatArray();
			case PropelConfiguration::TYPE_OBJECT:
				return $this;
			default:
				throw new PropelException('Unknown configuration type: '. var_export($type, true));
		}

	}


	/**
	 * Get the configuration as a flat array. ($array['name.space.item'] = 'value')
	 *
	 * @return     array
	 */
	protected function toFlatArray()
	{
		$result = array();
		$it = new PropelConfigurationIterator(new RecursiveArrayIterator($this->parameters), RecursiveIteratorIterator::SELF_FIRST);
		foreach($it as $key => $value) {
			$ns = $it->getDepth() ? $it->getNamespace() . '.'. $key : $key;
			if ($it->getNodeType() == PropelConfigurationIterator::NODE_ITEM) {
				$result[$ns] = $value;
			}
		}

		return $result;
	}

}

?>

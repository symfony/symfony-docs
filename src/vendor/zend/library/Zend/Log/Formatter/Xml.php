<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Log
 * @subpackage Formatter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @namespace
 */
namespace Zend\Log\Formatter;

use \Zend\Log\Formatter;

/**
 * @uses       DOMDocument
 * @uses       DOMElement
 * @uses       \Zend\Log\Formatter\FormatterInterface
 * @category   Zend
 * @package    Zend_Log
 * @subpackage Formatter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */
class Xml implements Formatter
{
    /**
     * @var Relates XML elements to log data field keys.
     */
    protected $_rootElement;

    /**
     * @var Relates XML elements to log data field keys.
     */
    protected $_elementMap;

    /**
     * @var string Encoding to use in XML
     */
    protected $_encoding;

    /**
     * Class constructor
     *
     * @param string $rootElement Name of root element
     * @param array $elementMap
     * @param string $encoding Encoding to use (defaults to UTF-8)
     */
    public function __construct($rootElement = 'logEntry', $elementMap = null, $encoding = 'UTF-8')
    {
        $this->_rootElement = $rootElement;
        $this->_elementMap  = $elementMap;
        $this->setEncoding($encoding);
    }

    /**
     * Get encoding
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->_encoding;
    }

    /**
     * Set encoding
     *
     * @param  string $value
     * @return \Zend\Log\Formatter\Xml
     */
    public function setEncoding($value)
    {
        $this->_encoding = (string) $value;
        return $this;
    }

    /**
     * Formats data into a single line to be written by the writer.
     *
     * @param  array    $event    event data
     * @return string             formatted line to write to the log
     */
    public function format($event)
    {
        if ($this->_elementMap === null) {
            $dataToInsert = $event;
        } else {
            $dataToInsert = array();
            foreach ($this->_elementMap as $elementName => $fieldKey) {
                $dataToInsert[$elementName] = $event[$fieldKey];
            }
        }

        $enc = $this->getEncoding();
        $dom = new \DOMDocument('1.0', $enc);
        $elt = $dom->appendChild(new \DOMElement($this->_rootElement));

        foreach ($dataToInsert as $key => $value) {
            if($key == "message") {
                $value = htmlspecialchars($value, ENT_COMPAT, $enc);
            }
            $elt->appendChild(new \DOMElement($key, $value));
        }

        $xml = $dom->saveXML();
        $xml = preg_replace('/<\?xml version="1.0"( encoding="[^\"]*")?\?>\n/u', '', $xml);

        return $xml . PHP_EOL;
    }
}

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
 * @subpackage Writer
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @namespace
 */
namespace Zend\Log\Writer;

use \Zend\Log\Factory,
    \Zend\Log\Writer;

/**
 * @uses       \Zend\Log\Exception
 * @uses       \Zend\Log\Factory
 * @uses       \Zend\Log\Filter\Priority
 * @category   Zend
 * @package    Zend_Log
 * @subpackage Writer
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */
abstract class AbstractWriter implements Writer, Factory
{
    /**
     * @var array of \Zend\Log\Filter
     */
    protected $_filters = array();

    /**
     * Formats the log message before writing.
     * @var \Zend\Log\Formatter
     */
    protected $_formatter;

    /**
     * Add a filter specific to this writer.
     *
     * @param  \Zend\Log\Filter  $filter
     * @return void
     */
    public function addFilter($filter)
    {
        if (is_integer($filter)) {
            $filter = new \Zend\Log\Filter\Priority($filter);
        }

        if (!$filter instanceof \Zend\Log\Filter) {
            throw new \Zend\Log\Exception('Invalid filter provided');
        }

        $this->_filters[] = $filter;
        return $this;
    }

    /**
     * Log a message to this writer.
     *
     * @param  array     $event  log data event
     * @return void
     */
    public function write($event)
    {
        foreach ($this->_filters as $filter) {
            if (! $filter->accept($event)) {
                return;
            }
        }

        // exception occurs on error
        $this->_write($event);
    }

    /**
     * Set a new formatter for this writer
     *
     * @param  \Zend\Log\Formatter $formatter
     * @return void
     */
    public function setFormatter(\Zend\Log\Formatter $formatter)
    {
        $this->_formatter = $formatter;
        return $this;
    }

    /**
     * Perform shutdown activites such as closing open resources
     *
     * @return void
     */
    public function shutdown()
    {}

    /**
     * Write a message to the log.
     *
     * @param  array  $event  log data event
     * @return void
     */
    abstract protected function _write($event);

    /**
     * Validate and optionally convert the config to array
     *
     * @param  array|\Zend\Config\Config $config \Zend\Config\Config or Array
     * @return array
     * @throws \Zend\Log\Exception
     */
    static protected function _parseConfig($config)
    {
        if ($config instanceof \Zend\Config\Config) {
            $config = $config->toArray();
        }

        if (!is_array($config)) {
            throw new \Zend\Log\Exception(
				'Configuration must be an array or instance of Zend\\Config\\Config'
			);
        }

        return $config;
    }
}

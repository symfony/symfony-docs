<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * PDO connection subclass that provides some basic support for query counting and logging.
 *
 * This class is ONLY intended for development use.  This class is also a work in-progress
 * and, as such, it should be expected that this class' API may change.
 * 
 * The following runtime configuration items affect the behaviour of this class:
 * 
 * - debugpdo.logging.enabled (default: true)
 *   Should any logging take place
 * 
 * - debugpdo.logging.innerglue (default: ": ")
 *   String to use for combining the title of a detail and its value
 * 
 * - debugpdo.logging.outerglue (default: " | ")
 *   String to use for combining details together on a log line
 * 
 * - debugpdo.logging.realmemoryusage (default: false)
 *   Parameter to memory_get_usage() and memory_get_peak_usage() calls 
 * 
 * - debugpdo.logging.methods (default: DebugPDO::$defaultLogMethods)
 *   An array of method names ("Class::method") to be included in method call logging
 * 
 * - debugpdo.logging.onlyslow (default: false)
 *   Suppress logging of non-slow queries.
 * 
 * - debugpdo.logging.details.slow.enabled (default: false)
 *   Enables flagging of slow method calls
 * 
 * - debugpdo.logging.details.slow.threshold (default: 0.1)
 *   Method calls taking more seconds than this threshold are considered slow 
 * 
 * - debugpdo.logging.details.time.enabled (default: false)
 *   Enables logging of method execution times
 * 
 * - debugpdo.logging.details.time.precision (default: 3)
 *   Determines the precision of the execution time logging
 * 
 * - debugpdo.logging.details.time.pad (default: 10)
 *   How much horizontal space to reserve for the execution time on a log line
 * 
 * - debugpdo.logging.details.mem.enabled (default: false)
 *   Enables logging of the instantaneous PHP memory consumption
 * 
 * - debugpdo.logging.details.mem.precision (default: 1)
 *   Determines the precision of the memory consumption logging
 * 
 * - debugpdo.logging.details.mem.pad (default: 9)
 *   How much horizontal space to reserve for the memory consumption on a log line
 * 
 * - debugpdo.logging.details.memdelta.enabled (default: false)
 *   Enables logging differences in memory consumption before and after the method call
 * 
 * - debugpdo.logging.details.memdelta.precision (default: 1)
 *   Determines the precision of the memory difference logging
 * 
 * - debugpdo.logging.details.memdelta.pad (default: 10)
 *   How much horizontal space to reserve for the memory difference on a log line
 * 
 * - debugpdo.logging.details.mempeak.enabled (default: false)
 *   Enables logging the peak memory consumption thus far by the currently executing PHP script
 * 
 * - debugpdo.logging.details.mempeak.precision (default: 1)
 *   Determines the precision of the memory peak logging
 * 
 * - debugpdo.logging.details.mempeak.pad (default: 9)
 *   How much horizontal space to reserve for the memory peak on a log line
 * 
 * - debugpdo.logging.details.querycount.enabled (default: false)
 *   Enables logging of the number of queries performed by the DebugPDO instance thus far
 * 
 * - debugpdo.logging.details.querycount.pad (default: 2)
 *   How much horizontal space to reserve for the query count on a log line
 * 
 * - debugpdo.logging.details.method.enabled (default: false)
 *   Enables logging of the name of the method call
 * 
 * - debugpdo.logging.details.method.pad (default: 28)
 *   How much horizontal space to reserve for the method name on a log line
 * 
 * The order in which the logging details are enabled is significant, since it determines the order in
 * which they will appear in the log file.
 * 
 * @example    // Enable simple query profiling, flagging calls taking over 1.5 seconds as slow:
 *             $config = Propel::getConfiguration(PropelConfiguration::TYPE_OBJECT);
 *             $config->setParameter('debugpdo.logging.details.slow.enabled', true);
 *             $config->setParameter('debugpdo.logging.details.slow.threshold', 1.5);
 *             $config->setParameter('debugpdo.logging.details.time.enabled', true);
 * 
 * @author     Francois Zaninotto
 * @author     Cameron Brunner <cameron.brunner@gmail.com>
 * @author     Hans Lellelid <hans@xmpl.org>
 * @author     Christian Abegg <abegg.ch@gmail.com>
 * @author     Jarno Rantanen <jarno.rantanen@tkk.fi>
 * @since      2006-09-22
 * @package    propel.runtime.connection
 */
class DebugPDO extends PropelPDO
{
  public $useDebug = true;
}

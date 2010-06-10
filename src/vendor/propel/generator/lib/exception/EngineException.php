<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

require_once 'phing/BuildException.php';

/**
 * The base class of all exceptions thrown by the engine.
 *
 * @author     Hans Lellelid <hans@xmpl.org> (Propel)
 * @author     Daniel Rall <dlr@collab.net> (Torque)
 * @author     Jason van Zyl <jvz@apache.org> (Torque)
 * @version    $Revision: 1612 $
 * @package    propel.generator.exception
 */
class EngineException extends BuildException {}

<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*
 * Autoloader and dependency injection initialization for Swift Mailer.
 */

//Load Swift utility class
require_once dirname(__FILE__) . '/Swift.php';

//Start the autoloader
Swift::registerAutoload();

//Load the init script to set up dependency injection
require_once dirname(__FILE__) . '/swift_init.php';

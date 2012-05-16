<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * The base class of all exceptions thrown by Propel.
 * @author     Hans Lellelid <hans@xmpl.org>
 * @version    $Revision: 1612 $
 * @package    propel.runtime.exception
 */
class PropelException extends Exception {

	/** The nested "cause" exception. */
	protected $cause;

	function __construct($p1, $p2 = null) {

		$cause = null;

		if ($p2 !== null) {
			$msg = $p1;
			$cause = $p2;
		} else {
			if ($p1 instanceof Exception) {
				$msg = "";
				$cause = $p1;
			} else {
				$msg = $p1;
			}
		}

		parent::__construct($msg);

		if ($cause !== null) {
			$this->cause = $cause;
			$this->message .= " [wrapped: " . $cause->getMessage() ."]";
		}
	}

	function getCause() {
		return $this->cause;
	}

}

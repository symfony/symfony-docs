<?php
/*
 *  $Id: OsCondition.php 43 2006-03-10 14:31:51Z mrook $
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://phing.info>.
 */

require_once 'phing/tasks/system/condition/ConditionBase.php';

/**
 *  Condition that tests the OS type.
 *
 *  @author    Andreas Aderhold <andi@binarycloud.com>
 *  @copyright � 2001,2002 THYRELL. All rights reserved
 *  @version   $Revision: 1.8 $ $Date: 2006-03-10 15:31:51 +0100 (Fri, 10 Mar 2006) $
 *  @access    public
 *  @package   phing.tasks.system.condition
 */
class OsCondition implements Condition {

    private $family;

    function setFamily($f) {
        $this->family = strtolower($f);
    }

    function evaluate() {
        $osName = strtolower(Phing::getProperty("os.name"));
		
        if ($this->family !== null) {
            if ($this->family === "windows") {
                return StringHelper::startsWith("win", $osName);
            } elseif ($this->family === "mac") {
                return (strpos($osName, "mac") !== false || strpos($osName, "darwin") !== false);
            } elseif ($this->family === ("unix")) {
				return (
					StringHelper::endsWith("ix", $osName) ||
					StringHelper::endsWith("ux", $osName) ||
					StringHelper::endsWith("bsd", $osName) ||
					StringHelper::startsWith("sunos", $osName) ||
					StringHelper::startsWith("darwin", $osName)
				);
            }
            throw new BuildException("Don't know how to detect os family '" . $this->family . "'");
        }
        return false;
    }

}

<?php

/*
 *  $Id: CreoleTask.php 59 2006-04-28 14:49:47Z mrook $
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

require_once 'phing/Task.php';
include_once 'phing/types/Reference.php';

/**
 * Handles PDO configuration needed by SQL type tasks.
 *
 * @author    Hans Lellelid <hans@xmpl.org> (Phing)
 * @author    Nick Chalko <nick@chalko.com> (Ant)
 * @author    Jeff Martin <jeff@custommonkey.org> (Ant)
 * @author    Michael McCallum <gholam@xtra.co.nz> (Ant)
 * @author    Tim Stephenson <tim.stephenson@sybase.com> (Ant)
 * @version   $Revision: 1.13 $
 * @package   phing.tasks.system
 */
abstract class PDOTask extends Task {

    private $caching = true;

    /**
     * Autocommit flag. Default value is false
     */
    private $autocommit = false;
    
    /**
     * DB url.
     */
    private $url;

    /**
     * User name.
     */
    private $userId;

    /**
     * Password
     */
    private $password;

    /**
     * RDBMS Product needed for this SQL.
     **/
    private $rdbms;
   
      /**
     * Initialize CreoleTask.
     * This method includes any necessary Creole libraries and triggers
     * appropriate error if they cannot be found.  This is not done in header
     * because we may want this class to be loaded w/o triggering an error.
     */
    function init() {
        if (!class_exists('PDO')) {
            throw new Exception("PDOTask depends on PDO feature being included in PHP.");
        }
    }

    /**
     * Caching loaders / driver. This is to avoid
     * getting an OutOfMemoryError when calling this task
     * multiple times in a row; default: true
     * @param $enable
     */
    public function setCaching($enable) {
        $this->caching = $enable;
    }

    /**
     * Sets the database connection URL; required.
     * @param url The url to set
     */
    public function setUrl($url) {
        $this->url = $url;
    }
        
    /**
     * Sets the password; required.
     * @param password The password to set
     */
    public function setPassword($password) {
        $this->password = $password;
    }

    /**
     * Auto commit flag for database connection;
     * optional, default false.
     * @param autocommit The autocommit to set
     */
    public function setAutocommit($autocommit) {
        $this->autocommit = $autocommit;
    }

    /**
     * Sets the version string, execute task only if 
     * rdbms version match; optional.
     * @param version The version to set
     */
    public function setVersion($version) {
        $this->version = $version;
    }
       
    protected function getLoaderMap() {
        return self::$loaderMap;
    }


    /**
     * Creates a new Connection as using the driver, url, userid and password specified.
     * The calling method is responsible for closing the connection.
     * @return Connection the newly created connection.
     * @throws BuildException if the UserId/Password/Url is not set or there is no suitable driver or the driver fails to load.
     */
    protected function getConnection() {
            
        if ($this->url === null) {
            throw new BuildException("Url attribute must be set!", $this->location);
        }
                
        try {

            $this->log("Connecting to " . $this->getUrl(), Project::MSG_VERBOSE);
            
            $user = null;
            $pass = null;
				
            if ($this->userId) {
            	$user = $this->getUserId();
            }
            
            if ($this->password) {
                $pass = $this->getPassword();
            }            
            
            $conn = new PDO($this->getUrl(), $user, $pass);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            if ($this->autocommit) {
            	try {
					$conn->setAttribute(PDO::ATTR_AUTOCOMMIT, $this->autocommit);
				} catch (PDOException $pe) {
					$this->log("Unable to enable auto-commit for this database: " . $pe->getMessage(), Project::MSG_WARN);
				}
			}
            
            return $conn;
            
        } catch (SQLException $e) {
            throw new BuildException($e->getMessage(), $this->location);
        }

    }

    public function isCaching($value) {
        $this->caching = $value;
    }

    /**
     * Gets the autocommit.
     * @return Returns a boolean
     */
    public function isAutocommit() {
        return $this->autocommit;
    }

    /**
     * Gets the url.
     * @return Returns a String
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * Gets the userId.
     * @return Returns a String
     */
    public function getUserId() {
        return $this->userId;
    }

    /**
     * Set the user name for the connection; required.
     * @param userId The userId to set
     */
    public function setUserid($userId) {
        $this->userId = $userId;
    }

    /**
     * Gets the password.
     * @return Returns a String
     */
    public function getPassword() {
        return $this->password;
    }

}

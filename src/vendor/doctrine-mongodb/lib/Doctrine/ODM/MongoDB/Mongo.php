<?php
/*
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
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\ODM\MongoDB;

/**
 * Wrapper for the PHP Mongo class.
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       1.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
class Mongo
{
    /** The PHP Mongo instance. */
    private $_mongo;

    /**
     * Create a new Mongo wrapper instance.
     *
     * @param mixed $server A string server name, an existing Mongo instance or can be omitted.
     * @param array $options 
     */
    public function __construct($server = null, array $options = array())
    {
        if ($server instanceof \Mongo) {
            $this->_mongo = $server;
        } elseif ($server !== null) {
            $this->_mongo = new \Mongo($server, $options);
        } else {
            $this->_mongo = new \Mongo();
        }
    }

    /**
     * Set the PHP Mongo instance to wrap.
     *
     * @param Mongo $mongo The PHP Mongo instance
     */
    public function setMongo(\Mongo $mongo)
    {
        $this->_mongo = $mongo;
    }

    /**
     * Returns the PHP Mongo instance being wrapped.
     *
     * @return Mongo
     */
    public function getMongo()
    {
        return $this->_mongo;
    }

    /** @proxy */
    public function __get($key)
    {
        return $this->_mongo->$key;
    }

    /** @proxy */
    public function __call($method, $arguments)
    {
        if (method_exists($this->_mongo, $method)) {
            return call_user_func_array(array($this->_mongo, $method), $arguments);
        }
        throw new \BadMethodCallException(sprintf('Method %s does not exist on %s', $method, get_class($this)));
    }
}
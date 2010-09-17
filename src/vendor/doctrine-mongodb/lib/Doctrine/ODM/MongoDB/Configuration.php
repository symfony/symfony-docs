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

use Doctrine\ODM\MongoDB\Mapping\Driver\Driver,
    Doctrine\ODM\MongoDB\Mapping\Driver\PHPDriver,
    Doctrine\Common\Cache\Cache;

/**
 * Configuration class for the DocumentManager. When setting up your DocumentManager
 * you can optionally specify an instance of this class as the second argument.
 * If you do not pass a configuration object, a blank one will be created for you.
 *
 *     <?php
 *
 *     $config = new Configuration();
 *     $dm = DocumentManager::create(new Mongo(), $config);
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.com
 * @since       1.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Roman Borschel <roman@code-factory.org>
 */
class Configuration
{
    /**
     * Array of attributes for this configuration instance.
     *
     * @var array $attributes
     */
    private $attributes = array('mongoCmd' => '$');

    /**
     * Adds a namespace under a certain alias.
     *
     * @param string $alias
     * @param string $namespace
     */
    public function addDocumentNamespace($alias, $namespace)
    {
        $this->attributes['documentNamespaces'][$alias] = $namespace;
    }

    /**
     * Resolves a registered namespace alias to the full namespace.
     *
     * @param string $documentNamespaceAlias 
     * @return string
     * @throws MongoDBException
     */
    public function getDocumentNamespace($documentNamespaceAlias)
    {
        if ( ! isset($this->attributes['documentNamespaces'][$documentNamespaceAlias])) {
            throw MongoDBException::unknownDocumentNamespace($documentNamespaceAlias);
        }

        return trim($this->attributes['documentNamespaces'][$documentNamespaceAlias], '\\');
    }

    /**
     * Set the document alias map
     *
     * @param array $documentAliasMap
     * @return void
     */
    public function setDocumentNamespaces(array $documentNamespaces)
    {
        $this->attributes['documentNamespaces'] = $documentNamespaces;
    }

    /**
     * Sets the cache driver implementation that is used for metadata caching.
     *
     * @param Driver $driverImpl
     * @todo Force parameter to be a Closure to ensure lazy evaluation
     *       (as soon as a metadata cache is in effect, the driver never needs to initialize).
     */
    public function setMetadataDriverImpl(Driver $driverImpl)
    {
        $this->attributes['metadataDriverImpl'] = $driverImpl;
    }

    /**
     * Add a new default annotation driver with a correctly configured annotation reader.
     * 
     * @param array $paths
     * @return Mapping\Driver\AnnotationDriver
     */
    public function newDefaultAnnotationDriver($paths = array())
    {
        $reader = new \Doctrine\Common\Annotations\AnnotationReader();
        $reader->setDefaultAnnotationNamespace('Doctrine\ODM\MongoDB\Mapping\\');
        
        return new \Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver($reader, (array) $paths);
    }

    /**
     * Gets the cache driver implementation that is used for the mapping metadata.
     *
     * @return Mapping\Driver\Driver
     */
    public function getMetadataDriverImpl()
    {
        return isset($this->attributes['metadataDriverImpl']) ?
            $this->attributes['metadataDriverImpl'] : null;
    }

    /**
     * Gets the cache driver implementation that is used for metadata caching.
     *
     * @return \Doctrine\Common\Cache\Cache
     */
    public function getMetadataCacheImpl()
    {
        return isset($this->attributes['metadataCacheImpl']) ?
                $this->attributes['metadataCacheImpl'] : null;
    }

    /**
     * Sets the cache driver implementation that is used for metadata caching.
     *
     * @param \Doctrine\Common\Cache\Cache $cacheImpl
     */
    public function setMetadataCacheImpl(Cache $cacheImpl)
    {
        $this->attributes['metadataCacheImpl'] = $cacheImpl;
    }

    /**
     * Sets the directory where Doctrine generates any necessary proxy class files.
     *
     * @param string $dir
     */
    public function setProxyDir($dir)
    {
        $this->attributes['proxyDir'] = $dir;
    }

    /**
     * Gets the directory where Doctrine generates any necessary proxy class files.
     *
     * @return string
     */
    public function getProxyDir()
    {
        return isset($this->attributes['proxyDir']) ?
                $this->attributes['proxyDir'] : null;
    }

    /**
     * Gets a boolean flag that indicates whether proxy classes should always be regenerated
     * during each script execution.
     *
     * @return boolean
     */
    public function getAutoGenerateProxyClasses()
    {
        return isset($this->attributes['autoGenerateProxyClasses']) ?
                $this->attributes['autoGenerateProxyClasses'] : true;
    }

    /**
     * Sets a boolean flag that indicates whether proxy classes should always be regenerated
     * during each script execution.
     *
     * @param boolean $bool
     */
    public function setAutoGenerateProxyClasses($bool)
    {
        $this->attributes['autoGenerateProxyClasses'] = $bool;
    }

    /**
     * Gets the namespace where proxy classes reside.
     * 
     * @return string
     */
    public function getProxyNamespace()
    {
        return isset($this->attributes['proxyNamespace']) ?
                $this->attributes['proxyNamespace'] : null;
    }

    /**
     * Sets the namespace where proxy classes reside.
     * 
     * @param string $ns
     */
    public function setProxyNamespace($ns)
    {
        $this->attributes['proxyNamespace'] = $ns;
    }

    /**
     * Sets the default DB to use for all Documents that do not specify
     * a database.
     *
     * @param string $defaultDB
     */
    public function setDefaultDB($defaultDB)
    {
        $this->attributes['defaultDB'] = $defaultDB;
    }

    /**
     * Gets the default DB to use for all Documents that do not specify a database.
     *
     * @return string $defaultDB
     */
    public function getDefaultDB()
    {
        return isset($this->attributes['defaultDB']) ?
            $this->attributes['defaultDB'] : null;
    }

    /**
     * Sets the environment
     *
     * @param string $environment
     */
    public function setEnvironment($environment)
    {
        $this->attributes['environment'] = $environment;
    }

    /**
     * Gets the environment
     *
     * @return string $environment
     */
    public function getEnvironment()
    {
        return isset($this->attributes['environment']) ?
            $this->attributes['environment'] : null;
    }

    /**
     * Gets prefix for environment
     *
     * @return string $envPrefix
     */
    public function getEnvironmentPrefix()
    {
        return isset($this->attributes['environment']) ?
            sprintf('%s_', $this->attributes['environment']) : null;
    }

    /**
     * Set the logger callable.
     *
     * @param mixed $loggerCallable The logger callable.
     */
    public function setLoggerCallable($loggerCallable)
    {
        $this->attributes['loggerCallable'] = $loggerCallable;
    }

    /**
     * Gets the logger callable.
     *
     * @return mixed $loggerCallable The logger callable.
     */
    public function getLoggerCallable()
    {
        return isset($this->attributes['loggerCallable']) ?
                $this->attributes['loggerCallable'] : null;
    }

    /**
     * Set prefix for db name
     *
     * @param string $prefix The prefix for names of databases
     */
    public function setDBPrefix($prefix = null)
    {
        $this->attributes['dbPrefix'] = $prefix;
    }

    /**
     * Get prefix for db name
     *
     * @return string 
     */
    public function getDBPrefix()
    {
        return isset($this->attributes['dbPrefix']) ?
            $this->attributes['dbPrefix'] : null;
    }

    /**
     * Set suffix for db name
     *
     * @param string $suffix The suffix for names of tables
     */
    public function setDBSuffix($suffix = null)
    {
        $this->attributes['dbSuffix'] = $suffix;
    }

    /**
     * Get suffix for db name
     *
     * @return string
     */
    public function getDBSuffix()
    {
        return isset($this->attributes['dbSuffix']) ?
            $this->attributes['dbSuffix'] : null;
    }

    /**
     * Get mongodb command prefix - '$' by default
     * @return string
     */
    public function getMongoCmd()
    {
        return $this->attributes['mongoCmd'];
    }

    /**
     * Set mongodb command prefix
     * @param string $cmd
     */
    public function setMongoCmd($cmd)
    {
        $this->attributes['mongoCmd'] = $cmd;
    }
}
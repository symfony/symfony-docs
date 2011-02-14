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

namespace Doctrine\ODM\MongoDB\Mapping;

use Doctrine\ODM\MongoDB\DocumentManager,
    Doctrine\ODM\MongoDB\Mapping\ClassMetadata,
    Doctrine\ODM\MongoDB\MongoDBException,
    Doctrine\ODM\MongoDB\ODMEvents,
    Doctrine\Common\Cache\Cache;

/**
 * The ClassMetadataFactory is used to create ClassMetadata objects that contain all the
 * metadata mapping informations of a class which describes how a class should be mapped
 * to a document database.
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.com
 * @since       1.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Roman Borschel <roman@code-factory.org>
 */
class ClassMetadataFactory
{
    /** The DocumentManager instance */
    private $_dm;

    /** The array of loaded ClassMetadata instances */
    private $_loadedMetadata;

    /** The used metadata driver. */
    private $_driver;

    /** The event manager instance */
    private $_evm;

    /** The used cache driver. */
    private $_cacheDriver;

    /** Whether factory has been lazily initialized yet */
    private $_initialized = false;

    /**
     * Creates a new factory instance that uses the given DocumentManager instance.
     *
     * @param $dm  The DocumentManager instance
     */
    public function __construct(DocumentManager $dm)
    {
        $this->_dm = $dm;
    }

    /**
     * Lazy initialization of this stuff, especially the metadata driver,
     * since these are not needed at all when a metadata cache is active.
     */
    private function _initialize()
    {
        $this->_driver = $this->_dm->getConfiguration()->getMetadataDriverImpl();
        $this->_evm = $this->_dm->getEventManager();
        $this->_initialized = true;
    }

    /**
     * Sets the cache driver used by the factory to cache ClassMetadata instances.
     *
     * @param Doctrine\Common\Cache\Cache $cacheDriver
     */
    public function setCacheDriver($cacheDriver)
    {
        $this->_cacheDriver = $cacheDriver;
    }

    /**
     * Gets the cache driver used by the factory to cache ClassMetadata instances.
     *
     * @return Doctrine\Common\Cache\Cache
     */
    public function getCacheDriver()
    {
        return $this->_cacheDriver;
    }

    /**
     * Gets the array of loaded ClassMetadata instances.
     *
     * @return array $loadedMetadata The loaded metadata.
     */
    public function getLoadedMetadata()
    {
        return $this->_loadedMetadata;
    }

    /**
     * Gets the class metadata descriptor for a class.
     *
     * @param string $className The name of the class.
     * @return Doctrine\ODM\MongoDB\Mapping\ClassMetadata
     */
    public function getMetadataFor($className)
    {
        if ( ! isset($this->_loadedMetadata[$className])) {
            $realClassName = $className;

            // Check for namespace alias
            if (strpos($className, ':') !== false) {
                list($namespaceAlias, $simpleClassName) = explode(':', $className);
                $realClassName = $this->_dm->getConfiguration()->getDocumentNamespace($namespaceAlias) . '\\' . $simpleClassName;

                if (isset($this->_loadedMetadata[$realClassName])) {
                    // We do not have the alias name in the map, include it
                    $this->_loadedMetadata[$className] = $this->_loadedMetadata[$realClassName];

                    return $this->_loadedMetadata[$realClassName];
                }
            }

            if ($this->_cacheDriver) {
                if (($cached = $this->_cacheDriver->fetch("$realClassName\$MONGODBODMCLASSMETADATA")) !== false) {
                    $this->_loadedMetadata[$realClassName] = $cached;
                } else {
                    foreach ($this->_loadMetadata($realClassName) as $loadedClassName) {
                        $this->_cacheDriver->save(
                            "$loadedClassName\$MONGODBODMCLASSMETADATA", $this->_loadedMetadata[$loadedClassName], null
                        );
                    }
                }
            } else {
                $this->_loadMetadata($realClassName);
            }

            if ($className != $realClassName) {
                // We do not have the alias name in the map, include it
                $this->_loadedMetadata[$className] = $this->_loadedMetadata[$realClassName];
            }
        }

        return $this->_loadedMetadata[$className];
    }

    /**
     * Loads the metadata of the class in question and all it's ancestors whose metadata
     * is still not loaded.
     *
     * @param string $name The name of the class for which the metadata should get loaded.
     * @param array  $tables The metadata collection to which the loaded metadata is added.
     */
    private function _loadMetadata($className)
    {
        if ( ! $this->_initialized) {
            $this->_initialize();
        }

        $loaded = array();

        $parentClasses = $this->_getParentClasses($className);
        $parentClasses[] = $className;

        // Move down the hierarchy of parent classes, starting from the topmost class
        $parent = null;
        $visited = array();
        foreach ($parentClasses as $className) {
            if (isset($this->_loadedMetadata[$className])) {
                $parent = $this->_loadedMetadata[$className];
                if ( ! $parent->isMappedSuperclass) {
                    array_unshift($visited, $className);
                }
                continue;
            }

            $class = $this->_newClassMetadataInstance($className);

            if ($parent) {
                $class->setInheritanceType($parent->inheritanceType);
                $class->setDiscriminatorField($parent->discriminatorField);
                $this->_addInheritedFields($class, $parent);
                $class->setIdentifier($parent->identifier);
                $class->setDiscriminatorMap($parent->discriminatorMap);
            }

            $this->_driver->loadMetadataForClass($className, $class);

            if ($parent && $parent->isInheritanceTypeSingleCollection()) {
                $class->setDB($parent->getDB());
                $class->setCollection($parent->getCollection());
            }

            if ( ! $class->identifier) {
                $class->mapField(array(
                    'id' => true,
                    'fieldName' => 'id'
                ));
            }
            $db = $class->getDB() ?: $this->_dm->getConfiguration()->getDefaultDB();
            $class->setDB($this->_dm->formatDBName($db));

            $class->setParentClasses($visited);

            if ($this->_evm->hasListeners(ODMEvents::loadClassMetadata)) {
                $eventArgs = new \Doctrine\ODM\MongoDB\Event\LoadClassMetadataEventArgs($class);
                $this->_evm->dispatchEvent(ODMEvents::loadClassMetadata, $eventArgs);
            }

            $this->_loadedMetadata[$className] = $class;

            $parent = $class;

            if ( ! $class->isMappedSuperclass) {
                array_unshift($visited, $className);
            }

            $loaded[] = $className;
        }

        return $loaded;
    }

    /**
     * Checks whether the factory has the metadata for a class loaded already.
     * 
     * @param string $className
     * @return boolean TRUE if the metadata of the class in question is already loaded, FALSE otherwise.
     */
    public function hasMetadataFor($className)
    {
        return isset($this->_loadedMetadata[$className]);
    }

    /**
     * Sets the metadata descriptor for a specific class.
     * 
     * NOTE: This is only useful in very special cases, like when generating proxy classes.
     *
     * @param string $className
     * @param ClassMetadata $class
     */
    public function setMetadataFor($className, $class)
    {
        $this->_loadedMetadata[$className] = $class;
    }

    /**
     * Creates a new ClassMetadata instance for the given class name.
     *
     * @param string $className
     * @return Doctrine\ODM\MongoDB\Mapping\ClassMetadata
     */
    protected function _newClassMetadataInstance($className)
    {
        return new ClassMetadata($className);
    }

    /**
     * Get array of parent classes for the given document class
     *
     * @param string $name
     * @return array $parentClasses
     */
    protected function _getParentClasses($name)
    {
        // Collect parent classes, ignoring transient (not-mapped) classes.
        $parentClasses = array();
        foreach (array_reverse(class_parents($name)) as $parentClass) {
            $parentClasses[] = $parentClass;
        }
        return $parentClasses;
    }

    /**
     * Adds inherited fields to the subclass mapping.
     *
     * @param Doctrine\ODM\MongoDB\Mapping\ClassMetadata $subClass
     * @param Doctrine\ODM\MongoDB\Mapping\ClassMetadata $parentClass
     */
    private function _addInheritedFields(ClassMetadata $subClass, ClassMetadata $parentClass)
    {
        foreach ($parentClass->fieldMappings as $fieldName => $mapping) {
            if ( ! isset($mapping['inherited'])) {
                $mapping['inherited'] = $parentClass->name;
            }
            if ( ! isset($mapping['declared'])) {
                $mapping['declared'] = $parentClass->name;
            }
            $subClass->mapField($mapping);
        }
        foreach ($parentClass->reflFields as $name => $field) {
            $subClass->reflFields[$name] = $field;
        }
    }
}
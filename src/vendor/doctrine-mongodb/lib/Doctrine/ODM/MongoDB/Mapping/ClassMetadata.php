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

/**
 * A <tt>ClassMetadata</tt> instance holds all the object-document mapping metadata
 * of a document and it's references.
 * 
 * Once populated, ClassMetadata instances are usually cached in a serialized form.
 *
 * <b>IMPORTANT NOTE:</b>
 *
 * The fields of this class are only public for 2 reasons:
 * 1) To allow fast READ access.
 * 2) To drastically reduce the size of a serialized instance (private/protected members
 *    get the whole class name, namespace inclusive, prepended to every property in
 *    the serialized representation).
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.com
 * @since       1.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Roman Borschel <roman@code-factory.org>
 */
class ClassMetadata
{
    /* The inheritance mapping types */
    /**
     * NONE means the class does not participate in an inheritance hierarchy
     * and therefore does not need an inheritance mapping type.
     */
    const INHERITANCE_TYPE_NONE = 1;

    /**
     * SINGLE_COLLECTION means the class will be persisted according to the rules of
     * <tt>Single Collection Inheritance</tt>.
     */
    const INHERITANCE_TYPE_SINGLE_COLLECTION = 2;

    /**
     * COLLECTION_PER_CLASS means the class will be persisted according to the rules
     * of <tt>Concrete Collection Inheritance</tt>.
     */
    const INHERITANCE_TYPE_COLLECTION_PER_CLASS = 3;

    /**
     * READ-ONLY: The name of the mongo database the document is mapped to.
     */
    public $db;

    /**
     * READ-ONLY: The name of the monge collection the document is mapped to.
     */
    public $collection;

    /**
     * READ-ONLY: The field name of the document identifier.
     */
    public $identifier;

    /**
     * READ-ONLY: The field that stores a file reference and indicates the 
     * document is a file and should be stored on the MongoGridFS.
     */
    public $file;

    /**
     * READ-ONLY: The array of indexes for the document collection.
     */
    public $indexes = array();

    /**
     * READ-ONLY: The name of the document class.
     */
    public $name;

    /**
     * READ-ONLY: The namespace the document class is contained in.
     *
     * @var string
     * @todo Not really needed. Usage could be localized.
     */
    public $namespace;

    /**
     * READ-ONLY: The name of the document class that is at the root of the mapped document inheritance
     * hierarchy. If the document is not part of a mapped inheritance hierarchy this is the same
     * as {@link $documentName}.
     *
     * @var string
     */
    public $rootDocumentName;

    /**
     * The name of the custom repository class used for the document class.
     * (Optional).
     *
     * @var string
     */
    public $customRepositoryClassName;

    /**
     * Whether custom id value is allowed or not
     * 
     * @var bool
     */
    public $allowCustomID = false;

    /**
     * READ-ONLY: The names of the parent classes (ancestors).
     *
     * @var array
     */
    public $parentClasses = array();

    /**
     * READ-ONLY: The names of all subclasses (descendants).
     *
     * @var array
     */
    public $subClasses = array();

    /**
     * The ReflectionProperty instances of the mapped class.
     *
     * @var array
     */
    public $reflFields = array();
    
    /**
     * The prototype from which new instances of the mapped class are created.
     * 
     * @var object
     */
    private $_prototype;

    /**
     * READ-ONLY: The inheritance mapping type used by the class.
     *
     * @var integer
     */
    public $inheritanceType = self::INHERITANCE_TYPE_NONE;

    /**
     * READ-ONLY: The field mappings of the class.
     * Keys are field names and values are mapping definitions.
     *
     * The mapping definition array has the following values:
     *
     * - <b>fieldName</b> (string)
     * The name of the field in the Document.
     *
     * - <b>id</b> (boolean, optional)
     * Marks the field as the primary key of the document. Multiple fields of an
     * document can have the id attribute, forming a composite key.
     *
     * @var array
     */
    public $fieldMappings = array();

    /**
     * READ-ONLY: The discriminator value of this class.
     *
     * <b>This does only apply to the JOINED and SINGLE_COLLECTION inheritance mapping strategies
     * where a discriminator field is used.</b>
     *
     * @var mixed
     * @see discriminatorField
     */
    public $discriminatorValue;

    /**
     * READ-ONLY: The discriminator map of all mapped classes in the hierarchy.
     *
     * <b>This does only apply to the SINGLE_COLLECTION inheritance mapping strategy
     * where a discriminator field is used.</b>
     *
     * @var mixed
     * @see discriminatorField
     */
    public $discriminatorMap = array();

    /**
     * READ-ONLY: The definition of the discriminator field used in SINGLE_COLLECTION
     * inheritance mapping.
     *
     * @var string
     */
    public $discriminatorField;

    /**
     * The ReflectionClass instance of the mapped class.
     *
     * @var ReflectionClass
     */
    public $reflClass;

    /**
     * READ-ONLY: Whether this class describes the mapping of a mapped superclass.
     *
     * @var boolean
     */
    public $isMappedSuperclass = false;

    /**
     * READ-ONLY: Whether this class describes the mapping of a embedded document.
     *
     * @var boolean
     */
    public $isEmbeddedDocument = false;

    /**
     * Initializes a new ClassMetadata instance that will hold the object-document mapping
     * metadata of the class with the given name.
     *
     * @param string $documentName The name of the document class the new instance is used for.
     */
    public function __construct($documentName)
    {
        $this->name = $documentName;
        $this->rootDocumentName = $documentName;
        $this->reflClass = new \ReflectionClass($documentName);
        $this->namespace = $this->reflClass->getNamespaceName();

        $e = explode('\\', $documentName);
        if (count($e) > 1) {
            $e = array_map(function($value) {
                return strtolower($value);
            }, $e);
            $collection = array_pop($e);
        } else {
            $collection = strtolower($documentName);
        }
        $this->setCollection($collection);

        foreach ($this->reflClass->getProperties() as $property) {
            $fieldName = $property->getName();
            $mapping = array(
                'fieldName' => $fieldName
            );
            $this->mapField($mapping);
        }
    }

    /**
     * Checks whether a field is part of the identifier/primary key field(s).
     *
     * @param string $fieldName  The field name
     * @return boolean  TRUE if the field is part of the table identifier/primary key field(s),
     *                  FALSE otherwise.
     */
    public function isIdentifier($fieldName)
    {
        return $this->identifier === $fieldName ? true : false;
    }

    /**
     * INTERNAL:
     * Sets the mapped identifier field of this class.
     *
     * @param array $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * Checks whether the class has a (mapped) field with a certain name.
     *
     * @return boolean
     */
    public function hasField($fieldName)
    {
        return isset($this->fieldMappings[$fieldName]);
    }

    /**
     * Sets the inheritance type used by the class and it's subclasses.
     *
     * @param integer $type
     */
    public function setInheritanceType($type)
    {
        $this->inheritanceType = $type;
    }

    /**
     * Registers a custom repository class for the document class.
     *
     * @param string $mapperClassName  The class name of the custom mapper.
     */
    public function setCustomRepositoryClass($repositoryClassName)
    {
        $this->customRepositoryClassName = $repositoryClassName;
    }

    /**
     * Dispatches the lifecycle event of the given document to the registered
     * lifecycle callbacks and lifecycle listeners.
     *
     * @param string $event The lifecycle event.
     * @param Document $document The Document on which the event occured.
     */
    public function invokeLifecycleCallbacks($lifecycleEvent, $document)
    {
        foreach ($this->lifecycleCallbacks[$lifecycleEvent] as $callback) {
            $document->$callback();
        }
    }

    /**
     * Whether the class has any attached lifecycle listeners or callbacks for a lifecycle event.
     *
     * @param string $lifecycleEvent
     * @return boolean
     */
    public function hasLifecycleCallbacks($lifecycleEvent)
    {
        return isset($this->lifecycleCallbacks[$lifecycleEvent]);
    }

    /**
     * Gets the registered lifecycle callbacks for an event.
     *
     * @param string $event
     * @return array
     */
    public function getLifecycleCallbacks($event)
    {
        return isset($this->lifecycleCallbacks[$event]) ? $this->lifecycleCallbacks[$event] : array();
    }

    /**
     * Adds a lifecycle callback for documents of this class.
     *
     * Note: If the same callback is registered more than once, the old one
     * will be overridden.
     *
     * @param string $callback
     * @param string $event
     */
    public function addLifecycleCallback($callback, $event)
    {
        $this->lifecycleCallbacks[$event][] = $callback;
    }

    /**
     * Sets the lifecycle callbacks for documents of this class.
     * Any previously registered callbacks are overwritten.
     *
     * @param array $callbacks
     */
    public function setLifecycleCallbacks(array $callbacks)
    {
        $this->lifecycleCallbacks = $callbacks;
    }

    /**
     * Sets the discriminator field name.
     *
     * @param string $discriminatorField
     * @see getDiscriminatorField()
     */
    public function setDiscriminatorField($discriminatorField)
    {
        if ( ! isset($discriminatorField['name']) && isset($discriminatorField['fieldName'])) {
            $discriminatorField['name'] = $discriminatorField['fieldName'];
        }
        $this->discriminatorField = $discriminatorField;
    }

    /**
     * Sets the discriminator values used by this class.
     * Used for JOINED and SINGLE_TABLE inheritance mapping strategies.
     *
     * @param array $map
     */
    public function setDiscriminatorMap(array $map)
    {
        foreach ($map as $value => $className) {
            if (strpos($className, '\\') === false && strlen($this->namespace)) {
                $className = $this->namespace . '\\' . $className;
            }
            $this->discriminatorMap[$value] = $className;
            if ($this->name == $className) {
                $this->discriminatorValue = $value;
            } else {
                if (is_subclass_of($className, $this->name)) {
                    $this->subClasses[] = $className;
                }
            }
        }
    }

    /**
     * Add a index for this Document.
     *
     * @param array $keys Array of keys for the index.
     * @param array $options Array of options for the index.
     */
    public function addIndex($keys, $options)
    {
        $this->indexes[] = array(
            'keys' => array_map(function($value) {
                return strtolower($value) == 'asc' ? 1 : -1;
            }, $keys),
            'options' => $options
        );
    }

    /**
     * Returns the array of indexes for this Document.
     *
     * @return array $indexes The array of indexes.
     */
    public function getIndexes()
    {
        return $this->indexes;
    }

    /**
     * Gets the ReflectionClass instance of the mapped class.
     *
     * @return ReflectionClass
     */
    public function getReflectionClass()
    {
        return $this->reflClass;
    }

    /**
     * Gets the ReflectionPropertys of the mapped class.
     *
     * @return array An array of ReflectionProperty instances.
     */
    public function getReflectionProperties()
    {
        return $this->reflFields;
    }

    /**
     * Gets a ReflectionProperty for a specific field of the mapped class.
     *
     * @param string $name
     * @return ReflectionProperty
     */
    public function getReflectionProperty($name)
    {
        return $this->reflFields[$name];
    }

    /**
     * The name of this Document class.
     *
     * @return string $name The Document class name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * The namespace this Document class belongs to.
     *
     * @return string $namespace The namespace name.
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Returns the database this Document is mapped to.
     *
     * @return string $db The database name.
     */
    public function getDB()
    {
        return $this->db;
    }

    /**
     * Set the database this Document is mapped to.
     *
     * @param string $db The database name
     */
    public function setDB($db)
    {
        $this->db = $db;
    }

    /**
     * Get the collection this Document is mapped to.
     *
     * @return string $collection The collection name.
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * Sets the collection this Document is mapped to.
     *
     * @param string $collection The collection name.
     */
    public function setCollection($collection)
    {
        $this->collection = $collection;
    }

    /**
     * Reeturns TRUE if this Document is mapped to a collection FALSE otherwise.
     *
     * @return boolean
     */
    public function isMappedToCollection()
    {
        return $this->collection ? true : false;
    }

    /**
     * Returns TRUE if this Document is a file to be stored on the MongoGridFS FALSE otherwise.
     *
     * @return boolean
     */
    public function isFile()
    {
        return $this->file ? true :false;
    }

    /**
     * Returns the file field name.
     *
     * @return string $file The file field name.
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Map a field.
     *
     * @param array $mapping The mapping information.
     */
    public function mapField(array $mapping)
    {
        if (isset($mapping['name'])) {
            $mapping['fieldName'] = $mapping['name'];
        }
        $mapping['name'] = $mapping['fieldName'];

        // unset transient fields
        if (isset($mapping['transient']) && $mapping['transient']) {
            unset($this->fieldMappings[$mapping['fieldName']]);
            return;
        }

        if ($mapping['fieldName'] === 'id') {
            $mapping['id'] = true;
            $mapping['type'] = isset($mapping['type']) ? $mapping['type'] : 'id';
        }

        if ( ! isset($mapping['type'])) {
            $mapping['type'] = 'string';
        }

        if (isset($mapping['targetDocument']) && strpos($mapping['targetDocument'], '\\') === false && strlen($this->namespace)) {
            $mapping['targetDocument'] = $this->namespace . '\\' . $mapping['targetDocument'];
        }

        if ($this->reflClass->hasProperty($mapping['fieldName'])) {
            $reflProp = $this->reflClass->getProperty($mapping['fieldName']);
            $reflProp->setAccessible(true);
            $this->reflFields[$mapping['fieldName']] = $reflProp;
        }

        if (isset($mapping['cascade']) && in_array('all', (array) $mapping['cascade'])) {
            unset($mapping['all']);
            $default = true;
        } else {
            $default = false;
        }
        $mapping['isCascadeRemove'] = $default;
        $mapping['isCascadePersist'] = $default;
        $mapping['isCascadeRefresh'] = $default;
        $mapping['isCascadeMerge'] = $default;
        $mapping['isCascadeDetach'] = $default;
        if (isset($mapping['cascade']) && is_array($mapping['cascade'])) {
            foreach ($mapping['cascade'] as $cascade) {
                $mapping['isCascade' . ucfirst($cascade)] = true;
            }
        }
        if (isset($mapping['file']) && $mapping['file'] === true) {
            $this->file = $mapping['fieldName'];
        }
        if (isset($mapping['id']) && $mapping['id'] === true) {
            $this->identifier = $mapping['fieldName'];
        }
        if ( ! isset($mapping['nullable'])) {
            $mapping['nullable'] = false;
        }
        $this->fieldMappings[$mapping['fieldName']] = $mapping;
    }

    /**
     * Map a MongoGridFSFile.
     *
     * @param array $mapping The mapping information.
     */
    public function mapFile(array $mapping)
    {
        $mapping['file'] = true;
        $mapping['type'] = 'file';
        $this->mapField($mapping);
    }

    /**
     * Map a single embedded document.
     *
     * @param array $mapping The mapping information.
     */
    public function mapOneEmbedded(array $mapping)
    {
        $mapping['embedded'] = true;
        $mapping['type'] = 'one';
        $this->mapField($mapping);
    }

    /**
     * Map a collection of embedded documents.
     *
     * @param array $mapping The mapping information.
     */
    public function mapManyEmbedded(array $mapping)
    {
        $mapping['embedded'] = true;
        $mapping['type'] = 'many';
        $this->mapField($mapping);
    }

    /**
     * Map a single document reference.
     *
     * @param array $mapping The mapping information.
     */
    public function mapOneReference(array $mapping)
    {
        $mapping['reference'] = true;
        $mapping['type'] = 'one';
        $this->mapField($mapping);
    }

    /**
     * Map a collection of document references.
     *
     * @param array $mapping The mapping information.
     */
    public function mapManyReference(array $mapping)
    {
        $mapping['reference'] = true;
        $mapping['type'] = 'many';
        $this->mapField($mapping);
    }

    /**
     * Checks whether the class has a mapped association with the given field name.
     *
     * @param string $fieldName
     * @return boolean
     */
    public function hasReference($fieldName)
    {
        return isset($this->fieldMappings[$fieldName]['reference']);
    }

    /**
     * Checks whether the class has a mapped association for the specified field
     * and if yes, checks whether it is a single-valued association (to-one).
     *
     * @param string $fieldName
     * @return boolean TRUE if the association exists and is single-valued, FALSE otherwise.
     */
    public function isSingleValuedReference($fieldName)
    {
        return isset($this->fieldMappings[$fieldName]['reference']) &&
                $this->fieldMappings[$fieldName]['type'] === 'one';
    }

    /**
     * Checks whether the class has a mapped association for the specified field
     * and if yes, checks whether it is a collection-valued association (to-many).
     *
     * @param string $fieldName
     * @return boolean TRUE if the association exists and is collection-valued, FALSE otherwise.
     */
    public function isCollectionValuedReference($fieldName)
    {
        return isset($this->fieldMappings[$fieldName]['reference']) &&
                $this->fieldMappings[$fieldName]['type'] === 'many';
    }

    public function getPHPIdentifierValue($id)
    {
        $idType = $this->fieldMappings[$this->identifier]['type'];
        return Types\Type::getType($idType)->convertToPHPValue($id);
    }

    public function getDatabaseIdentifierValue($id)
    {
        $idType = $this->fieldMappings[$this->identifier]['type'];
        return Types\Type::getType($idType)->convertToDatabaseValue($id);
    }

    /**
     * Sets the document identifier of a document.
     *
     * @param object $document
     * @param mixed $id
     */
    public function setIdentifierValue($document, $id)
    {
        $id = $this->getPHPIdentifierValue($id);
        if (isset($this->reflFields[$this->identifier])) {
            $this->reflFields[$this->identifier]->setValue($document, $id);
        } else {
            $identifier = $this->identifier;
            $document->$identifier = $id;
        }
    }

    /**
     * Gets the document identifier.
     *
     * @param object $document
     * @return string $id
     */
    public function getIdentifierValue($document)
    {
        if (isset($this->reflFields[$this->identifier])) {
            return (string) $this->reflFields[$this->identifier]->getValue($document);
        } else {
            $identifier = $this->identifier;
            return isset($document->$identifier) ? (string) $document->identifier : null;
        }
    }

    /**
     * Get the document identifier object.
     *
     * @param string $document
     * @return MongoId $id  The MongoID object.
     */
    public function getIdentifierObject($document)
    {
        if ($id = $this->getIdentifierValue($document)) {
            return $this->getDatabaseIdentifierValue($id);
        }
    }

    /**
     * Sets the specified field to the specified value on the given document.
     *
     * @param object $document
     * @param string $field
     * @param mixed $value
     */
    public function setFieldValue($document, $field, $value)
    {
        if (isset($this->reflFields[$field])) {
            $this->reflFields[$field]->setValue($document, $value);
        } else {
            $document->$field = $value;
        }
    }

    /**
     * Gets the specified field's value off the given document.
     *
     * @param object $document
     * @param string $field
     */
    public function getFieldValue($document, $field)
    {
        if (isset($this->reflFields[$field])) {
            return $this->reflFields[$field]->getValue($document);
        } else {
            return isset($document->$field) ? $document->$field : null;
        }
    }

    /**
     * @return boolean
     */
    public function isInheritanceTypeNone()
    {
        return $this->inheritanceType == self::INHERITANCE_TYPE_NONE;
    }

    /**
     * Checks whether the mapped class uses the SINGLE_COLLECTION inheritance mapping strategy.
     *
     * @return boolean TRUE if the class participates in a SINGLE_COLLECTION inheritance mapping,
     *                 FALSE otherwise.
     */
    public function isInheritanceTypeSingleCollection()
    {
        return $this->inheritanceType == self::INHERITANCE_TYPE_SINGLE_COLLECTION;
    }

    /**
     * Checks whether the mapped class uses the COLLECTION_PER_CLASS inheritance mapping strategy.
     *
     * @return boolean TRUE if the class participates in a COLLECTION_PER_CLASS inheritance mapping,
     *                 FALSE otherwise.
     */
    public function isInheritanceTypeCollectionPerClass()
    {
        return $this->inheritanceType == self::INHERITANCE_TYPE_COLLECTION_PER_CLASS;
    }

    /**
     * Sets the parent class names.
     * Assumes that the class names in the passed array are in the order:
     * directParent -> directParentParent -> directParentParentParent ... -> root.
     */
    public function setParentClasses(array $classNames)
    {
        $this->parentClasses = $classNames;
        if (count($classNames) > 0) {
            $this->rootDocumentName = array_pop($classNames);
        }
    }

    public function setAllowCustomId($boolean)
    {
        $this->allowCustomID = (bool) $boolean;
    }

    public function getAllowCustomID()
    {
        return $this->allowCustomID;
    }

    /**
     * Creates a new instance of the mapped class, without invoking the constructor.
     * 
     * @return object
     */
    public function newInstance()
    {
        if ($this->_prototype === null) {
            $this->_prototype = unserialize(sprintf('O:%d:"%s":0:{}', strlen($this->name), $this->name));
        }
        return clone $this->_prototype;
    }

    /**
     * Determines which fields get serialized.
     *
     * It is only serialized what is necessary for best unserialization performance.
     * That means any metadata properties that are not set or empty or simply have
     * their default value are NOT serialized.
     * 
     * Parts that are also NOT serialized because they can not be properly unserialized:
     *      - reflClass (ReflectionClass)
     *      - reflFields (ReflectionProperty array)
     * 
     * @return array The names of all the fields that should be serialized.
     */
    public function __sleep()
    {
        // This metadata is always serialized/cached.
        $serialized = array(
            'fieldMappings',
            'identifier',
            'name',
            'namespace', // TODO: REMOVE
            'db',
            'collection',
            'rootDocumentName',
            'allowCustomID',
        );

        if ($this->inheritanceType != self::INHERITANCE_TYPE_NONE) {
            $serialized[] = 'inheritanceType';
            $serialized[] = 'discriminatorField';
            $serialized[] = 'discriminatorValue';
            $serialized[] = 'discriminatorMap';
            $serialized[] = 'parentClasses';
            $serialized[] = 'subClasses';
        }

        if ($this->isMappedSuperclass) {
            $serialized[] = 'isMappedSuperclass';
        }

        return $serialized;

    }

    /**
     * Restores some state that can not be serialized/unserialized.
     * 
     * @return void
     */
    public function __wakeup()
    {
        $this->reflClass = new \ReflectionClass($this->name);

        foreach ($this->fieldMappings as $field => $mapping) {
            $reflField = $this->reflClass->getProperty($field);
            $reflField->setAccessible(true);
            $this->reflFields[$field] = $reflField;
        }
    }
}

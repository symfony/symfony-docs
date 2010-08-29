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

use Doctrine\ODM\MongoDB\Mapping\ClassMetadata,
    Doctrine\ODM\MongoDB\Mapping\ClassMetadataFactory,
    Doctrine\ODM\MongoDB\Mapping\Driver\PHPDriver,
    Doctrine\ODM\MongoDB\Query,
    Doctrine\ODM\MongoDB\Mongo,
    Doctrine\ODM\MongoDB\PersistentCollection,
    Doctrine\ODM\MongoDB\Proxy\ProxyFactory,
    Doctrine\ODM\MongoDB\Query\Parser,
    Doctrine\Common\Collections\ArrayCollection,
    Doctrine\Common\EventManager;

/**
 * The DocumentManager class is the central access point for managing the
 * persistence of documents.
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
class DocumentManager
{
    /**
     * The Doctrine Mongo wrapper instance
     *
     * @var Doctrine\ODM\MongoDB\Mongo
     */
    private $mongo;

    /**
     * The used Configuration.
     *
     * @var Doctrine\ODM\MongoDB\Configuration
     */
    private $config;

    /**
     * The metadata factory, used to retrieve the ORM metadata of document classes.
     *
     * @var Doctrine\ODM\MongoDB\Mapping\ClassMetadataFactory
     */
    private $metadataFactory;

    /**
     * The DocumentRepository instances.
     *
     * @var array
     */
    private $repositories = array();

    /**
     * The UnitOfWork used to coordinate object-level transactions.
     *
     * @var Doctrine\ODM\MongoDB\UnitOfWork
     */
    private $unitOfWork;

    /**
     * The event manager that is the central point of the event system.
     *
     * @var Doctrine\Common\EventManager
     */
    private $eventManager;

    /**
     * The Document hydrator instance.
     *
     * @var Doctrine\ODM\MongoDB\Hydrator
     */
    private $hydrator;

    /**
     * Array of cached MongoDB instances that are lazily loaded.
     *
     * @var array
     */
    private $documentDBs = array();

    /**
     * Array of cached MongoCollection instances that are lazily loaded.
     *
     * @var array
     */
    private $documentCollections = array();

    /**
     * The Query\Parser instance for parsing string based queries.
     *
     * @var Query\Parser $parser
     */
    private $queryParser;

    /**
     * Whether the DocumentManager is closed or not.
     */
    private $closed = false;

    /**
     * Creates a new Document that operates on the given Mongo connection
     * and uses the given Configuration.
     *
     * @param Doctrine\ODM\MongoDB\Mongo $mongo
     * @param Doctrine\ODM\MongoDB\Configuration $config
     * @param Doctrine\Common\EventManager $eventManager
     */
    protected function __construct(Mongo $mongo = null, Configuration $config = null, EventManager $eventManager = null)
    {
        if (is_string($mongo) || $mongo instanceof \Mongo) {
            $mongo = new Mongo($mongo);
        }
        $this->mongo = $mongo ? $mongo : new Mongo();
        $this->config = $config ? $config : new Configuration();
        $this->eventManager = $eventManager ? $eventManager : new EventManager();
        $this->hydrator = new Hydrator($this);
        $this->metadataFactory = new ClassMetadataFactory($this);
        if ($cacheDriver = $this->config->getMetadataCacheImpl()) {
            $this->metadataFactory->setCacheDriver($cacheDriver);
        }
        $this->queryParser = new Parser($this);
        $this->unitOfWork = new UnitOfWork($this);
        $this->proxyFactory = new ProxyFactory($this,
                $this->config->getProxyDir(),
                $this->config->getProxyNamespace(),
                $this->config->getAutoGenerateProxyClasses());
    }

    /**
     * Gets the proxy factory used by the DocumentManager to create document proxies.
     *
     * @return ProxyFactory
     */
    public function getProxyFactory()
    {
        return $this->proxyFactory;
    }

    /**
     * Creates a new Document that operates on the given Mongo connection
     * and uses the given Configuration.
     *
     * @param Doctrine\ODM\MongoDB\Mongo $mongo
     * @param Doctrine\ODM\MongoDB\Configuration $config
     * @param Doctrine\Common\EventManager $eventManager
     */
    public static function create(Mongo $mongo, Configuration $config = null, EventManager $eventManager = null)
    {
        return new DocumentManager($mongo, $config, $eventManager);
    }

    /**
     * Determines whether an document instance is managed in this DocumentManager.
     *
     * @param object $document
     * @return boolean TRUE if this DocumentManager currently manages the given document, FALSE otherwise.
     */
    public function contains($document)
    {
        return $this->unitOfWork->isScheduledForInsert($document) ||
               $this->unitOfWork->isInIdentityMap($document) &&
               ! $this->unitOfWork->isScheduledForDelete($document);
    }

    /**
     * Gets the EventManager used by the DocumentManager.
     *
     * @return Doctrine\Common\EventManager
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }

    public function getConfiguration()
    {
        return $this->config;
    }

    public function getMongo()
    {
        return $this->mongo;
    }

    /**
     * Gets the metadata factory used to gather the metadata of classes.
     *
     * @return Doctrine\ODM\MongoDB\Mapping\ClassMetadataFactory
     */
    public function getMetadataFactory()
    {
        return $this->metadataFactory;
    }

    /**
     * Gets the UnitOfWork used by the DocumentManager to coordinate operations.
     *
     * @return Doctrine\ODM\MongoDB\UnitOfWork
     */
    public function getUnitOfWork()
    {
        return $this->unitOfWork;
    }

    /**
     * Gets the Hydrator used by the DocumentManager to hydrate document arrays
     * to document objects.
     *
     * @return Doctrine\ODM\MongoDB\Hydrator
     */
    public function getHydrator()
    {
        return $this->hydrator;
    }
 
    /**
     * Returns the metadata for a class.
     *
     * @param string $className The class name.
     * @return Doctrine\ODM\MongoDB\Mapping\ClassMetadata
     * @internal Performance-sensitive method.
     */
    public function getClassMetadata($className)
    {
        return $this->metadataFactory->getMetadataFor($className);
    }

    /**
     * Returns the MongoDB instance for a class.
     *
     * @param string $className The class name.
     * @return Doctrine\ODM\MongoDB\MongoDB
     */
    public function getDocumentDB($className)
    {
        $db = $this->metadataFactory->getMetadataFor($className)->getDB();
        $db = $db ? $db : $this->config->getDefaultDB();
        $db = $db ? $db : 'doctrine';
        $db = sprintf('%s%s', $this->config->getEnvironmentPrefix(), $db);
        if ($db && ! isset($this->documentDBs[$db])) {
            $database = $this->mongo->selectDB($db);
            $this->documentDBs[$db] = new MongoDB($database);
        }
        if ( ! isset($this->documentDBs[$db])) {
            throw MongoDBException::documentNotMappedToDB($className);
        }
        return $this->documentDBs[$db];
    }

    /**
     * Returns the MongoCollection instance for a class.
     *
     * @param string $className The class name.
     * @return Doctrine\ODM\MongoDB\MongoCollection
     */
    public function getDocumentCollection($className)
    {
        $metadata = $this->metadataFactory->getMetadataFor($className);
        $db = $metadata->getDB();
        $collection = $metadata->getCollection();
        $key = $db . '.' . $collection . '.' . $className;
        if ($collection && ! isset($this->documentCollections[$key])) {
            if ($metadata->isFile()) {
                $collection = $this->getDocumentDB($className)->getGridFS($collection);
            } else {
                $collection = $this->getDocumentDB($className)->selectCollection($collection);
            }
            $mongoCollection = new MongoCollection($collection, $metadata, $this);
            $this->documentCollections[$key] = $mongoCollection;
        }
        if ( ! isset($this->documentCollections[$key])) {
            throw MongoDBException::documentNotMappedToCollection($className);
        }
        return $this->documentCollections[$key];
    }

    public function query($queryString, $parameters = array())
    {
        if ( ! is_array($parameters)) {
            $parameters = array($parameters);
        }
        return $this->queryParser->parse($queryString, $parameters);
    }

    /**
     * Create a new Query instance for a class.
     *
     * @param string $documentName The document class name.
     * @return Document\ODM\MongoDB\Query
     */
    public function createQuery($documentName = null)
    {
        return new Query($this, $documentName);
    }

    /**
     * Tells the DocumentManager to make an instance managed and persistent.
     *
     * The document will be entered into the database at or before transaction
     * commit or as a result of the flush operation.
     * 
     * NOTE: The persist operation always considers documents that are not yet known to
     * this DocumentManager as NEW. Do not pass detached documents to the persist operation.
     *
     * @param object $document The instance to make managed and persistent.
     */
    public function persist($document)
    {
        if ( ! is_object($document)) {
            throw new \InvalidArgumentException(gettype($document));
        }
        $this->errorIfClosed();
        $this->unitOfWork->persist($document);
    }

    /**
     * Removes a document instance.
     *
     * A removed document will be removed from the database at or before transaction commit
     * or as a result of the flush operation.
     *
     * @param object $document The document instance to remove.
     */
    public function remove($document)
    {
        if ( ! is_object($document)) {
            throw new \InvalidArgumentException(gettype($document));
        }
        $this->errorIfClosed();
        $this->unitOfWork->remove($document);
    }

    /**
     * Refreshes the persistent state of a document from the database,
     * overriding any local changes that have not yet been persisted.
     *
     * @param object $document The document to refresh.
     */
    public function refresh($document)
    {
        if ( ! is_object($document)) {
            throw new \InvalidArgumentException(gettype($document));
        }
        $this->errorIfClosed();
        $this->unitOfWork->refresh($document);
    }

    /**
     * Detaches a document from the DocumentManager, causing a managed document to
     * become detached.  Unflushed changes made to the document if any
     * (including removal of the document), will not be synchronized to the database.
     * Documents which previously referenced the detached document will continue to
     * reference it.
     *
     * @param object $document The document to detach.
     */
    public function detach($document)
    {
        if ( ! is_object($document)) {
            throw new \InvalidArgumentException(gettype($document));
        }
        $this->unitOfWork->detach($document);
    }

    /**
     * Merges the state of a detached document into the persistence context
     * of this DocumentManager and returns the managed copy of the document.
     * The document passed to merge will not become associated/managed with this DocumentManager.
     *
     * @param object $document The detached document to merge into the persistence context.
     * @return object The managed copy of the document.
     */
    public function merge($document)
    {
        if ( ! is_object($document)) {
            throw new \InvalidArgumentException(gettype($document));
        }
        $this->errorIfClosed();
        return $this->unitOfWork->merge($document);
    }

    /**
     * Gets the repository for a document class.
     *
     * @param string $documentName  The name of the Document.
     * @return DocumentRepository  The repository.
     */
    public function getRepository($documentName)
    {
        if (isset($this->repositories[$documentName])) {
            return $this->repositories[$documentName];
        }

        $metadata = $this->getClassMetadata($documentName);
        $customRepositoryClassName = $metadata->customRepositoryClassName;

        if ($customRepositoryClassName !== null) {
            $repository = new $customRepositoryClassName($this, $metadata);
        } else {
            $repository = new DocumentRepository($this, $metadata);
        }

        $this->repositories[$documentName] = $repository;

        return $repository;
    }

    /**
     * Loads a given document by its ID refreshing the values with the data from
     * the database if the document already exists in the identity map.
     *
     * @param string $documentName The document name to load.
     * @param string $id  The id the document to load.
     * @return object $document  The loaded document.
     * @todo this function seems to be doing to much, should we move parts of it
     * to BasicDocumentPersister maybe?
     */
    public function loadByID($documentName, $id)
    {
        $class = $this->getClassMetadata($documentName);
        $collection = $this->getDocumentCollection($documentName);

        $result = $collection->findOne(array('_id' => $class->getDatabaseIdentifierValue($id)));

        if ( ! $result) {
            return null;
        }
        return $this->load($documentName, $id, $result);
    }

    /**
     * Loads data for a document id refreshing and overriding any local values
     * if the document already exists in the identity map.
     *
     * @param string $documentName  The document name to load.
     * @param string $id  The id of the document being loaded.
     * @param string $data  The data to load into the document.
     * @return object $document The loaded document.
     */
    public function load($documentName, $id, $data)
    {
        if ($data !== null) {
            $hints = array(Query::HINT_REFRESH => Query::HINT_REFRESH);
            $document = $this->unitOfWork->getOrCreateDocument($documentName, $data, $hints);
            return $document;
        }
        return false;
    }

    /**
     * Flushes all changes to objects that have been queued up to now to the database.
     * This effectively synchronizes the in-memory state of managed objects with the
     * database.
     *
     * @param array $options Array of options to be used with batchInsert(), update() and remove()
     */
    public function flush(array $options = array())
    {
        $this->errorIfClosed();
        $this->unitOfWork->commit($options);
    }

    /**
     * Ensure indexes are created for all documents that can be loaded with the
     * metadata factory.
     */
    public function ensureIndexes()
    {
        foreach ($this->metadataFactory->getAllMetadata() as $class) {
            $this->ensureDocumentIndexes($class->name);
        }
    }

    /**
     * Ensure the given documents indexes are created.
     *
     * @param string $documentName The document name to ensure the indexes for.
     */
    public function ensureDocumentIndexes($documentName)
    {
        $class = $this->getClassMetadata($documentName);
        if ($indexes = $class->getIndexes()) {
            $collection = $this->getDocumentCollection($class->name);
            foreach ($indexes as $index) {
                $collection->ensureIndex($index['keys'], $index['options']);
            }
        }
    }

    /**
     * Delete indexes for all documents that can be loaded with the
     * metadata factory.
     */
    public function deleteIndexes()
    {
        foreach ($this->metadataFactory->getAllMetadata() as $class) {
            $this->deleteDocumentIndexes($class->name);
        }
    }

    /**
     * Delete the given documents indexes.
     *
     * @param string $documentName The document name to delete the indexes for.
     */
    public function deleteDocumentIndexes($documentName)
    {
        return $this->getDocumentCollection($documentName)->deleteIndexes();
    }

    /**
     * Execute a map reduce operation.
     *
     * @param string $documentName The document name to run the operation on.
     * @param string $map The javascript map function.
     * @param string $reduce The javascript reduce function.
     * @param array $query The mongo query.
     * @param array $options Array of options.
     * @return MongoCursor $cursor
     */
    public function mapReduce($documentName, $map, $reduce, array $query = array(), array $options = array())
    {
        $class = $this->getClassMetadata($documentName);
        $db = $this->getDocumentDB($documentName);
        if (is_string($map)) {
            $map = new \MongoCode($map);
        }
        if (is_string($reduce)) {
            $reduce = new \MongoCode($reduce);
        }
        $command = array(
            'mapreduce' => $class->getCollection(),
            'map' => $map,
            'reduce' => $reduce,
            'query' => $query
        );
        $command = array_merge($command, $options);
        $result = $db->command($command);
        if ( ! $result['ok']) {
            throw new \RuntimeException($result['errmsg']);
        }
        $cursor = $db->selectCollection($result['result'])->find();
        $cursor = new MongoCursor($this, $this->hydrator, $class, $cursor);
        $cursor->hydrate(false);
        return $cursor;
    }

    /**
     * Gets a reference to the document identified by the given type and identifier
     * without actually loading it.
     *
     * If partial objects are allowed, this method will return a partial object that only
     * has its identifier populated. Otherwise a proxy is returned that automatically
     * loads itself on first access.
     *
     * @return object The document reference.
     */
    public function getReference($documentName, $identifier)
    {
        $class = $this->metadataFactory->getMetadataFor($documentName);

        // Check identity map first, if its already in there just return it.
        if ($document = $this->unitOfWork->tryGetById($identifier, $class->rootDocumentName)) {
            return $document;
        }
        $document = $this->proxyFactory->getProxy($class->name, $identifier);
        $this->unitOfWork->registerManaged($document, $identifier, array());

        return $document;
    }

    /**
     * Gets a partial reference to the document identified by the given type and identifier
     * without actually loading it, if the document is not yet loaded.
     *
     * The returned reference may be a partial object if the document is not yet loaded/managed.
     * If it is a partial object it will not initialize the rest of the document state on access.
     * Thus you can only ever safely access the identifier of an document obtained through
     * this method.
     *
     * The use-cases for partial references involve maintaining bidirectional associations
     * without loading one side of the association or to update an document without loading it.
     * Note, however, that in the latter case the original (persistent) document data will
     * never be visible to the application (especially not event listeners) as it will
     * never be loaded in the first place.
     *
     * @param string $documentName The name of the document type.
     * @param mixed $identifier The document identifier.
     * @return object The (partial) document reference.
     */
    public function getPartialReference($documentName, $identifier)
    {
        $class = $this->metadataFactory->getMetadataFor($documentName);

        // Check identity map first, if its already in there just return it.
        if ($entity = $this->unitOfWork->tryGetById($identifier, $class->rootDocumentName)) {
            return $entity;
        }
        $document = $class->newInstance();
        $class->setIdentifierValue($document, $identifier);
        $this->unitOfWork->registerManaged($document, $identifier, array());

        return $document;
    }

    /**
     * Find a single document by its identifier or multiple by a given criteria.
     *
     * @param string $documentName The document to find.
     * @param mixed $query A single identifier or an array of criteria.
     * @param array $select The fields to select.
     * @return Doctrine\ODM\MongoDB\MongoCursor $cursor
     * @return object $document
     */
    public function find($documentName, $query = array(), array $select = array())
    {
        if (is_array($documentName)) {
            $classNames = $documentName;
            $documentName = $classNames[0];

            $discriminatorField = $this->getClassMetadata($documentName)->discriminatorField['name'];
            $discriminatorValues = $this->getDiscriminatorValues($classNames);
            $query[$discriminatorField] = array('$in' => $discriminatorValues);
        }
        return $this->getRepository($documentName)->find($query, $select);
    }

    /**
     * Find a single document with the given query and select fields.
     *
     * @param string $documentName The document to find.
     * @param array $query The query criteria.
     * @param array $select The fields to select
     * @return object $document
     */
    public function findOne($documentName, array $query = array(), array $select = array())
    {
        return $this->getRepository($documentName)->findOne($query, $select);
    }

    /**
     * Clears the DocumentManager. All documents that are currently managed
     * by this DocumentManager become detached.
     */
    public function clear()
    {
        $this->unitOfWork->clear();
    }

    /**
     * Closes the DocumentManager. All documents that are currently managed
     * by this DocumentManager become detached. The DocumentManager may no longer
     * be used after it is closed.
     */
    public function close()
    {
        $this->clear();
        $this->closed = true;
    }

    public function formatDBName($dbName)
    {
        return sprintf('%s%s%s', 
            $this->config->getDBPrefix(), 
            $dbName,
            $this->config->getDBSuffix()
        );
    }

    public function getDiscriminatorValues($classNames)
    {
        $discriminatorValues = array();
        $collections = array();
        foreach ($classNames as $className) {
            $class = $this->getClassMetadata($className);
            $discriminatorValues[] = $class->discriminatorValue;
            $key = $class->getDB() . '.' . $class->getCollection();
            $collections[$key] = $key;
        }
        if (count($collections) > 1) {
            throw new \InvalidArgumentException('Documents involved are not all mapped to the same database collection.');
        }
        return $discriminatorValues;
    }

    public function getClassNameFromDiscriminatorValue(array $mapping, $value)
    {
        $discriminatorField = isset($mapping['discriminatorField']) ? $mapping['discriminatorField'] : '_doctrine_class_name';
        if (isset($value[$discriminatorField])) {
            $discriminatorValue = $value[$discriminatorField];
            return isset($mapping['discriminatorMap'][$discriminatorValue]) ? $mapping['discriminatorMap'][$discriminatorValue] : $discriminatorValue;
        } else {
            return $mapping['targetDocument'];
        }
    }

    /**
     * Throws an exception if the DocumentManager is closed or currently not active.
     *
     * @throws MongoDBException If the DocumentManager is closed.
     */
    private function errorIfClosed()
    {
        if ($this->closed) {
            throw MongoDBException::documentManagerClosed();
        }
    }
}
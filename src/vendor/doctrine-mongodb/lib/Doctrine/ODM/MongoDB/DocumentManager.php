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
    private $_mongo;

    /**
     * The used Configuration.
     *
     * @var Doctrine\ODM\MongoDB\Configuration
     */
    private $_config;

    /**
     * The metadata factory, used to retrieve the ORM metadata of document classes.
     *
     * @var Doctrine\ODM\MongoDB\Mapping\ClassMetadataFactory
     */
    private $_metadataFactory;

    /**
     * The DocumentRepository instances.
     *
     * @var array
     */
    private $_repositories = array();

    /**
     * The UnitOfWork used to coordinate object-level transactions.
     *
     * @var Doctrine\ODM\MongoDB\UnitOfWork
     */
    private $_unitOfWork;

    /**
     * The event manager that is the central point of the event system.
     *
     * @var Doctrine\Common\EventManager
     */
    private $_eventManager;

    /**
     * The Document hydrator instance.
     *
     * @var Doctrine\ODM\MongoDB\Hydrator
     */
    private $_hydrator;

    /**
     * Array of cached MongoDB instances that are lazily loaded.
     *
     * @var array
     */
    private $_documentDBs = array();

    /**
     * Array of cached MongoCollection instances that are lazily loaded.
     *
     * @var array
     */
    private $_documentCollections = array();

    /**
     * The Query\Parser instance for parsing string based queries.
     *
     * @var Query\Parser $parser
     */
    private $_queryParser;

    /**
     * Whether the DocumentManager is closed or not.
     */
    private $_closed = false;

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
        $this->_mongo = $mongo ? $mongo : new Mongo();
        $this->_config = $config ? $config : new Configuration();
        $this->_eventManager = $eventManager ? $eventManager : new EventManager();
        $this->_hydrator = new Hydrator($this);
        $this->_metadataFactory = new ClassMetadataFactory($this);
        if ($cacheDriver = $this->_config->getMetadataCacheImpl()) {
            $this->_metadataFactory->setCacheDriver($cacheDriver);
        }
        $this->_queryParser = new Parser($this);
        $this->_unitOfWork = new UnitOfWork($this);
        $this->_proxyFactory = new ProxyFactory($this,
                $this->_config->getProxyDir(),
                $this->_config->getProxyNamespace(),
                $this->_config->getAutoGenerateProxyClasses());
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
        return new self($mongo, $config, $eventManager);
    }

    /**
     * Determines whether an document instance is managed in this DocumentManager.
     *
     * @param object $document
     * @return boolean TRUE if this DocumentManager currently manages the given document, FALSE otherwise.
     */
    public function contains($document)
    {
        return $this->_unitOfWork->isScheduledForInsert($document) ||
               $this->_unitOfWork->isInIdentityMap($document) &&
               ! $this->_unitOfWork->isScheduledForDelete($document);
    }

    /**
     * Gets the EventManager used by the DocumentManager.
     *
     * @return Doctrine\Common\EventManager
     */
    public function getEventManager()
    {
        return $this->_eventManager;
    }

    public function getConfiguration()
    {
        return $this->_config;
    }

    public function getMongo()
    {
        return $this->_mongo;
    }

    /**
     * Gets the metadata factory used to gather the metadata of classes.
     *
     * @return Doctrine\ODM\MongoDB\Mapping\ClassMetadataFactory
     */
    public function getMetadataFactory()
    {
        return $this->_metadataFactory;
    }

    /**
     * Gets the UnitOfWork used by the DocumentManager to coordinate operations.
     *
     * @return Doctrine\ODM\MongoDB\UnitOfWork
     */
    public function getUnitOfWork()
    {
        return $this->_unitOfWork;
    }

    /**
     * Gets the Hydrator used by the DocumentManager to hydrate document arrays
     * to document objects.
     *
     * @return Doctrine\ODM\MongoDB\Hydrator
     */
    public function getHydrator()
    {
        return $this->_hydrator;
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
        return $this->_metadataFactory->getMetadataFor($className);
    }

    /**
     * Returns the MongoDB instance for a class.
     *
     * @param string $className The class name.
     * @return Doctrine\ODM\MongoDB\MongoDB
     */
    public function getDocumentDB($className)
    {
        $db = $this->_metadataFactory->getMetadataFor($className)->getDB();
        $db = $db ? $db : $this->_config->getDefaultDB();
        $db = $db ? $db : 'doctrine';
        $db = sprintf('%s%s', $this->_config->getEnvironmentPrefix(), $db);
        if ($db && ! isset($this->_documentDBs[$db])) {
            $database = $this->_mongo->selectDB($db);
            $this->_documentDBs[$db] = new MongoDB($database);
        }
        if ( ! isset($this->_documentDBs[$db])) {
            throw MongoDBException::documentNotMappedToDB($className);
        }
        return $this->_documentDBs[$db];
    }

    /**
     * Returns the MongoCollection instance for a class.
     *
     * @param string $className The class name.
     * @return Doctrine\ODM\MongoDB\MongoCollection
     */
    public function getDocumentCollection($className)
    {
        $metadata = $this->_metadataFactory->getMetadataFor($className);
        $collection = $metadata->getCollection();
        $db = $metadata->getDB();
        $key = $db . '.' . $collection;
        if ($collection && ! isset($this->_documentCollections[$key])) {
            if ($metadata->isFile()) {
                $collection = $this->getDocumentDB($className)->getGridFS($collection);
            } else {
                $collection = $this->getDocumentDB($className)->selectCollection($collection);
            }
            $mongoCollection = new MongoCollection($collection, $metadata, $this);
            $this->_documentCollections[$key] = $mongoCollection;
        }
        if ( ! isset($this->_documentCollections[$key])) {
            throw MongoDBException::documentNotMappedToCollection($className);
        }
        return $this->_documentCollections[$key];
    }

    public function query($queryString, $parameters = array())
    {
        if ( ! is_array($parameters)) {
            $parameters = array($parameters);
        }
        return $this->_queryParser->parse($queryString, $parameters);
    }

    /**
     * Create a new Query instance for a class.
     *
     * @param string $className The class name.
     * @return Document\ODM\MongoDB\Query
     */
    public function createQuery($className = null)
    {
        return new Query($this, $className);
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
        $this->_errorIfClosed();
        $this->_unitOfWork->persist($document);
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
        $this->_errorIfClosed();
        $this->_unitOfWork->remove($document);
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
        $this->_errorIfClosed();
        $this->_unitOfWork->refresh($document);
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
        $this->_unitOfWork->detach($document);
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
        $this->_errorIfClosed();
        return $this->_unitOfWork->merge($document);
    }

    /**
     * Gets the repository for a document class.
     *
     * @param string $documentName  The name of the Document.
     * @return DocumentRepository  The repository.
     */
    public function getRepository($documentName)
    {
        if (isset($this->_repositories[$documentName])) {
            return $this->_repositories[$documentName];
        }

        $metadata = $this->getClassMetadata($documentName);
        $customRepositoryClassName = $metadata->customRepositoryClassName;

        if ($customRepositoryClassName !== null) {
            $repository = new $customRepositoryClassName($this, $metadata);
        } else {
            $repository = new DocumentRepository($this, $metadata);
        }

        $this->_repositories[$documentName] = $repository;

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
            $document = $this->_unitOfWork->getOrCreateDocument($documentName, $data, $hints);
            $this->getUnitOfWork()->registerManaged($document, $id, $data);
            return $document;
        }
        return false;
    }

    /**
     * Flushes all changes to objects that have been queued up to now to the database.
     * This effectively synchronizes the in-memory state of managed objects with the
     * database.
     */
    public function flush()
    {
        $this->_errorIfClosed();
        $this->_unitOfWork->commit();
    }

    public function ensureDocumentIndexes($class)
    {
        if ($indexes = $class->getIndexes()) {
            $collection = $this->getDocumentCollection($class->name);
            foreach ($indexes as $index) {
                $collection->ensureIndex($index['keys'], $index['options']);
            }
        }
    }

    public function deleteDocumentIndexes($documentName)
    {
        return $this->getDocumentCollection($documentName)->deleteIndexes();
    }

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
        $cursor = new MongoCursor($this, $this->_hydrator, $class, $cursor);
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
        $class = $this->_metadataFactory->getMetadataFor($documentName);

        // Check identity map first, if its already in there just return it.
        if ($document = $this->_unitOfWork->tryGetById($identifier, $class->rootDocumentName)) {
            return $document;
        }
        $document = $this->_proxyFactory->getProxy($class->name, $identifier);
        $this->_unitOfWork->registerManaged($document, $identifier, array());

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
     *
     * @param string $documentName
     */
    public function clear()
    {
        $this->_unitOfWork->clear();
    }

    /**
     * Closes the DocumentManager. All documents that are currently managed
     * by this DocumentManager become detached. The DocumentManager may no longer
     * be used after it is closed.
     */
    public function close()
    {
        $this->clear();
        $this->_closed = true;
    }

    /**
     * Throws an exception if the DocumentManager is closed or currently not active.
     *
     * @throws MongoDBException If the DocumentManager is closed.
     */
    private function _errorIfClosed()
    {
        if ($this->_closed) {
            throw MongoDBException::documentManagerClosed();
        }
    }

    public function formatDBName($dbName)
    {
        return sprintf('%s%s%s', 
            $this->_config->getDBPrefix(), 
            $dbName,
            $this->_config->getDBSuffix()
        );
    }
}


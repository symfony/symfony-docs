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

namespace Doctrine\ODM\MongoDB\Persisters;

use Doctrine\ODM\MongoDB\DocumentManager,
    Doctrine\ODM\MongoDB\UnitOfWork,
    Doctrine\ODM\MongoDB\Mapping\ClassMetadata,
    Doctrine\ODM\MongoDB\MongoCursor,
    Doctrine\ODM\MongoDB\Mapping\Types\Type,
    Doctrine\Common\Collections\Collection,
    Doctrine\ODM\MongoDB\ODMEvents,
    Doctrine\ODM\MongoDB\Event\OnUpdatePreparedArgs,
    Doctrine\ODM\MongoDB\MongoDBException;

/**
 * The BasicDocumentPersister is responsible for actual persisting the calculated
 * changesets performed by the UnitOfWork.
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @since       1.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Bulat Shakirzyanov <bulat@theopenskyproject.com>
 */
class BasicDocumentPersister
{
    /**
     * The DocumentManager instance.
     *
     * @var Doctrine\ODM\MongoDB\DocumentManager
     */
    private $_dm;

    /**
     * The UnitOfWork instance.
     *
     * @var Doctrine\ODM\MongoDB\UnitOfWork
     */
    private $_uow;

    /**
     * The ClassMetadata instance for the document type being persisted.
     *
     * @var Doctrine\ODM\MongoDB\Mapping\ClassMetadata
     */
    private $_class;

    /**
     * The MongoCollection instance for this document.
     *
     * @var Doctrine\ODM\MongoDB\MongoCollection
     */
    private $_collection;

    /**
     * The string document name being persisted.
     *
     * @var string
     */
    private $_documentName;

    /**
     * Array of quered inserts for the persister to insert.
     *
     * @var array
     */
    private $_queuedInserts = array();

    /**
     * Documents to be updated, used in executeReferenceUpdates() method
     * @var array
     */
    private $_documentsToUpdate = array();

    /**
     * Fields to update, used in executeReferenceUpdates() method
     * @var array
     */
    private $_fieldsToUpdate = array();

    /**
     * Mongo command prefix
     * @var string
     */
    private $_cmd;

    /**
     * Initializes a new BasicDocumentPersister instance.
     *
     * @param Doctrine\ODM\MongoDB\DocumentManager $dm
     * @param Doctrine\ODM\MongoDB\Mapping\ClassMetadata $class
     */
    public function __construct(DocumentManager $dm, ClassMetadata $class)
    {
        $this->_dm = $dm;
        $this->_uow = $dm->getUnitOfWork();
        $this->_class = $class;
        $this->_documentName = $class->getName();
        $this->_collection = $dm->getDocumentCollection($class->name);
        $this->_cmd = $this->_dm->getConfiguration()->getMongoCmd();
    }

    /**
     * Adds a document to the queued insertions.
     * The document remains queued until {@link executeInserts} is invoked.
     *
     * @param object $document The document to queue for insertion.
     */
    public function addInsert($document)
    {
        $this->_queuedInserts[spl_object_hash($document)] = $document;
    }

    /**
     * Executes all queued document insertions and returns any generated post-insert
     * identifiers that were created as a result of the insertions.
     *
     * If no inserts are queued, invoking this method is a NOOP.
     *
     * @return array An array of any generated post-insert IDs. This will be an empty array
     *               if the document class does not use the IDENTITY generation strategy.
     */
    public function executeInserts()
    {
        if ( ! $this->_queuedInserts) {
            return;
        }

        $postInsertIds = array();
        $inserts = array();

        foreach ($this->_queuedInserts as $oid => $document) {
            $data = $this->prepareInsertData($document);
            if ( ! $data) {
                continue;
            }
            $inserts[$oid] = $data;
        }
        if (empty($inserts)) {
            return;
        }
        $this->_collection->batchInsert($inserts);

        foreach ($inserts as $oid => $data) {
            $document = $this->_queuedInserts[$oid];
            $postInsertIds[] = array($data['_id'], $document);
            if ($this->_class->isFile()) {
                $this->_dm->getHydrator()->hydrate($this->_class, $document, $data);
            }
        }
        $this->_queuedInserts = array();

        return $postInsertIds;
    }

    /**
     * Executes reference updates in case document had references to new documents,
     * without identifier value
     */
    public function executeReferenceUpdates()
    {
        foreach ($this->_documentsToUpdate as $oid => $document)
        {
            $update = array();
            foreach ($this->_fieldsToUpdate[$oid] as $fieldName => $fieldData)
            {
                list ($mapping, $value) = $fieldData;
                $update[$fieldName] = $this->_prepareValue($mapping, $value);
            }
            $classMetadata = $this->_dm->getClassMetadata(get_class($document));
            $id = $this->_uow->getDocumentIdentifier($document);
            $id = $classMetadata->getDatabaseIdentifierValue($id);
            $this->_collection->update(array(
                '_id' => $id
            ), array(
                $this->_cmd . 'set' => $update
            ));
            unset($this->_documentsToUpdate[$oid]);
            unset($this->_fieldsToUpdate[$oid]);
        }
    }

    /**
     * Updates persisted document, using atomic operators
     *
     * @param mixed $document
     */
    public function update($document)
    {
        $id = $this->_uow->getDocumentIdentifier($document);

        $update = $this->prepareUpdateData($document);
        if ( ! empty($update)) {
            if ($this->_dm->getEventManager()->hasListeners(ODMEvents::onUpdatePrepared)) {
                $this->_dm->getEventManager()->dispatchEvent(
                    ODMEvents::onUpdatePrepared, new OnUpdatePreparedArgs($this->_dm, $document, $update)
                );
            }
            /**
             * temporary fix for @link http://jira.mongodb.org/browse/SERVER-1050
             * atomic modifiers $pushAll and $pullAll, $push, $pop and $pull
             * are not allowed on the same field in one update
             */
            $id = $this->_class->getDatabaseIdentifierValue($id);
            if (isset($update[$this->_cmd . 'pushAll']) && isset($update[$this->_cmd . 'pullAll'])) {
                $fields = array_intersect(
                    array_keys($update[$this->_cmd . 'pushAll']),
                    array_keys($update[$this->_cmd . 'pullAll'])
                );
                if ( ! empty($fields)) {
                    $tempUpdate = array();
                    foreach ($fields as $field) {
                        $tempUpdate[$field] = $update[$this->_cmd . 'pullAll'][$field];
                        unset($update[$this->_cmd . 'pullAll'][$field]);
                    }
                    if (empty($update[$this->_cmd . 'pullAll'])) {
                        unset($update[$this->_cmd . 'pullAll']);
                    }
                    $tempUpdate = array(
                        $this->_cmd . 'pullAll' => $tempUpdate
                    );
                    $this->_collection->update(array('_id' => $id), $tempUpdate);
                }
            }
            $this->_collection->update(array('_id' => $id), $update);
        }
    }

    /**
     * Removes document from mongo
     *
     * @param mixed $document
     */
    public function delete($document)
    {
        $id = $this->_uow->getDocumentIdentifier($document);

        $this->_collection->remove(array(
            '_id' => $this->_class->getDatabaseIdentifierValue($id)
        ));
    }

    /**
     * Prepares insert data for document
     *
     * @param mixed $document
     * @return array
     */
    public function prepareInsertData($document)
    {
        $oid = spl_object_hash($document);
        $changeset = $this->_uow->getDocumentChangeSet($document);
        $result = array();
        foreach ($this->_class->fieldMappings as $mapping) {
            if (isset($mapping['notSaved']) && $mapping['notSaved'] === true) {
                continue;
            }
            $new = isset($changeset[$mapping['fieldName']][1]) ? $changeset[$mapping['fieldName']][1] : null;
            if ($new === null && $mapping['nullable'] === false) {
                continue;
            }
            $changeset[$mapping['fieldName']] = array();
            if ($this->_class->isIdentifier($mapping['fieldName'])) {
                $result['_id'] = $this->_prepareValue($mapping, $new);
                continue;
            }
            $result[$mapping['fieldName']] = $this->_prepareValue($mapping, $new);
            if (isset($mapping['reference'])) {
                $scheduleForUpdate = false;
                if ($mapping['type'] === 'one') {
                    if (null === $result[$mapping['fieldName']][$this->_cmd . 'id']) {
                        $scheduleForUpdate = true;
                    }
                } elseif ($mapping['type'] === 'many') {
                    foreach ($result[$mapping['fieldName']] as $ref) {
                        if (null === $ref[$this->_cmd . 'id']) {
                            $scheduleForUpdate = true;
                            break;
                        }
                    }
                }
                if ($scheduleForUpdate) {
                    unset($result[$mapping['fieldName']]);
                    $id = spl_object_hash($document);
                    $this->_documentsToUpdate[$id] = $document;
                    $this->_fieldsToUpdate[$id][$mapping['fieldName']] = array($mapping, $new);
                }
            }
        }
        return $result;
    }

    /**
     * Prepares update array for document, using atomic operators
     *
     * @param mixed $document
     * @return array
     */
    public function prepareUpdateData($document)
    {
        $oid = spl_object_hash($document);
        $changeset = $this->_uow->getDocumentChangeSet($document);
        $result = array();
        foreach ($this->_class->fieldMappings as $mapping) {
            if (isset($mapping['notSaved']) && $mapping['notSaved'] === true) {
                continue;
            }
            $old = isset($changeset[$mapping['fieldName']][0]) ? $changeset[$mapping['fieldName']][0] : null;
            $new = isset($changeset[$mapping['fieldName']][1]) ? $changeset[$mapping['fieldName']][1] : null;
            $new = $this->_prepareValue($mapping, $new);
            $old = $this->_prepareValue($mapping, $old);
            if ($this->_class->isIdentifier($mapping['fieldName']) && ! $this->_equals($new, $old)) {
                throw MongoDBException::identifierCannotBeUpdated();
            }
            if (($mapping['type'] === 'many') || $mapping['type'] === 'collection') {
                $this->_addArrayUpdateAtomicOperator($mapping, (array) $new, (array) $old, $result);
            } else {
                $this->_addFieldUpdateAtomicOperator($mapping, $new, $old, $result);
            }
        }
        return $result;
    }

    /**
     *
     * @param array $mapping
     * @param mixed $value
     */
    private function _prepareValue(array $mapping, $value) {
        if ( ! isset($value)) {
            return null;
        }
        if ($mapping['type'] === 'many') {
            $values = $value;
            $value = array();
            foreach ($values as $rawValue) {
                $value[] = $this->_prepareValue(array_merge($mapping, array(
                    'type' => 'one'
                )), $rawValue);
            }
            unset($values, $rawValue);
        } elseif ((isset($mapping['reference'])) || isset($mapping['embedded'])) {
            $targetClass = $this->_dm->getClassMetadata($mapping['targetDocument']);
            if (isset($mapping['embedded'])) {
                $value = $this->_prepareDocEmbeded($targetClass, $value);
            } else if (isset($mapping['reference'])) {
                $value = $this->_prepareDocReference($targetClass, $value);
            }
        } else {
            $value = Type::getType($mapping['type'])->convertToDatabaseValue($this->_getScalar($value));
        }
        return $value;
    }

    /**
     * Gets the ClassMetadata instance of the document class this persister is used for.
     *
     * @return Doctrine\ODM\MongoDB\Mapping\ClassMetadata
     */
    public function getClassMetadata()
    {
        return $this->_class;
    }

    /**
     * Refreshes a managed document.
     *
     * @param object $document The document to refresh.
     */
    public function refresh($document)
    {
        $id = $this->_uow->getDocumentIdentifier($document);
        if ($this->_dm->loadByID($this->_class->name, $id) === null) {
            throw new \InvalidArgumentException(sprintf('Could not loadByID because ' . $this->_class->name . ' '.$id . ' does not exist anymore.'));
        }
    }

    /**
     * Loads an document by a list of field criteria.
     *
     * @param array $query The criteria by which to load the document.
     * @param object $document The document to load the data into. If not specified,
     *        a new document is created.
     * @param $assoc The association that connects the document to load to another document, if any.
     * @param array $hints Hints for document creation.
     * @return object The loaded and managed document instance or NULL if the document can not be found.
     * @todo Check identity map? loadById method? Try to guess whether $criteria is the id?
     * @todo Modify DocumentManager to use this method instead of its own hard coded
     */
    public function load(array $query = array(), array $select = array())
    {
        $result = $this->_collection->findOne($query, $select);
        if ($result !== null) {
            $document = $this->_uow->getOrCreateDocument($this->_documentName, $result);
            $this->_uow->registerManaged($document, $this->_class->getPHPIdentifierValue($result['_id']), $result);
            return $document;
        }
        return null;
    }

    /**
     * Lood document by its identifier.
     *
     * @param string $id
     * @return object|null
     */
    public function loadById($id)
    {
        $result = $this->_collection->findOne(array(
            '_id' => $this->_class->getDatabaseIdentifierValue($id)
        ));
        if ($result !== null) {
            $document = $this->_uow->getOrCreateDocument($this->_documentName, $result);
            $this->_uow->registerManaged($document, $this->_class->getPHPIdentifierValue($result['_id']), $result);
            return $document;
        }
        return null;
    }

    /**
     * Loads a list of documents by a list of field criteria.
     *
     * @param array $criteria
     * @return array
     */
    public function loadAll(array $query = array(), array $select = array())
    {
        $cursor = $this->_collection->find($query, $select);
        return new MongoCursor($this->_dm, $this->_dm->getHydrator(), $this->_class, $cursor);
    }

    /**
     * Add the atomic operator to update or remove a field from a document
     * based on whether or not the value has changed.
     *
     * @param string $fieldName
     * @param string $new
     * @param string $old
     * @param string $result
     */
    private function _addFieldUpdateAtomicOperator(array $mapping, $new, $old, array &$result)
    {
        if ($this->_equals($old, $new)) {
            return;
        }

        if ($mapping['type'] === 'increment') {
            if ($new >= $old) {
                $result[$this->_cmd . 'inc'][$mapping['fieldName']] = $new - $old;
            } else {
                $result[$this->_cmd . 'inc'][$mapping['fieldName']] = ($old - $new) * -1;
            }
        } else {
            if (isset($new) || $mapping['nullable'] === true) {
                $result[$this->_cmd . 'set'][$mapping['fieldName']] = $new;
            } else {
                $result[$this->_cmd . 'unset'][$mapping['fieldName']] = true;
            }
        }
    }

    /**
     * Add the atomic operator to add new values to an array and to remove values
     * from an array.
     *
     * @param string $fieldName
     * @param array $new
     * @param array $old
     * @param string $result
     */
    private function _addArrayUpdateAtomicOperator(array $mapping, array $new, array $old, array &$result)
    {
        foreach ($old as $val) {
            if ( ! in_array($val, $new)) {
                $result[$this->_cmd . 'pullAll'][$mapping['fieldName']][] = $val;
            }
        }
        foreach ($new as $val) {
            if ( ! in_array($val, $old)) {
                $result[$this->_cmd . 'pushAll'][$mapping['fieldName']][] = $val;
            }
        }
    }

    /**
     * Performs value comparison
     * @param mixed $old
     * @param mixed $new
     */
    private function _equals($old, $new)
    {
        $old = is_scalar($old) ? $old : $this->_getScalar($old);
        $new = is_scalar($new) ? $new : $this->_getScalar($new);
        return $new === $old;
    }

    /**
     * Converts value for comparison
     * @param mixed $val
     * @todo this conversion might not even be necessary, needs re-thinking
     */
    private function _getScalar($val)
    {
        if ($val instanceof \MongoDate) {
            return $val->sec;
        } else if ($val instanceof \DateTime) {
            return $val->getTimestamp();
        } else if ($val instanceof \MongoBinData) {
            return Type::getType('bin')->convertToPHPValue($val);
        } else if (($val instanceof \MongoId) || ($val instanceof \MongoTimestamp)) {
            return (string) $val;
        } else if (($val instanceof \MongoMaxKey) || ($val instanceof \MongoMinKey)) {
            return Type::getType('key')->convertToPHPValue($val);
        } else {
            return $val;
        }
    }

    /**
     * Returns the reference representation to be stored in mongodb or null if not applicable.
     *
     * @param ClassMetadata $class
     * @param Document $doc
     * @return array|null
     */
    private function _prepareDocReference(ClassMetadata $class, $doc)
    {
        if ( ! is_object($doc)) {
            return $doc;
        }
        $id = $this->_uow->getDocumentIdentifier($doc);
        if (null !== $id) {
            $id = $class->getPHPIdentifierValue($id);
        }
        $ref = array(
            $this->_cmd . 'ref' => $class->getCollection(),
            $this->_cmd . 'id' => $id,
            $this->_cmd . 'db' => $class->getDB()
        );
        return $ref;
    }

    /**
     * Prepares array of values to be stored in mongo to represent embedded object.
     *
     * @param ClassMetadata $class
     * @param Document $doc
     * @return array
     */
    private function _prepareDocEmbeded(ClassMetadata $class, $doc)
    {
        if ( ! is_object($doc)) {
            return $doc;
        }
        $changeset = array();
        foreach ($class->fieldMappings as $mapping) {
            $rawValue = $class->getFieldValue($doc, $mapping['fieldName']);
            if ( ! isset($rawValue)) {
                continue;
            }
            if (isset($mapping['embedded']) || isset($mapping['reference'])) {
                $classMetadata = $this->_dm->getClassMetadata($mapping['targetDocument']);
                if (isset($mapping['embedded'])) {
                    if ($mapping['type'] == 'many') {
                        $value = array();
                        foreach ($rawValue as $embeddedDoc) {
                            $value[] = $this->_prepareDocEmbeded($classMetadata, $embeddedDoc);
                        }
                    } elseif ($mapping['type'] == 'one') {
                        $value = $this->_prepareDocEmbeded($classMetadata, $rawValue);
                    }
                } elseif (isset($mapping['reference'])) {
                    if ($mapping['type'] == 'many') {
                         $value = array();
                        foreach ($rawValue as $referencedDoc) {
                            $value[] = $this->_prepareDocReference($classMetadata, $referencedDoc);
                        }
                    } else {
                        $value = $this->_prepareDocReference($classMetadata, $rawValue);
                    }
                }
            } else {
                $value = Type::getType($mapping['type'])->convertToDatabaseValue($rawValue);
            }
            $changeset[$mapping['fieldName']] = $value;
        }
        return $changeset;
    }
}
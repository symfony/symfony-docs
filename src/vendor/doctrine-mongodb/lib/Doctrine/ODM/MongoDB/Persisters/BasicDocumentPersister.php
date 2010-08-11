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
    Doctrine\ODM\MongoDB\MongoDBException,
    Doctrine\ODM\MongoDB\PersistentCollection;

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
    private $dm;

    /**
     * The UnitOfWork instance.
     *
     * @var Doctrine\ODM\MongoDB\UnitOfWork
     */
    private $uow;

    /**
     * The ClassMetadata instance for the document type being persisted.
     *
     * @var Doctrine\ODM\MongoDB\Mapping\ClassMetadata
     */
    protected $class;

    /**
     * The MongoCollection instance for this document.
     *
     * @var Doctrine\ODM\MongoDB\MongoCollection
     */
    private $collection;

    /**
     * The string document name being persisted.
     *
     * @var string
     */
    private $documentName;

    /**
     * Array of quered inserts for the persister to insert.
     *
     * @var array
     */
    private $queuedInserts = array();

    /**
     * Documents to be updated, used in executeReferenceUpdates() method
     * @var array
     */
    private $documentsToUpdate = array();

    /**
     * Fields to update, used in executeReferenceUpdates() method
     * @var array
     */
    private $fieldsToUpdate = array();

    /**
     * Mongo command prefix
     * @var string
     */
    private $cmd;

    /**
     * Initializes a new BasicDocumentPersister instance.
     *
     * @param Doctrine\ODM\MongoDB\DocumentManager $dm
     * @param Doctrine\ODM\MongoDB\Mapping\ClassMetadata $class
     */
    public function __construct(DocumentManager $dm, ClassMetadata $class)
    {
        $this->dm = $dm;
        $this->uow = $dm->getUnitOfWork();
        $this->class = $class;
        $this->documentName = $class->getName();
        $this->collection = $dm->getDocumentCollection($class->name);
        $this->cmd = $this->dm->getConfiguration()->getMongoCmd();
    }

    /**
     * Adds a document to the queued insertions.
     * The document remains queued until {@link executeInserts} is invoked.
     *
     * @param object $document The document to queue for insertion.
     */
    public function addInsert($document)
    {
        $this->queuedInserts[spl_object_hash($document)] = $document;
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
        if ( ! $this->queuedInserts) {
            return;
        }

        $postInsertIds = array();
        $inserts = array();
        foreach ($this->queuedInserts as $oid => $document) {
            $data = $this->prepareInsertData($document);
            if ( ! $data) {
                continue;
            }
            $inserts[$oid] = $data;
        }
        if (empty($inserts)) {
            return;
        }
        $this->collection->batchInsert($inserts);

        foreach ($inserts as $oid => $data) {
            $document = $this->queuedInserts[$oid];
            $postInsertIds[] = array($data['_id'], $document);
            if ($this->class->isFile()) {
                $this->dm->getHydrator()->hydrate($document, $data);
            }
        }
        $this->queuedInserts = array();

        return $postInsertIds;
    }

    /**
     * Executes reference updates in case document had references to new documents,
     * without identifier value
     */
    public function executeReferenceUpdates()
    {
        foreach ($this->documentsToUpdate as $oid => $document)
        {
            $update = array();
            foreach ($this->fieldsToUpdate[$oid] as $fieldName => $fieldData)
            {
                list ($mapping, $value) = $fieldData;
                $update[$fieldName] = $this->prepareValue($mapping, $value);
            }
            $classMetadata = $this->dm->getClassMetadata(get_class($document));
            $id = $this->uow->getDocumentIdentifier($document);
            $id = $classMetadata->getDatabaseIdentifierValue($id);
            $this->collection->update(array(
                '_id' => $id
            ), array(
                $this->cmd . 'set' => $update
            ));
        }
        $this->documentsToUpdate = array();
        $this->fieldsToUpdate = array();
    }

    /**
     * Updates persisted document, using atomic operators
     *
     * @param mixed $document
     */
    public function update($document)
    {
        $id = $this->uow->getDocumentIdentifier($document);

        $update = $this->prepareUpdateData($document);
        if ( ! empty($update)) {
            if ($this->dm->getEventManager()->hasListeners(ODMEvents::onUpdatePrepared)) {
                $this->dm->getEventManager()->dispatchEvent(
                    ODMEvents::onUpdatePrepared, new OnUpdatePreparedArgs($this->dm, $document, $update)
                );
            }
            $id = $this->class->getDatabaseIdentifierValue($id);

            if ((isset($update[$this->cmd . 'pushAll']) || isset($update[$this->cmd . 'pullAll'])) && isset($update[$this->cmd . 'set'])) {
                $tempUpdate = array($this->cmd . 'set' => $update[$this->cmd . 'set']);
                unset($update[$this->cmd . 'set']);
                $this->collection->update(array('_id' => $id), $tempUpdate);
            }

            /**
             * temporary fix for @link http://jira.mongodb.org/browse/SERVER-1050
             * atomic modifiers $pushAll and $pullAll, $push, $pop and $pull
             * are not allowed on the same field in one update
             */
            if (isset($update[$this->cmd . 'pushAll']) && isset($update[$this->cmd . 'pullAll'])) {
                $fields = array_intersect(
                    array_keys($update[$this->cmd . 'pushAll']),
                    array_keys($update[$this->cmd . 'pullAll'])
                );
                if ( ! empty($fields)) {
                    $tempUpdate = array();
                    foreach ($fields as $field) {
                        $tempUpdate[$field] = $update[$this->cmd . 'pullAll'][$field];
                        unset($update[$this->cmd . 'pullAll'][$field]);
                    }
                    if (empty($update[$this->cmd . 'pullAll'])) {
                        unset($update[$this->cmd . 'pullAll']);
                    }
                    $tempUpdate = array(
                        $this->cmd . 'pullAll' => $tempUpdate
                    );
                    $this->collection->update(array('_id' => $id), $tempUpdate);
                }
            }
            $this->collection->update(array('_id' => $id), $update);
        }
    }

    /**
     * Removes document from mongo
     *
     * @param mixed $document
     */
    public function delete($document)
    {
        $id = $this->uow->getDocumentIdentifier($document);

        $this->collection->remove(array(
            '_id' => $this->class->getDatabaseIdentifierValue($id)
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
        $changeset = $this->uow->getDocumentChangeSet($document);
        $insertData = array();
        foreach ($this->class->fieldMappings as $mapping) {
            if (isset($mapping['notSaved']) && $mapping['notSaved'] === true) {
                continue;
            }
            $new = isset($changeset[$mapping['fieldName']][1]) ? $changeset[$mapping['fieldName']][1] : null;
            if ($new === null && $mapping['nullable'] === false) {
                continue;
            }
            if ($this->class->isIdentifier($mapping['fieldName'])) {
                $insertData['_id'] = $this->prepareValue($mapping, $new);
                continue;
            }
            $insertData[$mapping['fieldName']] = $this->prepareValue($mapping, $new);
            if (isset($mapping['reference'])) {
                $scheduleForUpdate = false;
                if ($mapping['type'] === 'one') {
                    if (null === $insertData[$mapping['fieldName']][$this->cmd . 'id']) {
                        $scheduleForUpdate = true;
                    }
                } elseif ($mapping['type'] === 'many') {
                    foreach ($insertData[$mapping['fieldName']] as $ref) {
                        if (null === $ref[$this->cmd . 'id']) {
                            $scheduleForUpdate = true;
                            break;
                        }
                    }
                }
                if ($scheduleForUpdate) {
                    unset($insertData[$mapping['fieldName']]);
                    $id = spl_object_hash($document);
                    $this->documentsToUpdate[$id] = $document;
                    $this->fieldsToUpdate[$id][$mapping['fieldName']] = array($mapping, $new);
                }
            }
        }
        // add discriminator if the class has one
        if ($this->class->hasDiscriminator()) {
            $insertData[$this->class->discriminatorField['name']] = $this->class->discriminatorValue;
        }
        return $insertData;
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
        $class = $this->dm->getClassMetadata(get_class($document));
        $changeset = $this->uow->getDocumentChangeSet($document);
        $result = array();
        foreach ($class->fieldMappings as $mapping) {
            if (isset($mapping['notSaved']) && $mapping['notSaved'] === true) {
                continue;
            }
            $old = isset($changeset[$mapping['fieldName']][0]) ? $changeset[$mapping['fieldName']][0] : null;
            $new = isset($changeset[$mapping['fieldName']][1]) ? $changeset[$mapping['fieldName']][1] : null;

            if ($mapping['type'] === 'many' || $mapping['type'] === 'collection') {               
                if (isset($mapping['embedded']) && $new) {
                    foreach ($new as $k => $v) {
                        if ( ! isset($old[$k])) {
                            continue;
                        }
                        $update = $this->prepareUpdateData($v);
                        foreach ($update as $cmd => $values) {
                            foreach ($values as $key => $value) {
                                $result[$cmd][$mapping['fieldName'] . '.' . $k . '.' . $key] = $value;
                            }
                        }
                    }
                }
                if ($mapping['strategy'] === 'pushPull') {
                    if ($old !== $new) {
                        $old = $old ? $old : array();
                        $new = $new ? $new : array();
                        $deleteDiff = array_udiff_assoc($old, $new, function($a, $b) {return $a === $b ? 0 : 1; });
                        $insertDiff = array_udiff_assoc($new, $old, function($a, $b) {return $a === $b ? 0 : 1;});

                        // insert diff
                        if ($insertDiff) {
                            $result[$this->cmd . 'pushAll'][$mapping['fieldName']] = $this->prepareValue($mapping, $insertDiff);
                        }
                        // delete diff
                        if ($deleteDiff) {
                            $result[$this->cmd . 'pullAll'][$mapping['fieldName']] = $this->prepareValue($mapping, $deleteDiff);
                        }
                    }
                } elseif ($mapping['strategy'] === 'set') {
                    if ($old !== $new) {
                        $new = $this->prepareValue($mapping, $new);
                        $old = $this->prepareValue($mapping, $old);
                        $result[$this->cmd . 'set'][$mapping['fieldName']] = $new;
                    }
                }
            } else {
                if ($old !== $new) {
                    if ($mapping['type'] === 'increment') {
                        $new = $this->prepareValue($mapping, $new);
                        $old = $this->prepareValue($mapping, $old);
                        if ($new >= $old) {
                            $result[$this->cmd . 'inc'][$mapping['fieldName']] = $new - $old;
                        } else {
                            $result[$this->cmd . 'inc'][$mapping['fieldName']] = ($old - $new) * -1;
                        }
                    } else {
                        if (isset($mapping['embedded']) && $mapping['type'] === 'one') {
                            $embeddedDocument = $class->getFieldValue($document, $mapping['fieldName']);
                            $update = $this->prepareUpdateData($embeddedDocument);
                            foreach ($update as $cmd => $values) {
                                foreach ($values as $key => $value) {
                                    $result[$cmd][$mapping['fieldName'] . '.' . $key] = $value;
                                }
                            }
                        } else {
                            $old = $this->prepareValue($mapping, $old);
                            $new = $this->prepareValue($mapping, $new);
                            if (isset($new) || $mapping['nullable'] === true) {
                                $result[$this->cmd . 'set'][$mapping['fieldName']] = $new;
                            } else {
                                $result[$this->cmd . 'unset'][$mapping['fieldName']] = true;
                            }
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     *
     * @param array $mapping
     * @param mixed $value
     */
    private function prepareValue(array $mapping, $value)
    {
        if ($value === null) {
            return null;
        }
        if ($mapping['type'] === 'many') {
            $prepared = array();

            $oneMapping = $mapping;
            $oneMapping['type'] = 'one';
            foreach ($value as $rawValue) {
                $prepared[] = $this->prepareValue($oneMapping, $rawValue);
            }
        } elseif (isset($mapping['reference']) || isset($mapping['embedded'])) {
            if (isset($mapping['embedded'])) {
                $prepared = $this->prepareEmbeddedDocValue($mapping, $value);
            } elseif (isset($mapping['reference'])) {
                $prepared = $this->prepareReferencedDocValue($mapping, $value);
            }
        } else {
            $prepared = Type::getType($mapping['type'])->convertToDatabaseValue($value);
        }
        return $prepared;
    }

    /**
     * Gets the ClassMetadata instance of the document class this persister is used for.
     *
     * @return Doctrine\ODM\MongoDB\Mapping\ClassMetadata
     */
    public function getClassMetadata()
    {
        return $this->class;
    }

    /**
     * Refreshes a managed document.
     *
     * @param object $document The document to refresh.
     */
    public function refresh($document)
    {
        $id = $this->uow->getDocumentIdentifier($document);
        if ($this->dm->loadByID($this->class->name, $id) === null) {
            throw new \InvalidArgumentException(sprintf('Could not loadByID because ' . $this->class->name . ' '.$id . ' does not exist anymore.'));
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
        $result = $this->collection->findOne($query, $select);
        if ($result !== null) {
            return $this->uow->getOrCreateDocument($this->documentName, $result);
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
        $result = $this->collection->findOne(array(
            '_id' => $this->class->getDatabaseIdentifierValue($id)
        ));
        if ($result !== null) {
            return $this->uow->getOrCreateDocument($this->documentName, $result);
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
        $cursor = $this->collection->find($query, $select);
        return new MongoCursor($this->dm, $this->dm->getHydrator(), $this->class, $cursor);
    }

    /**
     * Returns the reference representation to be stored in mongodb or null if not applicable.
     *
     * @param array $referenceMapping
     * @param Document $document
     * @return array|null
     */
    private function prepareReferencedDocValue(array $referenceMapping, $document)
    {
        $class = $this->dm->getClassMetadata(get_class($document));
        $id = $this->uow->getDocumentIdentifier($document);
        if (null !== $id) {
            $id = $class->getDatabaseIdentifierValue($id);
        }
        $ref = array(
            $this->cmd . 'ref' => $class->getCollection(),
            $this->cmd . 'id' => $id,
            $this->cmd . 'db' => $class->getDB()
        );
        if ( ! isset($referenceMapping['targetDocument'])) {
            $discriminatorField = isset($referenceMapping['discriminatorField']) ? $referenceMapping['discriminatorField'] : '_doctrine_class_name';
            $discriminatorValue = isset($referenceMapping['discriminatorMap']) ? array_search($class->getName(), $referenceMapping['discriminatorMap']) : $class->getName();
            $ref[$discriminatorField] = $discriminatorValue;
        }
        return $ref;
    }

    /**
     * Prepares array of values to be stored in mongo to represent embedded object.
     *
     * @param array $embeddedMapping
     * @param Document $embeddedDocument
     * @return array
     */
    private function prepareEmbeddedDocValue(array $embeddedMapping, $embeddedDocument)
    {
        $className = is_object($embeddedDocument) ? get_class($embeddedDocument) : $embeddedDocument['className'];
        $class = $this->dm->getClassMetadata($className);
        $embeddedDocumentValue = array();
        foreach ($class->fieldMappings as $mapping) {
            if (is_object($embeddedDocument)) {
                $rawValue = $class->getFieldValue($embeddedDocument, $mapping['fieldName']);
            } else {
                $rawValue = isset($embeddedDocument[$mapping['fieldName']]) ? $embeddedDocument[$mapping['fieldName']] : null;
            }
            if (isset($mapping['notSaved']) && $mapping['notSaved'] === true) {
                continue;
            }
            if ($rawValue === null && $mapping['nullable'] === false) {
                continue;
            }
            if (isset($mapping['embedded']) || isset($mapping['reference'])) {
                if (isset($mapping['embedded'])) {
                    if ($mapping['type'] == 'many') {
                        $value = array();
                        foreach ($rawValue as $embeddedDoc) {
                            $value[] = $this->prepareEmbeddedDocValue($mapping, $embeddedDoc);
                        }
                    } elseif ($mapping['type'] == 'one') {
                        $value = $this->prepareEmbeddedDocValue($mapping, $rawValue);
                    }
                } elseif (isset($mapping['reference'])) {
                    if ($mapping['type'] == 'many') {
                         $value = array();
                        foreach ($rawValue as $referencedDoc) {
                            $value[] = $this->prepareReferencedDocValue($mapping, $referencedDoc);
                        }
                    } else {
                        $value = $this->prepareReferencedDocValue($mapping, $rawValue);
                    }
                }
            } else {
                $value = Type::getType($mapping['type'])->convertToDatabaseValue($rawValue);
            }
            $embeddedDocumentValue[$mapping['fieldName']] = $value;
        }
        if ( ! isset($embeddedMapping['targetDocument'])) {
            $discriminatorField = isset($embeddedMapping['discriminatorField']) ? $embeddedMapping['discriminatorField'] : '_doctrine_class_name';
            $discriminatorValue = isset($embeddedMapping['discriminatorMap']) ? array_search($class->getName(), $embeddedMapping['discriminatorMap']) : $class->getName();
            $embeddedDocumentValue[$discriminatorField] = $discriminatorValue;
        }
        return $embeddedDocumentValue;
    }

    /**
     * Checks whether the given managed document exists in the database.
     *
     * @param object $document
     * @return boolean TRUE if the document exists in the database, FALSE otherwise.
     */
    public function exists($document)
    {
        $id = $this->class->getIdentifierObject($document);
        return (bool) $this->collection->findOne(array(array('_id' => $id)), array('_id'));
    }
}
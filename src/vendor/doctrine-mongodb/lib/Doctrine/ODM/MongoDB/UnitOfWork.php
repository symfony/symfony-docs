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

use Doctrine\ODM\MongoDB\DocumentManager,
    Doctrine\ODM\MongoDB\Internal\CommitOrderCalculator,
    Doctrine\ODM\MongoDB\Mapping\ClassMetadata,
    Doctrine\ODM\MongoDB\Proxy\Proxy,
    Doctrine\ODM\MongoDB\Mapping\Types\Type,
    Doctrine\ODM\MongoDB\Event\LifecycleEventArgs,
    Doctrine\ODM\MongoDB\PersistentCollection,
    Doctrine\Common\Collections\Collection,
    Doctrine\Common\NotifyPropertyChanged,
    Doctrine\Common\PropertyChangedListener,
    Doctrine\Common\Collections\ArrayCollection;

/**
 * The UnitOfWork is responsible for tracking changes to objects during an
 * "object-level" transaction and for writing out changes to the database
 * in the correct order.
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @since       1.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Roman Borschel <roman@code-factory.org>
 */
class UnitOfWork implements PropertyChangedListener
{
    /**
     * An document is in MANAGED state when its persistence is managed by an DocumentManager.
     */
    const STATE_MANAGED = 1;

    /**
     * An document is new if it has just been instantiated (i.e. using the "new" operator)
     * and is not (yet) managed by an DocumentManager.
     */
    const STATE_NEW = 2;

    /**
     * A detached document is an instance with a persistent identity that is not
     * (or no longer) associated with an DocumentManager (and a UnitOfWork).
     */
    const STATE_DETACHED = 3;

    /**
     * A removed document instance is an instance with a persistent identity,
     * associated with an DocumentManager, whose persistent state has been
     * deleted (or is scheduled for deletion).
     */
    const STATE_REMOVED = 4;

    /**
     * The identity map that holds references to all managed documents that have
     * an identity. The documents are grouped by their class name.
     * Since all classes in a hierarchy must share the same identifier set,
     * we always take the root class name of the hierarchy.
     *
     * @var array
     */
    private $identityMap = array();

    /**
     * Map of all identifiers of managed documents.
     * Keys are object ids (spl_object_hash).
     *
     * @var array
     */
    private $documentIdentifiers = array();

    /**
     * Map of the original document data of managed documents.
     * Keys are object ids (spl_object_hash). This is used for calculating changesets
     * at commit time.
     *
     * @var array
     * @internal Note that PHPs "copy-on-write" behavior helps a lot with memory usage.
     *           A value will only really be copied if the value in the document is modified
     *           by the user.
     */
    private $originalDocumentData = array();

    /**
     * Map of document changes. Keys are object ids (spl_object_hash).
     * Filled at the beginning of a commit of the UnitOfWork and cleaned at the end.
     *
     * @var array
     */
    private $documentChangeSets = array();

    /**
     * The (cached) states of any known documents.
     * Keys are object ids (spl_object_hash).
     *
     * @var array
     */
    private $documentStates = array();

    /**
     * Map of documents that are scheduled for dirty checking at commit time.
     * This is only used for documents with a change tracking policy of DEFERRED_EXPLICIT.
     * Keys are object ids (spl_object_hash).
     * 
     * @var array
     * @todo rename: scheduledForSynchronization
     */
    private $scheduledForDirtyCheck = array();

    /**
     * A list of all pending document insertions.
     *
     * @var array
     */
    private $documentInsertions = array();

    /**
     * A list of all pending document updates.
     *
     * @var array
     */
    private $documentUpdates = array();

    /**
     * A list of all pending document deletions.
     *
     * @var array
     */
    private $documentDeletions = array();

    /**
     * List of collections visited during changeset calculation on a commit-phase of a UnitOfWork.
     * At the end of the UnitOfWork all these collections will make new snapshots
     * of their data.
     *
     * @var array
     */
    private $visitedCollections = array();

    /**
     * The DocumentManager that "owns" this UnitOfWork instance.
     *
     * @var Doctrine\ODM\MongoDB\DocumentManager
     */
    private $dm;

    /**
     * The calculator used to calculate the order in which changes to
     * documents need to be written to the database.
     *
     * @var Doctrine\ODM\MongoDB\Internal\CommitOrderCalculator
     */
    private $commitOrderCalculator;

    /**
     * The EventManager used for dispatching events.
     *
     * @var EventManager
     */
    private $evm;

    /**
     * The Hydrator used for hydrating array Mongo documents to Doctrine object documents.
     *
     * @var string
     */
    private $hydrator;

    protected $documentPersisters = array();

    /**
     * Initializes a new UnitOfWork instance, bound to the given DocumentManager.
     *
     * @param Doctrine\ODM\MongoDB\DocumentManager $dm
     */
    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
        $this->evm = $dm->getEventManager();
        $this->hydrator = $dm->getHydrator();
    }

    /**
     * Get the document persister instance for the given document name
     *
     * @param string $documentName 
     * @return BasicDocumentPersister
     */
    public function getDocumentPersister($documentName)
    {
        if ( ! isset($this->documentPersisters[$documentName])) {
            $class = $this->dm->getClassMetadata($documentName);
            $this->documentPersisters[$documentName] = new Persisters\BasicDocumentPersister($this->dm, $class);
        }
        return $this->documentPersisters[$documentName];
    }

    /**
     * Set the document persister instance to use for the given document name
     *
     * @param string $documentName 
     * @param BasicDocumentPersister $persister
     */
    public function setDocumentPersister($documentName, Persisters\BasicDocumentPersister $persister)
    {
        $this->documentPersisters[$documentName] = $persister;
    }

    /**
     * Commits the UnitOfWork, executing all operations that have been postponed
     * up to this point. The state of all managed documents will be synchronized with
     * the database.
     * 
     * The operations are executed in the following order:
     * 
     * 1) All document insertions
     * 2) All document updates
     * 3) All collection deletions
     * 4) All collection updates
     * 5) All document deletions
     * 
     */
    public function commit()
    {
        // Compute changes done since last commit.
        $this->computeChangeSets();

        if ( ! ($this->documentInsertions ||
                $this->documentDeletions ||
                $this->documentUpdates)) {
            return; // Nothing to do.
        }

        // Raise onFlush
        if ($this->evm->hasListeners(ODMEvents::onFlush)) {
            $this->evm->dispatchEvent(ODMEvents::onFlush, new Event\OnFlushEventArgs($this->dm));
        }

        // Now we need a commit order to maintain referential integrity
        $commitOrder = $this->getCommitOrder();

        if ($this->documentInsertions) {
            foreach ($commitOrder as $class) {
                $this->executeInserts($class);
            }
            foreach ($commitOrder as $class) {
                $this->executeReferenceUpdates($class);
            }
        }
        
        if ($this->documentUpdates) {
            foreach ($commitOrder as $class) {
                $this->executeUpdates($class);
            }
        }

        // Document deletions come last and need to be in reverse commit order
        if ($this->documentDeletions) {
            for ($count = count($commitOrder), $i = $count - 1; $i >= 0; --$i) {
                $this->executeDeletions($commitOrder[$i]);
            }
        }

        // Take new snapshots from visited collections
        foreach ($this->visitedCollections as $coll) {
            $coll->takeSnapshot();
        }

        // Clear up
        $this->documentInsertions =
        $this->documentUpdates =
        $this->documentDeletions =
        $this->documentChangeSets =
        $this->visitedCollections =
        $this->scheduledForDirtyCheck = array();
    }

    /**
     * Executes reference updates
     *
     * @param Doctrine\ODM\MongoDB\Mapping\ClassMetadata $class
     */
    private function executeReferenceUpdates(ClassMetadata $class)
    {
        $className = $class->name;
        $persister = $this->getDocumentPersister($className);
        $persister->executeReferenceUpdates();
    }

    /**
     * Gets the changeset for an document.
     *
     * @return array
     */
    public function getDocumentChangeSet($document)
    {
        $oid = spl_object_hash($document);
        if (isset($this->documentChangeSets[$oid])) {
            return $this->documentChangeSets[$oid];
        }
        return array();
    }

    public function getDocumentChangeSets()
    {
        return $this->documentChangeSets;
    }

    /**
     * Computes the changes that happened to a single document.
     *
     * Modifies/populates the following properties:
     *
     * {@link _originalDocumentData}
     * If the document is NEW or MANAGED but not yet fully persisted (only has an id)
     * then it was not fetched from the database and therefore we have no original
     * document data yet. All of the current document data is stored as the original document data.
     *
     * {@link _documentChangeSets}
     * The changes detected on all properties of the document are stored there.
     * A change is a tuple array where the first entry is the old value and the second
     * entry is the new value of the property. Changesets are used by persisters
     * to INSERT/UPDATE the persistent document state.
     *
     * {@link _documentUpdates}
     * If the document is already fully MANAGED (has been fetched from the database before)
     * and any changes to its properties are detected, then a reference to the document is stored
     * there to mark it for an update.
     *
     * @param object $parentDocument The top most parent document of the document we are computing.
     * @param ClassMetadata $class The class descriptor of the document.
     * @param object $document The document for which to compute the changes.
     */
    public function computeChangeSet($parentDocument, Mapping\ClassMetadata $class, $document)
    {
        if ( ! $class->isInheritanceTypeNone()) {
            $class = $this->dm->getClassMetadata(get_class($document));
        }
        
        $oid = spl_object_hash($document);
        $parentOid = spl_object_hash($parentDocument);

        $actualData = array();
        foreach ($class->fieldMappings as $name => $mapping) {
            if ( ! $class->isIdentifier($name) || $class->getAllowCustomID()) {
                $actualData[$name] = $class->getFieldValue($document, $mapping['fieldName']);
            }
            if (($class->isCollectionValuedReference($name) || $class->isCollectionValuedEmbed($name))
                    && $actualData[$name] !== null && ! ($actualData[$name] instanceof PersistentCollection)) {
                // If $actualData[$name] is not a Collection then use an ArrayCollection.
                if ( ! $actualData[$name] instanceof Collection) {
                    $actualData[$name] = new ArrayCollection($actualData[$name]);
                }

                // Inject PersistentCollection
                if ($class->isCollectionValuedReference($name)) {
                    $coll = new PersistentCollection($actualData[$name], $this->dm);
                } else {
                    $coll = new PersistentCollection($actualData[$name]);
                }
                $coll->setOwner($document, $mapping);
                $coll->setDirty( ! $coll->isEmpty());
                $class->reflFields[$name]->setValue($document, $coll);
                $actualData[$name] = $coll;
            }
            if ($class->isSingleValuedEmbed($name) && is_object($actualData[$name])) {
                $embeddedDocument = $actualData[$name];
                $embeddedMetadata = $this->dm->getClassMetadata(get_class($embeddedDocument));
                $actualData[$name] = array();
                foreach ($embeddedMetadata->fieldMappings as $mapping) {
                    $actualData[$name][$mapping['fieldName']] = $embeddedMetadata->getFieldValue($embeddedDocument, $mapping['fieldName']);
                }
                $actualData[$name]['className'] = $embeddedMetadata->name;
            }
        }

        if ( ! isset($this->originalDocumentData[$oid])) {
            // Document is either NEW or MANAGED but not yet fully persisted (only has an id).
            // These result in an INSERT.
            $this->originalDocumentData[$oid] = $actualData;
            $changeSet = array();
            foreach ($actualData as $propName => $actualValue) {
                $changeSet[$propName] = array(null, $actualValue);
            }
            $this->documentChangeSets[$oid] = $changeSet;
        } else {
            // Document is "fully" MANAGED: it was already fully persisted before
            // and we have a copy of the original data
            $originalData = $this->originalDocumentData[$oid];
            $isChangeTrackingNotify = $class->isChangeTrackingNotify();
            $changeSet = $isChangeTrackingNotify ? $this->documentChangeSets[$oid] : array();

            foreach ($actualData as $propName => $actualValue) {
                $orgValue = isset($originalData[$propName]) ? $originalData[$propName] : null;
                if ($orgValue instanceof PersistentCollection) {
                    $orgValue = $orgValue->getSnapshot();
                }
                if ($actualValue instanceof PersistentCollection) {
                    $actualValue = $actualValue->toArray();
                }
                if ((isset($class->fieldMappings[$propName]['embedded']) && $class->fieldMappings[$propName]['type'] === 'one')
                        || (isset($class->fieldMappings[$propName]['reference']) && $class->fieldMappings[$propName]['type'] === 'one')) {
                    if ($orgValue !== $actualValue) {
                        $changeSet[$propName] = array($orgValue, $actualValue);
                    }
                } else if ($isChangeTrackingNotify) {
                    continue;
                } else if (isset($class->fieldMappings[$propName]['type']) && $class->fieldMappings[$propName]['type'] === 'many') {
                    $changeSet[$propName] = array($orgValue, $actualValue);
                } else if (is_object($orgValue) && $orgValue !== $actualValue) {
                    $changeSet[$propName] = array($orgValue, $actualValue);
                } else if ($orgValue != $actualValue || ($orgValue === null ^ $actualValue === null)) {
                    $changeSet[$propName] = array($orgValue, $actualValue);
                }
            }
            if ($changeSet) {
                $this->documentChangeSets[$oid] = $changeSet;
                $this->originalDocumentData[$oid] = $actualData;
                if ( ! $class->isEmbeddedDocument) {
                    $this->documentUpdates[$oid] = $document;
                }
            }
        }

        // Look for changes in references of the document
        foreach ($class->fieldMappings as $mapping) {
            if (isset($mapping['reference'])) {
                $val = $class->reflFields[$mapping['fieldName']]->getValue($document);
                if ($val !== null) {
                    $this->computeReferenceChanges($parentDocument, $mapping, $val);
                }
            } elseif (isset($mapping['embedded'])) {
                $val = $class->reflFields[$mapping['fieldName']]->getValue($document);
                if ($val !== null) {
                    $this->computeEmbeddedChanges($parentDocument, $mapping, $val);
                }
            }
        }
    }

    /**
     * Computes all the changes that have been done to documents and collections
     * since the last commit and stores these changes in the _documentChangeSet map
     * temporarily for access by the persisters, until the UoW commit is finished.
     */
    public function computeChangeSets()
    {
        // Compute changes for INSERTed documents first. This must always happen.
        foreach ($this->documentInsertions as $document) {
            $class = $this->dm->getClassMetadata(get_class($document));
            $this->computeChangeSet($document, $class, $document);
        }

        // Compute changes for other MANAGED documents. Change tracking policies take effect here.
        foreach ($this->identityMap as $className => $documents) {
            $class = $this->dm->getClassMetadata($className);
            if ($class->isEmbeddedDocument) {
                continue;
            }
            $class = $this->dm->getClassMetadata($className);

            // If change tracking is explicit or happens through notification, then only compute
            // changes on documents of that type that are explicitly marked for synchronization.
            $documentsToProcess = ! $class->isChangeTrackingDeferredImplicit() ?
                    (isset($this->scheduledForDirtyCheck[$className]) ?
                        $this->scheduledForDirtyCheck[$className] : array())
                    : $documents;

            foreach ($documentsToProcess as $document) {
                // Ignore uninitialized proxy objects
                if (/* $document is readOnly || */ $document instanceof Proxy && ! $document->__isInitialized__) {
                    continue;
                }
                // Only MANAGED documents that are NOT SCHEDULED FOR INSERTION are processed here.
                $oid = spl_object_hash($document);
                if ( ! isset($this->documentInsertions[$oid]) && isset($this->documentStates[$oid])) {
                    $this->computeChangeSet($document, $class, $document);
                }
            }
        }
    }

    /**
     * Computes the changes of a reference.
     *
     * @param object $parentDocument The top most parent document of the document we are computing.
     * @param array $mapping
     * @param mixed $value The value of the association.
     */
    private function computeReferenceChanges($parentDocument, $mapping, $value)
    {
        if ($value instanceof PersistentCollection && $value->isDirty()) {
            $this->visitedCollections[] = $value;
        }

        if ( ! $mapping['isCascadePersist']) {
            return; // "Persistence by reachability" only if persist cascade specified
        }

        // Look through the documents, and in any of their reference, for transient
        // enities, recursively. ("Persistence by reachability")
        if ($mapping['type'] === 'one') {
            if ($value instanceof Proxy && ! $value->__isInitialized__) {
                return; // Ignore uninitialized proxy objects
            }
            $value = array($value);
        } elseif ($value instanceof PersistentCollection) {
            $value = $value->unwrap();
        }

        foreach ($value as $entry) {
            $targetClass = $this->dm->getClassMetadata(get_class($entry));
            $state = $this->getDocumentState($entry, self::STATE_NEW);
            $oid = spl_object_hash($entry);
            if ($state == self::STATE_NEW) {
                if (isset($targetClass->lifecycleCallbacks[ODMEvents::prePersist])) {
                    $targetClass->invokeLifecycleCallbacks(ODMEvents::prePersist, $entry);
                }
                if ($this->evm->hasListeners(ODMEvents::prePersist)) {
                    $this->evm->dispatchEvent(ODMEvents::prePersist, new LifecycleEventArgs($entry, $this->dm));
                }

                $this->documentStates[$oid] = self::STATE_MANAGED;

                $this->documentInsertions[$oid] = $entry;

                $this->computeChangeSet($parentDocument, $targetClass, $entry);
                
            } elseif ($state == self::STATE_REMOVED) {
                throw MongoDBException::removedDocumentInCollectionDetected($entry, $mapping);
            }
            // MANAGED associated documents are already taken into account
            // during changeset calculation anyway, since they are in the identity map.
        }
    }

    /**
     * Computes the changes of an embedded document.
     *
     * @param object $parentDocument The top most parent document of the document we are computing.
     * @param array $mapping
     * @param mixed $value The value of the association.
     */
    private function computeEmbeddedChanges($parentDocument, $mapping, $value)
    {
        if ($value instanceof PersistentCollection && $value->isDirty()) {
            $this->visitedCollections[] = $value;
        }

        if ( ! $mapping['isCascadePersist']) {
            return; // "Persistence by reachability" only if persist cascade specified
        }

        if ($mapping['type'] === 'one') {
            $value = array($value);
        } elseif ($value instanceof PersistentCollection) {
            $value = $value->unwrap();
        }
        foreach ($value as $entry) {
            $targetClass = $this->dm->getClassMetadata(get_class($entry));
            $this->computeChangeSet($parentDocument, $targetClass, $entry);
        }
    }

    /**
     * INTERNAL:
     * Computes the changeset of an individual document, independently of the
     * computeChangeSets() routine that is used at the beginning of a UnitOfWork#commit().
     * 
     * The passed document must be a managed document. If the document already has a change set
     * because this method is invoked during a commit cycle then the change sets are added.
     * whereby changes detected in this method prevail.
     * 
     * @ignore
     * @param ClassMetadata $class The class descriptor of the document.
     * @param object $document The document for which to (re)calculate the change set.
     * @throws InvalidArgumentException If the passed document is not MANAGED.
     */
    public function recomputeSingleDocumentChangeSet($class, $document)
    {
        $oid = spl_object_hash($document);
        
        if ( ! isset($this->documentStates[$oid]) || $this->documentStates[$oid] != self::STATE_MANAGED) {
            throw new \InvalidArgumentException('Document must be managed.');
        }

        if ( ! $class->isInheritanceTypeNone()) {
            $class = $this->dm->getClassMetadata(get_class($document));
        }

        $actualData = array();
        foreach ($class->reflFields as $name => $refProp) {
            if ( ! $class->isIdentifier($name)) {
                $actualData[$name] = $refProp->getValue($document);
            }
        }

        $originalData = $this->originalDocumentData[$oid];
        $changeSet = array();

        foreach ($actualData as $propName => $actualValue) {
            $orgValue = isset($originalData[$propName]) ? $originalData[$propName] : null;
            if (is_object($orgValue) && $orgValue !== $actualValue) {
                $changeSet[$propName] = array($orgValue, $actualValue);
            } elseif ($orgValue != $actualValue || ($orgValue === null ^ $actualValue === null)) {
                $changeSet[$propName] = array($orgValue, $actualValue);
            }
        }

        if ($changeSet) {
            if (isset($this->documentChangeSets[$oid])) {
                $this->documentChangeSets[$oid] = $changeSet + $this->documentChangeSets[$oid];
            }
            $this->originalDocumentData[$oid] = $actualData;
        }
    }

    /**
     * Executes all document insertions for documents of the specified type.
     *
     * @param Doctrine\ODM\MongoDB\Mapping\ClassMetadata $class
     */
    private function executeInserts($class)
    {
        $className = $class->name;
        $persister = $this->getDocumentPersister($className);
        $collection = $this->dm->getDocumentCollection($className);

        $hasLifecycleCallbacks = isset($class->lifecycleCallbacks[ODMEvents::postPersist]);
        $hasListeners = $this->evm->hasListeners(ODMEvents::postPersist);
        if ($hasLifecycleCallbacks || $hasListeners) {
            $documents = array();
        }

        $inserts = array();
        foreach ($this->documentInsertions as $oid => $document) {
            if (get_class($document) === $className) {
                $persister->addInsert($document);
                unset($this->documentInsertions[$oid]);
                if ($hasLifecycleCallbacks || $hasListeners) {
                    $documents[] = $document;
                }
            }
        }

        $postInsertIds = $persister->executeInserts();

        if ($postInsertIds) {
            foreach ($postInsertIds as $pair) {
                list($id, $document) = $pair;
                $oid = spl_object_hash($document);
                $class->setIdentifierValue($document, $id);
                $this->documentIdentifiers[$oid] = $id;
                $this->documentStates[$oid] = self::STATE_MANAGED;
                $this->originalDocumentData[$oid][$class->identifier] = $id;
                $this->addToIdentityMap($document);
            }
        }

        if ($hasLifecycleCallbacks || $hasListeners) {
            foreach ($documents as $document) {
                if ($hasLifecycleCallbacks) {
                    $class->invokeLifecycleCallbacks(ODMEvents::postPersist, $document);
                }
                if ($hasListeners) {
                    $this->evm->dispatchEvent(ODMEvents::postPersist, new LifecycleEventArgs($document, $this->dm));
                }
            }
        }
    }

    /**
     * Executes all document updates for documents of the specified type.
     *
     * @param Doctrine\ODM\MongoDB\Mapping\ClassMetadata $class
     */
    private function executeUpdates($class)
    {
        $className = $class->name;
        $persister = $this->getDocumentPersister($className);

        $hasPreUpdateLifecycleCallbacks = isset($class->lifecycleCallbacks[ODMEvents::preUpdate]);
        $hasPreUpdateListeners = $this->evm->hasListeners(ODMEvents::preUpdate);
        $hasPostUpdateLifecycleCallbacks = isset($class->lifecycleCallbacks[ODMEvents::postUpdate]);
        $hasPostUpdateListeners = $this->evm->hasListeners(ODMEvents::postUpdate);

        foreach ($this->documentUpdates as $oid => $document) {
            if (get_class($document) == $className || $document instanceof Proxy && $document instanceof $className) {
                if ($hasPreUpdateLifecycleCallbacks) {
                    $class->invokeLifecycleCallbacks(ODMEvents::preUpdate, $document);
                    $this->recomputeSingleDocumentChangeSet($class, $document);
                }
                
                if ($hasPreUpdateListeners) {
                    $this->evm->dispatchEvent(ODMEvents::preUpdate, new Event\PreUpdateEventArgs(
                        $document, $this->dm, $this->documentChangeSets[$oid])
                    );
                }

                $persister->update($document);
                unset($this->documentUpdates[$oid]);

                if ($hasPostUpdateLifecycleCallbacks) {
                    $class->invokeLifecycleCallbacks(ODMEvents::postUpdate, $document);
                }
                if ($hasPostUpdateListeners) {
                    $this->evm->dispatchEvent(ODMEvents::postUpdate, new LifecycleEventArgs($document, $this->dm));
                }
            }
        }
    }
    /**
     * Executes all document deletions for documents of the specified type.
     *
     * @param Doctrine\ODM\MongoDB\Mapping\ClassMetadata $class
     */
    private function executeDeletions($class)
    {
        $hasLifecycleCallbacks = isset($class->lifecycleCallbacks[ODMEvents::postRemove]);
        $hasListeners = $this->evm->hasListeners(ODMEvents::postRemove);

        $className = $class->name;
        $persister = $this->getDocumentPersister($className);
        $collection = $this->dm->getDocumentCollection($className);
        foreach ($this->documentDeletions as $oid => $document) {
            if (get_class($document) == $className || $document instanceof Proxy && $document instanceof $className) {
                $persister->delete($document);
                unset(
                    $this->documentDeletions[$oid],
                    $this->documentIdentifiers[$oid],
                    $this->originalDocumentData[$oid]
                );
                // Document with this $oid after deletion treated as NEW, even if the $oid
                // is obtained by a new document because the old one went out of scope.
                $this->documentStates[$oid] = self::STATE_NEW;

                if ($hasLifecycleCallbacks) {
                    $class->invokeLifecycleCallbacks(ODMEvents::postRemove, $document);
                }
                if ($hasListeners) {
                    $this->evm->dispatchEvent(ODMEvents::postRemove, new LifecycleEventArgs($document, $this->dm));
                }
            }
        }
    }

    /**
     * Gets the commit order.
     *
     * @return array
     */
    private function getCommitOrder(array $documentChangeSet = null)
    {
        if ($documentChangeSet === null) {
            $documentChangeSet = array_merge(
                $this->documentInsertions,
                $this->documentUpdates,
                $this->documentDeletions
            );
        }
        
        $calc = $this->getCommitOrderCalculator();
        
        // See if there are any new classes in the changeset, that are not in the
        // commit order graph yet (dont have a node).
        $newNodes = array();
        foreach ($documentChangeSet as $oid => $document) {
            $className = get_class($document);         
            if ( ! $calc->hasClass($className)) {
                $class = $this->dm->getClassMetadata($className);
                $calc->addClass($class);
                $newNodes[] = $class;
            }
        }

        // Calculate dependencies for new nodes
        foreach ($newNodes as $class) {
            $this->addDependencies($class, $calc);
        }

        $classes = $calc->getCommitOrder();
        foreach ($classes as $key => $class) {
            if ($class->isEmbeddedDocument) {
                unset($classes[$key]);
            }
        }
        return array_values($classes);
    }

    /**
     * Add dependencies recursively through embedded documents. Embedded documents
     * may have references to other documents so those need to be saved first.
     *
     * @param ClassMetadata $class
     * @param CommitOrderCalculator $calc
     */
    private function addDependencies($class, $calc)
    {
        foreach ($class->fieldMappings as $mapping) {
            if (isset($mapping['reference']) && isset($mapping['targetDocument'])) {
                $targetClass = $this->dm->getClassMetadata($mapping['targetDocument']);
                if ( ! $calc->hasClass($targetClass->name)) {
                    $calc->addClass($targetClass);
                }
                if ( ! $calc->hasDependency($targetClass, $class)) {
                    $calc->addDependency($targetClass, $class);
                }
            }
            if (isset($mapping['embedded']) && isset($mapping['targetDocument'])) {
                $targetClass = $this->dm->getClassMetadata($mapping['targetDocument']);
                if ( ! $calc->hasClass($targetClass->name)) {
                    $calc->addClass($targetClass);
                }
                if ( ! $calc->hasDependency($targetClass, $class)) {
                    $calc->addDependency($targetClass, $class);
                }

                // avoid infinite recursion
                if ($class != $targetClass) {
                    $this->addDependencies($targetClass, $calc);
                }
            }
        }
    }

    /**
     * Schedules an document for insertion into the database.
     * If the document already has an identifier, it will be added to the identity map.
     *
     * @param object $document The document to schedule for insertion.
     */
    public function scheduleForInsert($document)
    {
        $oid = spl_object_hash($document);

        if (isset($this->documentUpdates[$oid])) {
            throw new \InvalidArgumentException("Dirty document can not be scheduled for insertion.");
        }
        if (isset($this->documentDeletions[$oid])) {
            throw new \InvalidArgumentException("Removed document can not be scheduled for insertion.");
        }
        if (isset($this->documentInsertions[$oid])) {
            throw new \InvalidArgumentException("Document can not be scheduled for insertion twice.");
        }

        $this->documentInsertions[$oid] = $document;

        if (isset($this->documentIdentifiers[$oid])) {
            $this->addToIdentityMap($document);
        }
    }

    /**
     * Checks whether an document is scheduled for insertion.
     *
     * @param object $document
     * @return boolean
     */
    public function isScheduledForInsert($document)
    {
        return isset($this->documentInsertions[spl_object_hash($document)]);
    }

    /**
     * Schedules an document for being updated.
     *
     * @param object $document The document to schedule for being updated.
     */
    public function scheduleForUpdate($document)
    {
        $oid = spl_object_hash($document);
        if ( ! isset($this->documentIdentifiers[$oid])) {
            throw new \InvalidArgumentException("Document has no identity.");
        }
        if (isset($this->documentDeletions[$oid])) {
            throw new \InvalidArgumentException("Document is removed.");
        }

        if ( ! isset($this->documentUpdates[$oid]) && ! isset($this->documentInsertions[$oid])) {
            $this->documentUpdates[$oid] = $document;
        }
    }

    /**
     * Checks whether an document is registered as dirty in the unit of work.
     * Note: Is not very useful currently as dirty documents are only registered
     * at commit time.
     *
     * @param object $document
     * @return boolean
     */
    public function isScheduledForUpdate($document)
    {
        return isset($this->documentUpdates[spl_object_hash($document)]);
    }

    public function isScheduledForDirtyCheck($document)
    {
        $rootDocumentName = $this->dm->getClassMetadata(get_class($document))->rootDocumentName;
        return isset($this->scheduledForDirtyCheck[$rootDocumentName][spl_object_hash($document)]);
    }

    /**
     * INTERNAL:
     * Schedules an document for deletion.
     * 
     * @param object $document
     */
    public function scheduleForDelete($document)
    {
        $oid = spl_object_hash($document);
        
        if (isset($this->documentInsertions[$oid])) {
            if ($this->isInIdentityMap($document)) {
                $this->removeFromIdentityMap($document);
            }
            unset($this->documentInsertions[$oid]);
            return; // document has not been persisted yet, so nothing more to do.
        }

        if ( ! $this->isInIdentityMap($document)) {
            return; // ignore
        }

        $this->removeFromIdentityMap($document);

        if (isset($this->documentUpdates[$oid])) {
            unset($this->documentUpdates[$oid]);
        }
        if ( ! isset($this->documentDeletions[$oid])) {
            $this->documentDeletions[$oid] = $document;
        }
    }

    /**
     * Checks whether an document is registered as removed/deleted with the unit
     * of work.
     *
     * @param object $document
     * @return boolean
     */
    public function isScheduledForDelete($document)
    {
        return isset($this->documentDeletions[spl_object_hash($document)]);
    }

    /**
     * Checks whether an document is scheduled for insertion, update or deletion.
     * 
     * @param $document
     * @return boolean
     */
    public function isDocumentScheduled($document)
    {
        $oid = spl_object_hash($document);
        return isset($this->documentInsertions[$oid]) ||
                isset($this->documentUpdates[$oid]) ||
                isset($this->documentDeletions[$oid]);
    }

    /**
     * INTERNAL:
     * Registers an document in the identity map.
     * Note that documents in a hierarchy are registered with the class name of
     * the root document.
     *
     * @ignore
     * @param object $document  The document to register.
     * @return boolean  TRUE if the registration was successful, FALSE if the identity of
     *                  the document in question is already managed.
     */
    public function addToIdentityMap($document)
    {
        $classMetadata = $this->dm->getClassMetadata(get_class($document));
        if ($classMetadata->isEmbeddedDocument) {
            $id = spl_object_hash($document);
        } else {
            $id = $this->documentIdentifiers[spl_object_hash($document)];
            $id = $classMetadata->getPHPIdentifierValue($id);
        }
        if ($id === '') {
            throw new \InvalidArgumentException("The given document has no identity.");
        }
        $className = $classMetadata->rootDocumentName;
        if (isset($this->identityMap[$className][$id])) {
            return false;
        }
        $this->identityMap[$className][$id] = $document;
        if ($document instanceof NotifyPropertyChanged) {
            $document->addPropertyChangedListener($this);
        }
        return true;
    }

    /**
     * Gets the state of an document within the current unit of work.
     * 
     * NOTE: This method sees documents that are not MANAGED or REMOVED and have a
     *       populated identifier, whether it is generated or manually assigned, as
     *       DETACHED. This can be incorrect for manually assigned identifiers.
     *
     * @param object $document
     * @param integer $assume The state to assume if the state is not yet known. This is usually
     *                        used to avoid costly state lookups, in the worst case with a database
     *                        lookup.
     * @return int The document state.
     */
    public function getDocumentState($document, $assume = null)
    {
        $oid = spl_object_hash($document);
        if ( ! isset($this->documentStates[$oid])) {
            // State can only be NEW or DETACHED, because MANAGED/REMOVED states are known.
            // Note that you can not remember the NEW or DETACHED state in _documentStates since
            // the UoW does not hold references to such objects and the object hash can be reused.
            // More generally because the state may "change" between NEW/DETACHED without the UoW being aware of it.
            if ($assume === null) {
                $class = $this->dm->getClassMetadata(get_class($document));
                $id = $class->getIdentifierValue($document);
                if ( ! $id) {
                    return self::STATE_NEW;
                } else {
                    // Last try before db lookup: check the identity map.
                    if ($this->tryGetById($id, $class->rootDocumentName)) {
                        return self::STATE_DETACHED;
                    } else {
                        // db lookup
                        if ($this->getDocumentPersister(get_class($document))->exists($document)) {
                            return self::STATE_DETACHED;
                        } else {
                            return self::STATE_NEW;
                        }
                    }
                }
            } else {
                return $assume;
            }
        }
        return $this->documentStates[$oid];    }

    /**
     * INTERNAL:
     * Removes an document from the identity map. This effectively detaches the
     * document from the persistence management of Doctrine.
     *
     * @ignore
     * @param object $document
     * @return boolean
     */
    public function removeFromIdentityMap($document)
    {
        $oid = spl_object_hash($document);
        $classMetadata = $this->dm->getClassMetadata(get_class($document));
        $id = $this->documentIdentifiers[$oid];
        $id = $classMetadata->getPHPIdentifierValue($id);
        if ($id === '') {
            throw new \InvalidArgumentException("The given document has no identity.");
        }
        $className = $classMetadata->rootDocumentName;
        if (isset($this->identityMap[$className][$id])) {
            unset($this->identityMap[$className][$id]);
            $this->documentStates[$oid] = self::STATE_DETACHED;
            return true;
        }

        return false;
    }

    /**
     * INTERNAL:
     * Gets an document in the identity map by its identifier hash.
     *
     * @ignore
     * @param string $id
     * @param string $rootClassName
     * @return object
     */
    public function getById($id, $rootClassName)
    {
        return $this->identityMap[$rootClassName][$id];
    }

    /**
     * INTERNAL:
     * Tries to get an document by its identifier hash. If no document is found for
     * the given hash, FALSE is returned.
     *
     * @ignore
     * @param string $id
     * @param string $rootClassName
     * @return mixed The found document or FALSE.
     */
    public function tryGetById($id, $rootClassName)
    {
        return isset($this->identityMap[$rootClassName][$id]) ?
                $this->identityMap[$rootClassName][$id] : false;
    }

    /**
     * Schedules a document for dirty-checking at commit-time.
     *
     * @param object $document The document to schedule for dirty-checking.
     * @todo Rename: scheduleForSynchronization
     */
    public function scheduleForDirtyCheck($document)
    {
        $rootClassName = $this->dm->getClassMetadata(get_class($document))->rootDocumentName;
        $this->scheduledForDirtyCheck[$rootClassName][spl_object_hash($document)] = $document;
    }

    /**
     * Checks whether an document is registered in the identity map of this UnitOfWork.
     *
     * @param object $document
     * @return boolean
     */
    public function isInIdentityMap($document)
    {
        $oid = spl_object_hash($document);
        if ( ! isset($this->documentIdentifiers[$oid])) {
            return false;
        }
        $classMetadata = $this->dm->getClassMetadata(get_class($document));
        $id = $this->documentIdentifiers[$oid];
        $id = $classMetadata->getPHPIdentifierValue($id);
        if ($id === '') {
            return false;
        }
        
        return isset($this->identityMap[$classMetadata->rootDocumentName][$id]);
    }

    /**
     * INTERNAL:
     * Checks whether an identifier hash exists in the identity map.
     *
     * @ignore
     * @param string $id
     * @param string $rootClassName
     * @return boolean
     */
    public function containsId($id, $rootClassName)
    {
        return isset($this->identityMap[$rootClassName][$id]);
    }

    /**
     * Persists an document as part of the current unit of work.
     *
     * @param object $document The document to persist.
     */
    public function persist($document)
    {
        $class = $this->dm->getClassMetadata(get_class($document));
        if ($class->isEmbeddedDocument || $class->isMappedSuperclass) {
            throw MongoDBException::cannotPersistEmbeddedDocumentOrMappedSuperclass($class->name);
        }
        $visited = array();
        $this->doPersist($document, $visited);
    }

    /**
     * Saves an document as part of the current unit of work.
     * This method is internally called during save() cascades as it tracks
     * the already visited documents to prevent infinite recursions.
     * 
     * NOTE: This method always considers documents that are not yet known to
     * this UnitOfWork as NEW.
     *
     * @param object $document The document to persist.
     * @param array $visited The already visited documents.
     */
    private function doPersist($document, array &$visited)
    {
        $oid = spl_object_hash($document);
        if (isset($visited[$oid])) {
            return; // Prevent infinite recursion
        }

        $visited[$oid] = $document; // Mark visited

        $class = $this->dm->getClassMetadata(get_class($document));

        $documentState = $this->getDocumentState($document, self::STATE_NEW);
        if ($class->isEmbeddedDocument) {
            if ($documentState === self::STATE_NEW) {
                $this->registerManagedEmbeddedDocument($document, array());
            }
            switch ($documentState) {
                case self::STATE_MANAGED:
                    // Nothing to do, except if policy is "deferred explicit"
                    if ($class->isChangeTrackingDeferredExplicit()) {
                        $this->scheduleForDirtyCheck($document);
                    }
                    break;
                case self::STATE_NEW:
                    $this->registerManagedEmbeddedDocument($document, array());
                    break;
            }
        } else {
            switch ($documentState) {
                case self::STATE_MANAGED:
                    // Nothing to do, except if policy is "deferred explicit"
                    if ($class->isChangeTrackingDeferredExplicit()) {
                        $this->scheduleForDirtyCheck($document);
                    }
                    break;
                case self::STATE_NEW:
                    if (isset($class->lifecycleCallbacks[ODMEvents::prePersist])) {
                        $class->invokeLifecycleCallbacks(ODMEvents::prePersist, $document);
                    }
                    if ($this->evm->hasListeners(ODMEvents::prePersist)) {
                        $this->evm->dispatchEvent(ODMEvents::prePersist, new LifecycleEventArgs($document, $this->dm));
                    }

                    $this->documentStates[$oid] = self::STATE_MANAGED;
                
                    $this->scheduleForInsert($document);
                    break;
                case self::STATE_DETACHED:
                    throw new \InvalidArgumentException(
                            "Behavior of persist() for a detached document is not yet defined.");
                case self::STATE_REMOVED:
                    // Document becomes managed again
                    if ($this->isScheduledForDelete($document)) {
                        unset($this->documentDeletions[$oid]);
                    } else {
                        //FIXME: There's more to think of here...
                        $this->scheduleForInsert($document);
                    }
                    break;
                default:
                    throw MongoDBException::invalidDocumentState($documentState);
            }
        }
        
        $this->cascadePersist($document, $visited);
    }

    /**
     * Deletes an document as part of the current unit of work.
     *
     * @param object $document The document to remove.
     */
    public function remove($document)
    {
        $visited = array();
        $this->doRemove($document, $visited);
    }

    /**
     * Deletes an document as part of the current unit of work.
     *
     * This method is internally called during delete() cascades as it tracks
     * the already visited documents to prevent infinite recursions.
     *
     * @param object $document The document to delete.
     * @param array $visited The map of the already visited documents.
     * @throws InvalidArgumentException If the instance is a detached document.
     */
    private function doRemove($document, array &$visited)
    {
        $oid = spl_object_hash($document);
        if (isset($visited[$oid])) {
            return; // Prevent infinite recursion
        }

        $visited[$oid] = $document; // mark visited

        $class = $this->dm->getClassMetadata(get_class($document));
        $documentState = $this->getDocumentState($document);
        switch ($documentState) {
            case self::STATE_NEW:
            case self::STATE_REMOVED:
                // nothing to do
                break;
            case self::STATE_MANAGED:
                if (isset($class->lifecycleCallbacks[ODMEvents::preRemove])) {
                    $class->invokeLifecycleCallbacks(ODMEvents::preRemove, $document);
                }
                if ($this->evm->hasListeners(ODMEvents::preRemove)) {
                    $this->evm->dispatchEvent(ODMEvents::preRemove, new LifecycleEventArgs($document, $this->dm));
                }
                $this->scheduleForDelete($document);
                break;
            case self::STATE_DETACHED:
                throw MongoDBException::detachedDocumentCannotBeRemoved();
            default:
                throw MongoDBException::invalidDocumentState($documentState);
        }

        $this->cascadeRemove($document, $visited);
    }

    /**
     * Merges the state of the given detached document into this UnitOfWork.
     *
     * @param object $document
     * @return object The managed copy of the document.
     */
    public function merge($document)
    {
        $visited = array();
        return $this->doMerge($document, $visited);
    }

    /**
     * Executes a merge operation on an document.
     *
     * @param object $document
     * @param array $visited
     * @return object The managed copy of the document.
     * @throws InvalidArgumentException If the document instance is NEW.
     */
    private function doMerge($document, array &$visited, $prevManagedCopy = null, $mapping = null)
    {
        $class = $this->dm->getClassMetadata(get_class($document));
        $id = $class->getIdentifierValue($document);

        if ( ! $id) {
            throw new \InvalidArgumentException('New document detected during merge.'
                    . ' Persist the new document before merging.');
        }
        
        // MANAGED documents are ignored by the merge operation
        if ($this->getDocumentState($document, self::STATE_DETACHED) == self::STATE_MANAGED) {
            $managedCopy = $document;
        } else {
            // Try to look the document up in the identity map.
            $managedCopy = $this->tryGetById($id, $class->rootDocumentName);
            if ($managedCopy) {
                // We have the document in-memory already, just make sure its not removed.
                if ($this->getDocumentState($managedCopy) == self::STATE_REMOVED) {
                    throw new \InvalidArgumentException('Removed document detected during merge.'
                            . ' Can not merge with a removed document.');
                }
            } else {
                // We need to fetch the managed copy in order to merge.
                $managedCopy = $this->dm->find($class->name, $id);
            }

            if ($managedCopy === null) {
                throw new \InvalidArgumentException('New document detected during merge.'
                        . ' Persist the new document before merging.');
            }

            // Merge state of $document into existing (managed) document
            foreach ($class->reflFields as $name => $prop) {
                if ( ! isset($class->fieldMappings[$name]['reference'])) {
                    $prop->setValue($managedCopy, $prop->getValue($document));
                } else {
                    $mapping2 = $class->fieldMappings[$name];
                    if ($mapping2['type'] === 'one') {
                        if ( ! $assoc2['isCascadeMerge']) {
                            $other = $class->reflFields[$name]->getValue($document); //TODO: Just $prop->getValue($document)?
                            if ($other !== null) {
                                $targetClass = $this->dm->getClassMetadata($mapping2['targetDocument']);
                                $id = $targetClass->getIdentifierValue($other);
                                $reference = $this->dm->getReference($mapping2['targetDocument'], $id);
                                $prop->setValue($managedCopy, $reference);
                            }
                        }
                    } else {
                        $coll = new PersistentCollection(new ArrayCollection(), $this->dm);
                        $coll->setOwner($managedCopy, $mapping2);
                        $coll->setInitialized($mapping2['isCascadeMerge']);
                        $prop->setValue($managedCopy, $coll);
                    }
                }
                if ($class->isChangeTrackingNotify()) {
                    // Just treat all properties as changed, there is no other choice.
                    $this->propertyChanged($managedCopy, $name, null, $prop->getValue($managedCopy));
                }
            }
            if ($class->isChangeTrackingDeferredExplicit()) {
                $this->scheduleForDirtyCheck($document);
            }
        }

        if ($prevManagedCopy !== null) {
            $assocField = $mapping['fieldName'];
            $prevClass = $this->dm->getClassMetadata(get_class($prevManagedCopy));
            if ($mapping['type'] === 'one') {
                $prevClass->reflFields[$assocField]->setValue($prevManagedCopy, $managedCopy);
            } else {
                $prevClass->reflFields[$assocField]->getValue($prevManagedCopy)->unwrap()->add($managedCopy);
            }
        }

        $this->cascadeMerge($document, $managedCopy, $visited);

        return $managedCopy;
    }
    
    /**
     * Detaches an document from the persistence management. It's persistence will
     * no longer be managed by Doctrine.
     *
     * @param object $document The document to detach.
     */
    public function detach($document)
    {
        $visited = array();
        $this->doDetach($document, $visited);
    }
    
    /**
     * Executes a detach operation on the given document.
     * 
     * @param object $document
     * @param array $visited
     * @internal This method always considers documents with an assigned identifier as DETACHED.
     */
    private function doDetach($document, array &$visited)
    {
        $oid = spl_object_hash($document);
        if (isset($visited[$oid])) {
            return; // Prevent infinite recursion
        }

        $visited[$oid] = $document; // mark visited
        
        switch ($this->getDocumentState($document, self::STATE_DETACHED)) {
            case self::STATE_MANAGED:
                $this->removeFromIdentityMap($document);
                unset($this->documentInsertions[$oid], $this->documentUpdates[$oid],
                        $this->documentDeletions[$oid], $this->documentIdentifiers[$oid],
                        $this->documentStates[$oid], $this->originalDocumentData[$oid]);
                break;
            case self::STATE_NEW:
            case self::STATE_DETACHED:
                return;
        }
        
        $this->cascadeDetach($document, $visited);
    }
    
    /**
     * Refreshes the state of the given document from the database, overwriting
     * any local, unpersisted changes.
     * 
     * @param object $document The document to refresh.
     * @throws InvalidArgumentException If the document is not MANAGED.
     */
    public function refresh($document)
    {
        $visited = array();
        $this->doRefresh($document, $visited);
    }
    
    /**
     * Executes a refresh operation on an document.
     * 
     * @param object $document The document to refresh.
     * @param array $visited The already visited documents during cascades.
     * @throws InvalidArgumentException If the document is not MANAGED.
     */
    private function doRefresh($document, array &$visited)
    {
        $oid = spl_object_hash($document);
        if (isset($visited[$oid])) {
            return; // Prevent infinite recursion
        }

        $visited[$oid] = $document; // mark visited

        $class = $this->dm->getClassMetadata(get_class($document));
        if ($this->getDocumentState($document) == self::STATE_MANAGED) {
            $this->getDocumentPersister($class->name)->refresh($document);
        } else {
            throw new \InvalidArgumentException("Document is not MANAGED.");
        }
        
        $this->cascadeRefresh($document, $visited);
    }
    
    /**
     * Cascades a refresh operation to associated documents.
     *
     * @param object $document
     * @param array $visited
     */
    private function cascadeRefresh($document, array &$visited)
    {
        $class = $this->dm->getClassMetadata(get_class($document));
        foreach ($class->fieldMappings as $mapping) {
            if ( ! isset($mapping['reference']) || ! $mapping['isCascadeRefresh']) {
                continue;
            }
            if (isset($mapping['embedded'])) {
                $relatedDocuments = $class->reflFields[$mapping['fieldName']]->getValue($document);
                if (($relatedDocuments instanceof Collection || is_array($relatedDocuments))) {
                    if ($relatedDocuments instanceof PersistentCollection) {
                        // Unwrap so that foreach() does not initialize
                        $relatedDocuments = $relatedDocuments->unwrap();
                    }
                    foreach ($relatedDocuments as $relatedDocument) {
                        $this->cascadeRefresh($relatedDocument, $visited);
                    }
                } elseif ($relatedDocuments !== null) {
                    $this->cascadeRefresh($relatedDocuments, $visited);
                }
            } elseif (isset($mapping['reference'])) {
                $relatedDocuments = $class->reflFields[$mapping['fieldName']]->getValue($document);
                if (($relatedDocuments instanceof Collection || is_array($relatedDocuments))) {
                    if ($relatedDocuments instanceof PersistentCollection) {
                        // Unwrap so that foreach() does not initialize
                        $relatedDocuments = $relatedDocuments->unwrap();
                    }
                    foreach ($relatedDocuments as $relatedDocument) {
                        $this->doRefresh($relatedDocument, $visited);
                    }
                } elseif ($relatedDocuments !== null) {
                    $this->doRefresh($relatedDocuments, $visited);
                }
            }
        }
    }
    
    /**
     * Cascades a detach operation to associated documents.
     *
     * @param object $document
     * @param array $visited
     */
    private function cascadeDetach($document, array &$visited)
    {
        $class = $this->dm->getClassMetadata(get_class($document));
        foreach ($class->fieldMappings as $mapping) {
            if ( ! isset($mapping['embedded']) && (!isset($mapping['reference']) || ! $mapping['isCascadeDetach'])) {
                continue;
            }
            if (isset($mapping['embedded'])) {
                $relatedDocuments = $class->reflFields[$mapping['fieldName']]->getValue($document);
                if (($relatedDocuments instanceof Collection || is_array($relatedDocuments))) {
                    if ($relatedDocuments instanceof PersistentCollection) {
                        // Unwrap so that foreach() does not initialize
                        $relatedDocuments = $relatedDocuments->unwrap();
                    }
                    foreach ($relatedDocuments as $relatedDocument) {
                        $this->cascadeDetach($relatedDocument, $visited);
                    }
                } elseif ($relatedDocuments !== null) {
                    $this->cascadeDetach($relatedDocuments, $visited);
                }
            } elseif (isset($mapping['reference'])) {
                $relatedDocuments = $class->reflFields[$mapping['fieldName']]->getValue($document);
                if (($relatedDocuments instanceof Collection || is_array($relatedDocuments))) {
                    if ($relatedDocuments instanceof PersistentCollection) {
                        // Unwrap so that foreach() does not initialize
                        $relatedDocuments = $relatedDocuments->unwrap();
                    }
                    foreach ($relatedDocuments as $relatedDocument) {
                        $this->doDetach($relatedDocument, $visited);
                    }
                } elseif ($relatedDocuments !== null) {
                    $this->doDetach($relatedDocuments, $visited);
                }
            }
        }
    }

    /**
     * Cascades a merge operation to associated documents.
     *
     * @param object $document
     * @param object $managedCopy
     * @param array $visited
     */
    private function cascadeMerge($document, $managedCopy, array &$visited)
    {
        $class = $this->dm->getClassMetadata(get_class($document));
        foreach ($class->fieldMappings as $mapping) {
            if ( ! isset($mapping['embedded']) && (!isset($mapping['reference']) || ! $mapping['isCascadeMerge'])) {
                continue;
            }
            if (isset($mapping['embedded'])) {
                $relatedDocuments = $class->reflFields[$mapping['fieldName']]->getValue($document);
                if (($relatedDocuments instanceof Collection || is_array($relatedDocuments))) {
                    if ($relatedDocuments instanceof PersistentCollection) {
                        // Unwrap so that foreach() does not initialize
                        $relatedDocuments = $relatedDocuments->unwrap();
                    }
                    foreach ($relatedDocuments as $relatedDocument) {
                        $this->cascadeMerge($relatedDocument, $managedCopy, $visited);
                    }
                } elseif ($relatedDocuments !== null) {
                    $this->cascadeMerge($relatedDocuments, $managedCopy, $visited);
                }
            } elseif (isset($mapping['reference'])) {
                $relatedDocuments = $class->reflFields[$mapping['fieldName']]->getValue($document);
                if (($relatedDocuments instanceof Collection || is_array($relatedDocuments))) {
                    if ($relatedDocuments instanceof PersistentCollection) {
                        // Unwrap so that foreach() does not initialize
                        $relatedDocuments = $relatedDocuments->unwrap();
                    }
                    foreach ($relatedDocuments as $relatedDocument) {
                        $this->doMerge($relatedDocument, $visited);
                    }
                } elseif ($relatedDocuments !== null) {
                    $this->doMerge($relatedDocuments, $visited);
                }
            }
        }
    }

    /**
     * Cascades the save operation to associated documents.
     *
     * @param object $document
     * @param array $visited
     * @param array $insertNow
     */
    private function cascadePersist($document, array &$visited)
    {
        $class = $this->dm->getClassMetadata(get_class($document));
        foreach ($class->fieldMappings as $mapping) {
            if ( ! $mapping['isCascadePersist']) {
                continue;
            }
            if (isset($mapping['embedded']) || isset($mapping['reference'])) {
                $relatedDocuments = $class->reflFields[$mapping['fieldName']]->getValue($document);
                if (($relatedDocuments instanceof Collection || is_array($relatedDocuments))) {
                    if ($relatedDocuments instanceof PersistentCollection) {
                        // Unwrap so that foreach() does not initialize
                        $relatedDocuments = $relatedDocuments->unwrap();
                    }
                    foreach ($relatedDocuments as $relatedDocument) {
                        $this->doPersist($relatedDocument, $visited);
                    }
                } elseif ($relatedDocuments !== null) {
                    $this->doPersist($relatedDocuments, $visited);
                }
            }
        }
    }

    /**
     * Cascades the delete operation to associated documents.
     *
     * @param object $document
     * @param array $visited
     */
    private function cascadeRemove($document, array &$visited)
    {
        $class = $this->dm->getClassMetadata(get_class($document));
        foreach ($class->fieldMappings as $mapping) {
            if ( ! isset($mapping['embedded']) && (!isset($mapping['reference']) || ! $mapping['isCascadeRemove'])) {
                continue;
            }
            if (isset($mapping['embedded'])) {
                $relatedDocuments = $class->reflFields[$mapping['fieldName']]->getValue($document);
                if (($relatedDocuments instanceof Collection || is_array($relatedDocuments))) {
                    if ($relatedDocuments instanceof PersistentCollection) {
                        // Unwrap so that foreach() does not initialize
                        $relatedDocuments = $relatedDocuments->unwrap();
                    }
                    foreach ($relatedDocuments as $relatedDocument) {
                        $this->cascadeRemove($relatedDocument, $visited);
                    }
                } elseif ($relatedDocuments !== null) {
                    $this->cascadeRemove($relatedDocuments, $visited);
                }
            } elseif (isset($mapping['reference'])) {
                $relatedDocuments = $class->reflFields[$mapping['fieldName']]->getValue($document);
                if (($relatedDocuments instanceof Collection || is_array($relatedDocuments))) {
                    if ($relatedDocuments instanceof PersistentCollection) {
                        // Unwrap so that foreach() does not initialize
                        $relatedDocuments = $relatedDocuments->unwrap();
                    }
                    foreach ($relatedDocuments as $relatedDocument) {
                        $this->doRemove($relatedDocument, $visited);
                    }
                } elseif ($relatedDocuments !== null) {
                    $this->doRemove($relatedDocuments, $visited);
                }
            }
        }
    }

    /**
     * Gets the CommitOrderCalculator used by the UnitOfWork to order commits.
     *
     * @return Doctrine\ODM\MongoDB\Internal\CommitOrderCalculator
     */
    public function getCommitOrderCalculator()
    {
        if ($this->commitOrderCalculator === null) {
            $this->commitOrderCalculator = new Internal\CommitOrderCalculator;
        }
        return $this->commitOrderCalculator;
    }

    /**
     * Clears the UnitOfWork.
     */
    public function clear()
    {
        $this->identityMap =
        $this->documentIdentifiers =
        $this->originalDocumentData =
        $this->documentChangeSets =
        $this->documentStates =
        $this->scheduledForDirtyCheck =
        $this->documentInsertions =
        $this->documentUpdates =
        $this->documentDeletions = array();
        if ($this->commitOrderCalculator !== null) {
            $this->commitOrderCalculator->clear();
        }
    }

    public function isCollectionScheduledForDeletion(PersistentCollection $coll)
    {
        return in_array($coll, $this->collectionsDeletions, true);
    }

    /**
     * INTERNAL:
     * Creates an document. Used for reconstitution of documents during hydration.
     *
     * @ignore
     * @param string $className The name of the document class.
     * @param array $data The data for the document.
     * @param array $hints Any hints to account for during reconstitution/lookup of the document.
     * @return object The document instance.
     * @internal Highly performance-sensitive method.
     */
    public function getOrCreateDocument($className, $data, &$hints = array())
    {
        $class = $this->dm->getClassMetadata($className);
        if ($data instanceof \MongoGridFSFile) {
            $file = $data;
            $data = $file->file;
            $data[$class->file] = $file;
        }

        if ($class->hasDiscriminator()) {
            if (isset($data[$class->discriminatorField['name']])) {
                $type = $data[$class->discriminatorField['name']];
                $class = $this->dm->getClassMetadata($class->discriminatorMap[$data[$class->discriminatorField['name']]]);
                unset($data[$class->discriminatorField['name']]);
            }
        }

        $id = $class->getPHPIdentifierValue($data['_id']);
        if (isset($this->identityMap[$class->rootDocumentName][$id])) {
            $document = $this->identityMap[$class->rootDocumentName][$id];
            $oid = spl_object_hash($document);
            if ($document instanceof Proxy && ! $document->__isInitialized__) {
                $document->__isInitialized__ = true;
                $overrideLocalValues = true;
                if ($document instanceof NotifyPropertyChanged) {
                    $document->addPropertyChangedListener($this);
                }
            } else {
                $overrideLocalValues = isset($hints[Query::HINT_REFRESH]);
            }
            if ($overrideLocalValues) {
                $this->hydrator->hydrate($document, $data);
                $this->originalDocumentData[$oid] = $data;
            }
        } else {
            $document = $class->newInstance();
            $this->hydrator->hydrate($document, $data);
            $this->registerManaged($document, $id, $data);
        }
        if (isset($class->lifecycleCallbacks[ODMEvents::postLoad])) {
            $class->invokeLifecycleCallbacks(ODMEvents::postLoad, $document);
        }
        if ($this->evm->hasListeners(ODMEvents::postLoad)) {
            $this->evm->dispatchEvent(ODMEvents::postLoad, new LifecycleEventArgs($document, $this->dm));
        }
        return $document;
    }

    /**
     * Gets the identity map of the UnitOfWork.
     *
     * @return array
     */
    public function getIdentityMap()
    {
        return $this->identityMap;
    }

    /**
     * Gets the original data of an document. The original data is the data that was
     * present at the time the document was reconstituted from the database.
     *
     * @param object $document
     * @return array
     */
    public function getOriginalDocumentData($document)
    {
        $oid = spl_object_hash($document);
        if (isset($this->originalDocumentData[$oid])) {
            return $this->originalDocumentData[$oid];
        }
        return array();
    }
    
    /**
     * @ignore
     */
    public function setOriginalDocumentData($document, array $data)
    {
        $this->originalDocumentData[spl_object_hash($document)] = $data;
    }

    /**
     * INTERNAL:
     * Sets a property value of the original data array of an document.
     *
     * @ignore
     * @param string $oid
     * @param string $property
     * @param mixed $value
     */
    public function setOriginalDocumentProperty($oid, $property, $value)
    {
        $this->originalDocumentData[$oid][$property] = $value;
    }

    /**
     * Gets the identifier of an document.
     * The returned value is always an array of identifier values. If the document
     * has a composite identifier then the identifier values are in the same
     * order as the identifier field names as returned by ClassMetadata#getIdentifierFieldNames().
     *
     * @param object $document
     * @return array The identifier values.
     */
    public function getDocumentIdentifier($document)
    {
        return isset($this->documentIdentifiers[spl_object_hash($document)]) ?
            $this->documentIdentifiers[spl_object_hash($document)] : null;
    }

    /**
     * Checks whether the UnitOfWork has any pending insertions.
     *
     * @return boolean TRUE if this UnitOfWork has pending insertions, FALSE otherwise.
     */
    public function hasPendingInsertions()
    {
        return ! empty($this->documentInsertions);
    }

    /**
     * Calculates the size of the UnitOfWork. The size of the UnitOfWork is the
     * number of documents in the identity map.
     *
     * @return integer
     */
    public function size()
    {
        $count = 0;
        foreach ($this->identityMap as $documentSet) {
            $count += count($documentSet);
        }
        return $count;
    }

    /**
     * INTERNAL:
     * Registers a document as managed.
     *
     * @param object $document The document.
     * @param array $id The identifier values.
     * @param array $data The original document data.
     */
    public function registerManaged($document, $id, array $data)
    {
        $oid = spl_object_hash($document);
        $this->documentIdentifiers[$oid] = $id;
        $this->documentStates[$oid] = self::STATE_MANAGED;
        $this->originalDocumentData[$oid] = $data;
        $this->addToIdentityMap($document);
    }

    /**
     * INTERNAL:
     * Registers an embedded document as managed.
     *
     * @param object $document The document.
     * @param array $data The original document data.
     */
    public function registerManagedEmbeddedDocument($document, array $data)
    {
        $class = $this->dm->getClassMetadata(get_class($document));
        $oid = spl_object_hash($document);
        $this->documentStates[$oid] = self::STATE_MANAGED;
        $this->documentIdentifiers[$oid] = $oid;
        $this->originalDocumentData[$oid] = $data;
        $this->addToIdentityMap($document);
    }

    /**
     * INTERNAL:
     * Clears the property changeset of the document with the given OID.
     *
     * @param string $oid The document's OID.
     */
    public function clearDocumentChangeSet($oid)
    {
        unset($this->documentChangeSets[$oid]);
    }

    /* PropertyChangedListener implementation */

    /**
     * Notifies this UnitOfWork of a property change in an document.
     *
     * @param object $document The document that owns the property.
     * @param string $propertyName The name of the property that changed.
     * @param mixed $oldValue The old value of the property.
     * @param mixed $newValue The new value of the property.
     */
    public function propertyChanged($document, $propertyName, $oldValue, $newValue)
    {
        $oid = spl_object_hash($document);
        $class = $this->dm->getClassMetadata(get_class($document));

        if ( ! isset($class->fieldMappings[$propertyName])) {
            return; // ignore non-persistent fields
        }

        // Update changeset and mark document for synchronization
        $this->documentChangeSets[$oid][$propertyName] = array($oldValue, $newValue);
        if ( ! isset($this->scheduledForDirtyCheck[$class->rootDocumentName][$oid])) {
            $this->scheduleForDirtyCheck($document);
        }
    }

    /**
     * Gets the currently scheduled document insertions in this UnitOfWork.
     * 
     * @return array
     */
    public function getScheduledDocumentInsertions()
    {
        return $this->documentInsertions;
    }
    
    /**
     * Gets the currently scheduled document updates in this UnitOfWork.
     * 
     * @return array
     */
    public function getScheduledDocumentUpdates()
    {
        return $this->documentUpdates;
    }
    
    /**
     * Gets the currently scheduled document deletions in this UnitOfWork.
     * 
     * @return array
     */
    public function getScheduledDocumentDeletions()
    {
        return $this->documentDeletions;
    }
}

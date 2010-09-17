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

namespace Doctrine\ORM\Internal\Hydration;

use PDO,
    Doctrine\ORM\Mapping\ClassMetadata,
    Doctrine\ORM\PersistentCollection,
    Doctrine\ORM\Query,
    Doctrine\Common\Collections\ArrayCollection,
    Doctrine\Common\Collections\Collection;

/**
 * The ObjectHydrator constructs an object graph out of an SQL result set.
 *
 * @author Roman Borschel <roman@code-factory.org>
 * @since 2.0
 * @internal Highly performance-sensitive code.
 */
class ObjectHydrator extends AbstractHydrator
{
    /* Local ClassMetadata cache to avoid going to the EntityManager all the time.
     * This local cache is maintained between hydration runs and not cleared.
     */
    private $_ce = array();
    
    /* The following parts are reinitialized on every hydration run. */
    
    private $_identifierMap;
    private $_resultPointers;
    private $_idTemplate;
    private $_resultCounter;
    private $_rootAliases = array();
    private $_initializedCollections = array();
    private $_existingCollections = array();
    //private $_createdEntities;
    

    /** @override */
    protected function _prepare()
    {
        $this->_identifierMap =
        $this->_resultPointers =
        $this->_idTemplate = array();
        $this->_resultCounter = 0;
        
        foreach ($this->_rsm->aliasMap as $dqlAlias => $className) {
            $this->_identifierMap[$dqlAlias] = array();
            $this->_idTemplate[$dqlAlias] = '';
            $class = $this->_em->getClassMetadata($className);

            if ( ! isset($this->_ce[$className])) {
                $this->_ce[$className] = $class;
            }
            
            // Remember which associations are "fetch joined", so that we know where to inject
            // collection stubs or proxies and where not.
            if (isset($this->_rsm->relationMap[$dqlAlias])) {
                $sourceClassName = $this->_rsm->aliasMap[$this->_rsm->parentAliasMap[$dqlAlias]];
                $sourceClass = $this->_getClassMetadata($sourceClassName);
                $assoc = $sourceClass->associationMappings[$this->_rsm->relationMap[$dqlAlias]];
                $this->_hints['fetched'][$sourceClassName][$assoc['fieldName']] = true;
                if ($sourceClass->subClasses) {
                    foreach ($sourceClass->subClasses as $sourceSubclassName) {
                        $this->_hints['fetched'][$sourceSubclassName][$assoc['fieldName']] = true;
                    }
                }
                if ($assoc['type'] != ClassMetadata::MANY_TO_MANY) {
                    // Mark any non-collection opposite sides as fetched, too.
                    if ($assoc['mappedBy']) {
                        $this->_hints['fetched'][$className][$assoc['mappedBy']] = true;
                    } else {
                        if ($assoc['inversedBy']) {
                            $inverseAssoc = $class->associationMappings[$assoc['inversedBy']];
                            if ($inverseAssoc['type'] & ClassMetadata::TO_ONE) {
                                $this->_hints['fetched'][$className][$inverseAssoc['fieldName']] = true;
                                if ($class->subClasses) {
                                    foreach ($class->subClasses as $targetSubclassName) {
                                        $this->_hints['fetched'][$targetSubclassName][$inverseAssoc['fieldName']] = true;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function _cleanup()
    {
        parent::_cleanup();
        $this->_identifierMap =
        $this->_initializedCollections =
        $this->_existingCollections =
        $this->_resultPointers = array();
    }

    /**
     * {@inheritdoc}
     */
    protected function _hydrateAll()
    {
        $result = array();
        $cache = array();

        while ($row = $this->_stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->_hydrateRow($row, $cache, $result);
        }

        // Take snapshots from all newly initialized collections
        foreach ($this->_initializedCollections as $coll) {
            $coll->takeSnapshot();
        }

        return $result;
    }

    /**
     * Initializes a related collection.
     *
     * @param object $entity The entity to which the collection belongs.
     * @param string $name The name of the field on the entity that holds the collection.
     */
    private function _initRelatedCollection($entity, $class, $fieldName)
    {
        $oid = spl_object_hash($entity);
        $relation = $class->associationMappings[$fieldName];
        
        $value = $class->reflFields[$fieldName]->getValue($entity);
        if ($value === null) {
            $value = new ArrayCollection;
        }
        
        if ( ! $value instanceof PersistentCollection) {
            $value = new PersistentCollection(
                $this->_em,
                $this->_ce[$relation['targetEntity']],
                $value
            );
            $value->setOwner($entity, $relation);
            $class->reflFields[$fieldName]->setValue($entity, $value);
            $this->_uow->setOriginalEntityProperty($oid, $fieldName, $value);
            $this->_initializedCollections[$oid . $fieldName] = $value;
        } else if (isset($this->_hints[Query::HINT_REFRESH])) {
            // Is already PersistentCollection, but REFRESH
            $value->setDirty(false);
            $value->setInitialized(true);
            $value->unwrap()->clear();
            $this->_initializedCollections[$oid . $fieldName] = $value;
        } else {
            // Is already PersistentCollection, and DONT REFRESH
            $this->_existingCollections[$oid . $fieldName] = $value;
        }
        
        return $value;
    }
    
    /**
     * Gets an entity instance.
     * 
     * @param $data The instance data.
     * @param $dqlAlias The DQL alias of the entity's class.
     * @return object The entity.
     */
    private function _getEntity(array $data, $dqlAlias)
    {
    	$className = $this->_rsm->aliasMap[$dqlAlias];
        if (isset($this->_rsm->discriminatorColumns[$dqlAlias])) {
            $discrColumn = $this->_rsm->metaMappings[$this->_rsm->discriminatorColumns[$dqlAlias]];
            $className = $this->_ce[$className]->discriminatorMap[$data[$discrColumn]];
            unset($data[$discrColumn]);
        }
        return $this->_uow->createEntity($className, $data, $this->_hints);
    }
    
    private function _getEntityFromIdentityMap($className, array $data)
    {
        $class = $this->_ce[$className];
        if ($class->isIdentifierComposite) {
            $idHash = '';
            foreach ($class->identifier as $fieldName) {
                $idHash .= $data[$fieldName] . ' ';
            }
            return $this->_uow->tryGetByIdHash(rtrim($idHash), $class->rootEntityName);
        } else {
            return $this->_uow->tryGetByIdHash($data[$class->identifier[0]], $class->rootEntityName);
        }
    }
    
    /**
     * Gets a ClassMetadata instance from the local cache.
     * If the instance is not yet in the local cache, it is loaded into the
     * local cache.
     * 
     * @param string $className The name of the class.
     * @return ClassMetadata
     */
    private function _getClassMetadata($className)
    {
        if ( ! isset($this->_ce[$className])) {
            $this->_ce[$className] = $this->_em->getClassMetadata($className);
        }
        return $this->_ce[$className];
    }

    /**
     * Hydrates a single row in an SQL result set.
     * 
     * @internal
     * First, the data of the row is split into chunks where each chunk contains data
     * that belongs to a particular component/class. Afterwards, all these chunks
     * are processed, one after the other. For each chunk of class data only one of the
     * following code paths is executed:
     * 
     * Path A: The data chunk belongs to a joined/associated object and the association
     *         is collection-valued.
     * Path B: The data chunk belongs to a joined/associated object and the association
     *         is single-valued.
     * Path C: The data chunk belongs to a root result element/object that appears in the topmost
     *         level of the hydrated result. A typical example are the objects of the type
     *         specified by the FROM clause in a DQL query. 
     * 
     * @param array $data The data of the row to process.
     * @param array $cache The cache to use.
     * @param array $result The result array to fill.
     */
    protected function _hydrateRow(array $data, array &$cache, array &$result)
    {
        // Initialize
        $id = $this->_idTemplate; // initialize the id-memory
        $nonemptyComponents = array();
        // Split the row data into chunks of class data.
        $rowData = $this->_gatherRowData($data, $cache, $id, $nonemptyComponents);

        // Extract scalar values. They're appended at the end.
        if (isset($rowData['scalars'])) {
            $scalars = $rowData['scalars'];
            unset($rowData['scalars']);
            if (empty($rowData)) {
                ++$this->_resultCounter;
            }
        }

        // Hydrate the data chunks
        foreach ($rowData as $dqlAlias => $data) {
            $entityName = $this->_rsm->aliasMap[$dqlAlias];
            
            if (isset($this->_rsm->parentAliasMap[$dqlAlias])) {
                // It's a joined result

                $parentAlias = $this->_rsm->parentAliasMap[$dqlAlias];

                // Get a reference to the parent object to which the joined element belongs.
                if ($this->_rsm->isMixed && isset($this->_rootAliases[$parentAlias])) {
                	$first = reset($this->_resultPointers);
                    $parentObject = $this->_resultPointers[$parentAlias][key($first)];
                } else if (isset($this->_resultPointers[$parentAlias])) {
                    $parentObject = $this->_resultPointers[$parentAlias];
                } else {
                    // Parent object of relation not found, so skip it.
                    continue;
                }

                $parentClass = $this->_ce[$this->_rsm->aliasMap[$parentAlias]];
                $oid = spl_object_hash($parentObject);
                $relationField = $this->_rsm->relationMap[$dqlAlias];
                $relation = $parentClass->associationMappings[$relationField];
                $reflField = $parentClass->reflFields[$relationField];

                // Check the type of the relation (many or single-valued)
                if ( ! ($relation['type'] & ClassMetadata::TO_ONE)) {
                    // PATH A: Collection-valued association
                    if (isset($nonemptyComponents[$dqlAlias])) {
                        $collKey = $oid . $relationField;
                        if (isset($this->_initializedCollections[$collKey])) {
                            $reflFieldValue = $this->_initializedCollections[$collKey];
                        } else if ( ! isset($this->_existingCollections[$collKey])) {
                            $reflFieldValue = $this->_initRelatedCollection($parentObject, $parentClass, $relationField);
                        }
                        
                        $indexExists = isset($this->_identifierMap[$dqlAlias][$id[$dqlAlias]]);
                        $index = $indexExists ? $this->_identifierMap[$dqlAlias][$id[$dqlAlias]] : false;
                        $indexIsValid = $index !== false ? isset($reflFieldValue[$index]) : false;
                        
                        if ( ! $indexExists || ! $indexIsValid) {
                            if (isset($this->_existingCollections[$collKey])) {
                                // Collection exists, only look for the element in the identity map.
                                if ($element = $this->_getEntityFromIdentityMap($entityName, $data)) {
                                    $this->_resultPointers[$dqlAlias] = $element;
                                } else {
                                    unset($this->_resultPointers[$dqlAlias]);
                                }
                            } else {
                                $element = $this->_getEntity($data, $dqlAlias);

                                if (isset($this->_rsm->indexByMap[$dqlAlias])) {
                                    $field = $this->_rsm->indexByMap[$dqlAlias];
                                    $indexValue = $this->_ce[$entityName]->reflFields[$field]->getValue($element);
                                    $reflFieldValue->hydrateSet($indexValue, $element);
                                    $this->_identifierMap[$dqlAlias][$id[$dqlAlias]] = $indexValue;
                                } else {
                                    $reflFieldValue->hydrateAdd($element);
                                    $reflFieldValue->last();
                                    $this->_identifierMap[$dqlAlias][$id[$dqlAlias]] = $reflFieldValue->key();
                                }
                                // Update result pointer
                                $this->_resultPointers[$dqlAlias] = $element;
                            }
                        } else {
                            // Update result pointer
                            $this->_resultPointers[$dqlAlias] = $reflFieldValue[$index];
                        }
                    } else if ( ! $reflField->getValue($parentObject)) {
                        $coll = new PersistentCollection($this->_em, $this->_ce[$entityName], new ArrayCollection);
                        $reflField->setValue($parentObject, $coll);
                        $this->_uow->setOriginalEntityProperty($oid, $relationField, $coll);
                    }
                } else {
                    // PATH B: Single-valued association
                    $reflFieldValue = $reflField->getValue($parentObject);
                    if ( ! $reflFieldValue || isset($this->_hints[Query::HINT_REFRESH])) {
                        if (isset($nonemptyComponents[$dqlAlias])) {
                            $element = $this->_getEntity($data, $dqlAlias);
                            $reflField->setValue($parentObject, $element);
                            $this->_uow->setOriginalEntityProperty($oid, $relationField, $element);
                            $targetClass = $this->_ce[$relation['targetEntity']];
                            if ($relation['isOwningSide']) {
                                //TODO: Just check hints['fetched'] here?
                                // If there is an inverse mapping on the target class its bidirectional
                                if ($relation['inversedBy']) {
                                    $inverseAssoc = $targetClass->associationMappings[$relation['inversedBy']];
                                    if ($inverseAssoc['type'] & ClassMetadata::TO_ONE) {
                                        $targetClass->reflFields[$inverseAssoc['fieldName']]->setValue($element, $parentObject);
                                        $this->_uow->setOriginalEntityProperty(spl_object_hash($element), $inverseAssoc['fieldName'], $parentObject);
                                    }
                                } else if ($parentClass === $targetClass && $relation['mappedBy']) {
                                    // Special case: bi-directional self-referencing one-one on the same class
                                    $targetClass->reflFields[$relationField]->setValue($element, $parentObject);
                                }
                            } else {
                                // For sure bidirectional, as there is no inverse side in unidirectional mappings
                                $targetClass->reflFields[$relation['mappedBy']]->setValue($element, $parentObject);
                                $this->_uow->setOriginalEntityProperty(spl_object_hash($element), $relation['mappedBy'], $parentObject);
                            }
                            // Update result pointer
                            $this->_resultPointers[$dqlAlias] = $element;
                        }
                        // else leave $reflFieldValue null for single-valued associations
                    } else {
                        // Update result pointer
                        $this->_resultPointers[$dqlAlias] = $reflFieldValue;
                    }
                }
            } else {
                // PATH C: Its a root result element
                $this->_rootAliases[$dqlAlias] = true; // Mark as root alias

                if ( ! isset($this->_identifierMap[$dqlAlias][$id[$dqlAlias]])) {
                    $element = $this->_getEntity($rowData[$dqlAlias], $dqlAlias);
                    if (isset($this->_rsm->indexByMap[$dqlAlias])) {
                        $field = $this->_rsm->indexByMap[$dqlAlias];
                        $key = $this->_ce[$entityName]->reflFields[$field]->getValue($element);
                        if ($this->_rsm->isMixed) {
                            $element = array($key => $element);
                            $result[] = $element;
                            $this->_identifierMap[$dqlAlias][$id[$dqlAlias]] = $this->_resultCounter;
                            ++$this->_resultCounter;
                        } else {
                            $result[$key] = $element;
                            $this->_identifierMap[$dqlAlias][$id[$dqlAlias]] = $key;
                        }
                    } else {
                        if ($this->_rsm->isMixed) {
                            $element = array(0 => $element);
                        }
                        $result[] = $element;
                        $this->_identifierMap[$dqlAlias][$id[$dqlAlias]] = $this->_resultCounter;
                        ++$this->_resultCounter;
                    }

                    // Update result pointer
                    $this->_resultPointers[$dqlAlias] = $element;

                } else {
                    // Update result pointer
                    $index = $this->_identifierMap[$dqlAlias][$id[$dqlAlias]];
                    $this->_resultPointers[$dqlAlias] = $result[$index];
                    /*if ($this->_rsm->isMixed) {
                        $result[] = $result[$index];
                        ++$this->_resultCounter;
                    }*/
                }
            }
        }

        // Append scalar values to mixed result sets
        if (isset($scalars)) {
            foreach ($scalars as $name => $value) {
                $result[$this->_resultCounter - 1][$name] = $value;
            }
        }
    }
}

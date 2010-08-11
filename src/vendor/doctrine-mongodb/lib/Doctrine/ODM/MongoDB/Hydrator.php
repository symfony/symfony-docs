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

use Doctrine\ODM\MongoDB\Query,
    Doctrine\ODM\MongoDB\Mapping\ClassMetadata,
    Doctrine\ODM\MongoDB\Mapping\Types\Type,
    Doctrine\ODM\MongoDB\PersistentCollection,
    Doctrine\Common\Collections\ArrayCollection,
    Doctrine\Common\Collections\Collection;

/**
 * The Hydrator class is responsible for converting a document from MongoDB
 * which is an array to classes and collections based on the mapping of the document
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.com
 * @since       1.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
class Hydrator
{
    /**
     * The DocumentManager associationed with this Hydrator
     *
     * @var Doctrine\ODM\MongoDB\DocumentManager
     */
    private $dm;

    /**
     * Mongo command prefix
     * @var string
     */
    private $cmd;

    /**
     * Create a new Hydrator instance
     *
     * @param Doctrine\ODM\MongoDB\DocumentManager $dm
     */
    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
        $this->cmd = $dm->getConfiguration()->getMongoCmd();
    }

    /**
     * Hydrate array of MongoDB document data into the given document object.
     *
     * @param object $document  The document object to hydrate the data into.
     * @param array $data The array of document data.
     * @return array $values The array of hydrated values.
     */
    public function hydrate($document, &$data)
    {
        $metadata = $this->dm->getClassMetadata(get_class($document));
        foreach ($metadata->fieldMappings as $mapping) {
            if (isset($mapping['alsoLoadMethods'])) {
                foreach ($mapping['alsoLoadMethods'] as $method) {
                    if (isset($data[$mapping['fieldName']])) {
                        $document->$method($data[$mapping['fieldName']]);
                    }
                }
            }

            if (isset($mapping['alsoLoadFields'])) {
                $rawValue = null;
                $names = isset($mapping['alsoLoadFields']) ? $mapping['alsoLoadFields'] : array();
                array_unshift($names, $mapping['fieldName']);
                foreach ($names as $name) {
                    if (isset($data[$name])) {
                        $rawValue = $data[$name];
                        break;
                    }
                }
            } else {
                $rawValue = isset($data[$mapping['fieldName']]) ? $data[$mapping['fieldName']] : null;
            }
            if ($rawValue === null) {
                continue;
            }

            $value = null;

            // Hydrate embedded
            if (isset($mapping['embedded'])) {
                if ($mapping['type'] === 'one') {
                    $embeddedDocument = $rawValue;
                    $className = $this->getClassNameFromDiscriminatorValue($mapping, $embeddedDocument);
                    $embeddedMetadata = $this->dm->getClassMetadata($className);
                    $value = $embeddedMetadata->newInstance();
                    $this->hydrate($value, $embeddedDocument);
                    $this->dm->getUnitOfWork()->registerManagedEmbeddedDocument($value, $embeddedDocument);
                } elseif ($mapping['type'] === 'many') {
                    $embeddedDocuments = $rawValue;
                    $coll = new PersistentCollection(new ArrayCollection());
                    foreach ($embeddedDocuments as $embeddedDocument) {
                        $className = $this->getClassNameFromDiscriminatorValue($mapping, $embeddedDocument);
                        $embeddedMetadata = $this->dm->getClassMetadata($className);
                        $embeddedDocumentObject = $embeddedMetadata->newInstance();
                        $this->hydrate($embeddedDocumentObject, $embeddedDocument);
                        $this->dm->getUnitOfWork()->registerManagedEmbeddedDocument($embeddedDocumentObject, $embeddedDocument);
                        $coll->add($embeddedDocumentObject);
                    }
                    $coll->setOwner($document, $mapping);
                    $coll->takeSnapshot();
                    $value = $coll;
                }
            // Hydrate reference
            } elseif (isset($mapping['reference'])) {
                $reference = $rawValue;
                if ($mapping['type'] === 'one' && isset($reference[$this->cmd . 'id'])) {
                    $className = $this->getClassNameFromDiscriminatorValue($mapping, $reference);
                    $targetMetadata = $this->dm->getClassMetadata($className);
                    $id = $targetMetadata->getPHPIdentifierValue($reference[$this->cmd . 'id']);
                    $value = $this->dm->getReference($className, $id);
                } elseif ($mapping['type'] === 'many' && (is_array($reference) || $reference instanceof Collection)) {
                    $references = $reference;
                    $coll = new PersistentCollection(new ArrayCollection(), $this->dm);
                    $coll->setInitialized(false);
                    foreach ($references as $reference) {
                        $className = $this->getClassNameFromDiscriminatorValue($mapping, $reference);
                        $targetMetadata = $this->dm->getClassMetadata($className);
                        $id = $targetMetadata->getPHPIdentifierValue($reference[$this->cmd . 'id']);
                        $reference = $this->dm->getReference($className, $id);
                        $coll->add($reference);
                    }
                    $coll->takeSnapshot();
                    $value = $coll;
                }

            // Hydrate regular field
            } else {
                $value = Type::getType($mapping['type'])->convertToPHPValue($rawValue);
            }

            // Set hydrated field value to document
            if ($value !== null) {
                $data[$mapping['name']] = $value;
                $metadata->setFieldValue($document, $mapping['fieldName'], $value);
            }
        }
        // Set the document identifier
        if (isset($data['_id'])) {
            $metadata->setIdentifierValue($document, $data['_id']);
            $data[$metadata->identifier] = $data['_id'];
            unset($data['_id']);
        }
        return $document;
    }

    private function getClassNameFromDiscriminatorValue(array $mapping, $value)
    {
        $discriminatorField = isset($mapping['discriminatorField']) ? $mapping['discriminatorField'] : '_doctrine_class_name';
        if (isset($value[$discriminatorField])) {
            $discriminatorValue = $value[$discriminatorField];
            return isset($mapping['discriminatorMap'][$discriminatorValue]) ? $mapping['discriminatorMap'][$discriminatorValue] : $discriminatorValue;
        } else {
            return $mapping['targetDocument'];
        }
    }
}
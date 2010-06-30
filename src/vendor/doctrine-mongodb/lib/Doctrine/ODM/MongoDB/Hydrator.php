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
    Doctrine\ODM\MongoDB\PersistentCollection,
    Doctrine\ODM\MongoDB\Mapping\Types\Type,
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
    private $_dm;

    /**
     * Mongo command prefix
     * @var string
     */
    private $_cmd;

    /**
     * Create a new Hydrator instance
     *
     * @param Doctrine\ODM\MongoDB\DocumentManager $dm
     */
    public function __construct(DocumentManager $dm)
    {
        $this->_dm = $dm;
        $this->_cmd = $dm->getConfiguration()->getMongoCmd();
    }

    /**
     * Hydrate array of MongoDB document data into the given document object
     * based on the mapping information provided in the ClassMetadata instance.
     *
     * @param ClassMetadata $metadata  The ClassMetadata instance for mapping information.
     * @param string $document  The document object to hydrate the data into.
     * @param array $data The array of document data.
     * @return array $values The array of hydrated values.
     */
    public function hydrate(ClassMetadata $metadata, $document, $data)
    {
        $values = array();
        foreach ($metadata->fieldMappings as $mapping) {
            $rawValue = $this->_getFieldValue($mapping, $document, $data);
            if ( ! isset($rawValue)) {
                continue;
            }
            
            if (isset($mapping['embedded'])) {
                $embeddedMetadata = $this->_dm->getClassMetadata($mapping['targetDocument']);
                $embeddedDocument = $embeddedMetadata->newInstance();
                if ($mapping['type'] === 'many') {
                    $documents = new ArrayCollection();
                    foreach ($rawValue as $docArray) {
                        $doc = clone $embeddedDocument;
                        $this->hydrate($embeddedMetadata, $doc, $docArray);
                        $documents->add($doc);
                    }
                    $metadata->setFieldValue($document, $mapping['fieldName'], $documents);
                    $value = $documents;
                } else {
                    $value = clone $embeddedDocument;
                    $this->hydrate($embeddedMetadata, $value, $rawValue);
                    $metadata->setFieldValue($document, $mapping['fieldName'], $value);
                }
            } elseif (isset($mapping['reference'])) {
                $targetMetadata = $this->_dm->getClassMetadata($mapping['targetDocument']);
                $targetDocument = $targetMetadata->newInstance();
                if ($mapping['type'] === 'one' && isset($rawValue[$this->_cmd . 'id'])) {
                    $id = $targetMetadata->getPHPIdentifierValue($rawValue[$this->_cmd . 'id']);
                    $proxy = $this->_dm->getReference($mapping['targetDocument'], $id);
                    $metadata->setFieldValue($document, $mapping['fieldName'], $proxy);
                } elseif ($mapping['type'] === 'many' && (is_array($rawValue) || $rawValue instanceof Collection)) {
                    $documents = new PersistentCollection($this->_dm, $targetMetadata, new ArrayCollection());
                    $documents->setInitialized(false);
                    foreach ($rawValue as $v) {
                        $id = $targetMetadata->getPHPIdentifierValue($v[$this->_cmd . 'id']);
                        $proxy = $this->_dm->getReference($mapping['targetDocument'], $id);
                        $documents->add($proxy);
                    }
                    $metadata->setFieldValue($document, $mapping['fieldName'], $documents);
                }
            } else {
                $value = Type::getType($mapping['type'])->convertToPHPValue($rawValue);
                $metadata->setFieldValue($document, $mapping['fieldName'], $value);
            }
            if (isset($value)) {
                $values[$mapping['fieldName']] = $value;
            }
        }
        if (isset($data['_id'])) {
            $metadata->setIdentifierValue($document, $data['_id']);
        }
        return $values;
    }

    private function _getFieldValue(array $mapping, $document, $data)
    {
        $names = isset($mapping['alsoLoadFields']) ? $mapping['alsoLoadFields'] : array();
        array_unshift($names, $mapping['fieldName']);
        foreach ($names as $name) {
            if (isset($data[$name])) {
                return $data[$name];
            }
        }
        if (isset($mapping['alsoLoadMethods'])) {
            foreach ($mapping['alsoLoadMethods'] as $alsoLoad) {
                $names = $alsoLoad['name'];
                foreach ($names as $name) {
                    if (isset($data[$name])) {
                        $document->$alsoLoad['method']($data[$name]);
                    }
                }
            }
        }
        return null;
    }
}
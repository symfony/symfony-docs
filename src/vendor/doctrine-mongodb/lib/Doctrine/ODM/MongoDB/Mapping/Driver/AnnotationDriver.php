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

namespace Doctrine\ODM\MongoDB\Mapping\Driver;

use Doctrine\ODM\MongoDB\Mapping\ClassMetadata,
    Doctrine\Common\Annotations\AnnotationReader;

require __DIR__ . '/DoctrineAnnotations.php';

/**
 * The AnnotationDriver reads the mapping metadata from docblock annotations.
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       1.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Roman Borschel <roman@code-factory.org>
 */
class AnnotationDriver implements Driver
{
    /**
     * The AnnotationReader.
     *
     * @var AnnotationReader
     */
    private $reader;

    /**
     * The paths where to look for mapping files.
     *
     * @var array
     */
    private $paths = array();

    /**
     * Initializes a new AnnotationDriver that uses the given AnnotationReader for reading
     * docblock annotations.
     * 
     * @param $reader The AnnotationReader to use.
     * @param string|array $paths One or multiple paths where mapping classes can be found. 
     */
    public function __construct(AnnotationReader $reader, $paths = null)
    {
        $this->reader = $reader;
        if ($paths) {
            $this->addPaths((array) $paths);
        }
    }

    /**
     * Append lookup paths to metadata driver.
     *
     * @param array $paths
     */
    public function addPaths(array $paths)
    {
        $this->paths = array_unique(array_merge($this->paths, $paths));
    }

    /**
     * Retrieve the defined metadata lookup paths.
     *
     * @return array
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * {@inheritdoc}
     */
    public function loadMetadataForClass($className, ClassMetadata $class)
    {
        $reflClass = $class->getReflectionClass();

        $classAnnotations = $this->reader->getClassAnnotations($reflClass);
        if (isset($classAnnotations['Doctrine\ODM\MongoDB\Mapping\Document'])) {
            $documentAnnot = $classAnnotations['Doctrine\ODM\MongoDB\Mapping\Document'];
        } elseif (isset($classAnnotations['Doctrine\ODM\MongoDB\Mapping\MappedSuperclass'])) {
            $documentAnnot = $classAnnotations['Doctrine\ODM\MongoDB\Mapping\MappedSuperclass'];
            $class->isMappedSuperclass = true;
        } elseif (isset($classAnnotations['Doctrine\ODM\MongoDB\Mapping\EmbeddedDocument'])) {
            $documentAnnot = $classAnnotations['Doctrine\ODM\MongoDB\Mapping\EmbeddedDocument'];
            $class->isEmbeddedDocument = true;
        }

        if (isset($documentAnnot->db)) {
            $class->setDB($documentAnnot->db);
        }
        if (isset($documentAnnot->collection)) {
            $class->setCollection($documentAnnot->collection);
        }
        if (isset($documentAnnot->repositoryClass)) {
            $class->setCustomRepositoryClass($documentAnnot->repositoryClass);
        }
        if (isset($documentAnnot->indexes)) {
            foreach($documentAnnot->indexes as $index) {
                $class->addIndex($index->keys, $index->options);
            }
        }
        if (isset($classAnnotations['Doctrine\ODM\MongoDB\Mapping\InheritanceType'])) {
            $inheritanceTypeAnnot = $classAnnotations['Doctrine\ODM\MongoDB\Mapping\InheritanceType'];
            $class->setInheritanceType(constant('Doctrine\ODM\MongoDB\Mapping\ClassMetadata::INHERITANCE_TYPE_' . $inheritanceTypeAnnot->value));
        }
        if (isset($classAnnotations['Doctrine\ODM\MongoDB\Mapping\DiscriminatorField'])) {
            $discrFieldAnnot = $classAnnotations['Doctrine\ODM\MongoDB\Mapping\DiscriminatorField'];
            $class->setDiscriminatorField(array(
                'fieldName' => $discrFieldAnnot->fieldName,
            ));
        }
        if (isset($classAnnotations['Doctrine\ODM\MongoDB\Mapping\DiscriminatorMap'])) {
            $discrMapAnnot = $classAnnotations['Doctrine\ODM\MongoDB\Mapping\DiscriminatorMap'];
            $class->setDiscriminatorMap($discrMapAnnot->value);
        }
        if (isset($classAnnotations['Doctrine\ODM\MongoDB\Mapping\DiscriminatorValue'])) {
            $discrValueAnnot = $classAnnotations['Doctrine\ODM\MongoDB\Mapping\DiscriminatorValue'];
            $class->setDiscriminatorValue($discrValueAnnot->value);
        }
        if (isset($classAnnotations['Doctrine\ODM\MongoDB\Mapping\ChangeTrackingPolicy'])) {
            $changeTrackingAnnot = $classAnnotations['Doctrine\ODM\MongoDB\Mapping\ChangeTrackingPolicy'];
            $class->setChangeTrackingPolicy(constant('Doctrine\ODM\MongoDB\Mapping\ClassMetadata::CHANGETRACKING_' . $changeTrackingAnnot->value));
        }

        $methods = $reflClass->getMethods();

        foreach ($reflClass->getProperties() as $property) {
            $mapping = array();
            $mapping['fieldName'] = $property->getName();

            if ($alsoLoad = $this->reader->getPropertyAnnotation($property, 'Doctrine\ODM\MongoDB\Mapping\AlsoLoad')) {
                $mapping['alsoLoadFields'] = (array) $alsoLoad->value;
            }
            if ($notSaved = $this->reader->getPropertyAnnotation($property, 'Doctrine\ODM\MongoDB\Mapping\NotSaved')) {
                $mapping['notSaved'] = true;
            }

            foreach ($this->reader->getPropertyAnnotations($property) as $fieldAnnot) {
                if ($fieldAnnot instanceof \Doctrine\ODM\MongoDB\Mapping\Field) {
                    if ($fieldAnnot instanceof \Doctrine\ODM\MongoDB\Mapping\Id && $fieldAnnot->custom) {
                        $fieldAnnot->type = 'custom_id';
                        $class->setAllowCustomId(true);
                    }
                    $mapping = array_merge($mapping, (array) $fieldAnnot);
                    $class->mapField($mapping);
                }
            }
        }

        foreach ($methods as $method) {
            if ($method->isPublic()) {
                if ($alsoLoad = $this->reader->getMethodAnnotation($method, 'Doctrine\ODM\MongoDB\Mapping\AlsoLoad')) {
                    $fields = (array) $alsoLoad->value;
                    foreach ($fields as $value) {
                        $class->fieldMappings[$value]['alsoLoadMethods'][] = $method->getName();
                    }
                }
            }
        }
        if (isset($classAnnotations['Doctrine\ODM\MongoDB\Mapping\HasLifecycleCallbacks'])) {
            foreach ($methods as $method) {
                if ($method->isPublic()) {
                    $annotations = $this->reader->getMethodAnnotations($method);

                    if (isset($annotations['Doctrine\ODM\MongoDB\Mapping\PrePersist'])) {
                        $class->addLifecycleCallback($method->getName(), \Doctrine\ODM\MongoDB\ODMEvents::prePersist);
                    }

                    if (isset($annotations['Doctrine\ODM\MongoDB\Mapping\PostPersist'])) {
                        $class->addLifecycleCallback($method->getName(), \Doctrine\ODM\MongoDB\ODMEvents::postPersist);
                    }

                    if (isset($annotations['Doctrine\ODM\MongoDB\Mapping\PreUpdate'])) {
                        $class->addLifecycleCallback($method->getName(), \Doctrine\ODM\MongoDB\ODMEvents::preUpdate);
                    }

                    if (isset($annotations['Doctrine\ODM\MongoDB\Mapping\PostUpdate'])) {
                        $class->addLifecycleCallback($method->getName(), \Doctrine\ODM\MongoDB\ODMEvents::postUpdate);
                    }

                    if (isset($annotations['Doctrine\ODM\MongoDB\Mapping\PreRemove'])) {
                        $class->addLifecycleCallback($method->getName(), \Doctrine\ODM\MongoDB\ODMEvents::preRemove);
                    }

                    if (isset($annotations['Doctrine\ODM\MongoDB\Mapping\PostRemove'])) {
                        $class->addLifecycleCallback($method->getName(), \Doctrine\ODM\MongoDB\ODMEvents::postRemove);
                    }

                    if (isset($annotations['Doctrine\ODM\MongoDB\Mapping\PostLoad'])) {
                        $class->addLifecycleCallback($method->getName(), \Doctrine\ODM\MongoDB\ODMEvents::postLoad);
                    }
                }
            }
        }
    }


    /**
     * Factory method for the Annotation Driver
     * 
     * @param array|string $paths
     * @param AnnotationReader $reader
     * @return AnnotationDriver
     */
    static public function create($paths = array(), AnnotationReader $reader = null)
    {
        if ($reader == null) {
            $reader = new AnnotationReader();
            $reader->setDefaultAnnotationNamespace('Doctrine\ODM\MongoDB\Mapping\\');
        }
        return new self($reader, $paths);
    }
}
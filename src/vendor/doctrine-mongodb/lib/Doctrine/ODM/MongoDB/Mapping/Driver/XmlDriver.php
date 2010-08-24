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
    SimpleXmlElement;

/**
 * XmlDriver is a metadata driver that enables mapping through XML files.
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       1.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Roman Borschel <roman@code-factory.org>
 */
class XmlDriver extends AbstractFileDriver
{
    /**
     * The file extension of mapping documents.
     *
     * @var string
     */
    protected $fileExtension = '.dcm.xml';

    /**
     * {@inheritdoc}
     */
    public function loadMetadataForClass($className, ClassMetadata $class)
    {
        $xmlRoot = $this->getElement($className);
        if ( ! $xmlRoot) {
            return;
        }
        if ($xmlRoot->getName() == 'document') {
            if (isset($xmlRoot['repository-class'])) {
                $class->setCustomRepositoryClass((string) $xmlRoot['repository-class']);
            }
        } elseif ($xmlRoot->getName() == 'mapped-superclass') {
            $class->isMappedSuperclass = true;
        } elseif ($xmlRoot->getName() == 'embedded-document') {
            $class->isEmbeddedDocument = true;
        }
        if (isset($xmlRoot['db'])) {
            $class->setDB((string) $xmlRoot['db']);
        }
        if (isset($xmlRoot['collection'])) {
            $class->setCollection((string) $xmlRoot['collection']);
        }
        if (isset($xmlRoot['customId']) && ((string) $xmlRoot['customId'] === true)) {
            $class->setAllowCustomId(true);
        }
        if (isset($xmlRoot['inheritance-type'])) {
            $inheritanceType = (string) $xmlRoot['inheritance-type'];
            $class->setInheritanceType(constant('Doctrine\ODM\MongoDB\Mapping\ClassMetadata::INHERITANCE_TYPE_' . $inheritanceType));
        }
        if (isset($xmlRoot->{'discriminator-field'})) {
            $discrField = $xmlRoot->{'discriminator-field'};
            $class->setDiscriminatorField(array(
                'name' => (string) $discrField['name'],
                'fieldName' => (string) $discrField['fieldName'],
            ));
        }
        if (isset($xmlRoot->{'discriminator-map'})) {
            $map = array();
            foreach ($xmlRoot->{'discriminator-map'}->{'discriminator-mapping'} AS $discrMapElement) {
                $map[(string) $discrMapElement['value']] = (string) $discrMapElement['class'];
            }
            $class->setDiscriminatorMap($map);
        }
        if (isset($xmlRoot->{'change-tracking-policy'})) {
            $class->setChangeTrackingPolicy(constant('Doctrine\ODM\MongoDB\Mapping\ClassMetadata::CHANGETRACKING_'
                    . strtoupper((string)$xmlRoot->{'change-tracking-policy'})));
        }
        if (isset($xmlRoot->{'indexes'})) {
            foreach($xmlRoot->{'indexes'}->{'index'} as $index) {
                $this->addIndex($class, $index);
            }
        }
        if (isset($xmlRoot->field)) {
            foreach ($xmlRoot->field as $field) {
                $mapping = array();
                $attributes = $field->attributes();
                foreach ($attributes as $key => $value) {
                    $mapping[$key] = (string) $value;
                    $booleanAttributes = array('id', 'reference', 'embed', 'unique');
                    if (in_array($key, $booleanAttributes)) {
                        $mapping[$key] = ('true' === $mapping[$key]) ? true : false;
                    }
                }
                $this->addFieldMapping($class, $mapping);
            }
        }
        if (isset($xmlRoot->{'embed-one'})) {
            foreach ($xmlRoot->{'embed-one'} as $embed) {
                $this->addEmbedMapping($class, $embed, 'one');
            }
        }
        if (isset($xmlRoot->{'embed-many'})) {
            foreach ($xmlRoot->{'embed-many'} as $embed) {
                $this->addEmbedMapping($class, $embed, 'many');
            }
        }
        if (isset($xmlRoot->{'reference-many'})) {
            foreach ($xmlRoot->{'reference-many'} as $reference) {
                $this->addReferenceMapping($class, $reference, 'many');
            }
        }
        if (isset($xmlRoot->{'reference-one'})) {
            foreach ($xmlRoot->{'reference-one'} as $reference) {
                $this->addReferenceMapping($class, $reference, 'one');
            }
        }
        if (isset($xmlRoot->{'lifecycle-callbacks'})) {
            foreach ($xmlRoot->{'lifecycle-callbacks'}->{'lifecycle-callback'} as $lifecycleCallback) {
                $class->addLifecycleCallback((string) $lifecycleCallback['method'], constant('Doctrine\ODM\MongoDB\ODMEvents::' . (string) $lifecycleCallback['type']));
            }
        }
    }

    private function addFieldMapping(ClassMetadata $class, $mapping)
    {
        $keys = null;
        $name = isset($mapping['name']) ? $mapping['name'] : $mapping['fieldName'];
        if (isset($mapping['index'])) {
            $keys = array(
                $name => isset($mapping['order']) ? $mapping['order'] : 'asc'
            );
        }
        if (isset($mapping['unique'])) {
            $keys = array(
                $name => isset($mapping['order']) ? $mapping['order'] : 'asc'
            );
        }
        if ($keys !== null) {
            $options = array();
            if (isset($mapping['index-name'])) {
                $options['name'] = (string) $mapping['index-name'];
            }
            if (isset($mapping['drop-dups'])) {
                $options['dropDups'] = (boolean) $mapping['drop-dups'];
            }
            if (isset($mapping['background'])) {
                $options['background'] = (boolean) $mapping['background'];
            }
            if (isset($mapping['safe'])) {
                $options['safe'] = (boolean) $mapping['safe'];
            }
            if (isset($mapping['unique'])) {
                $options['unique'] = (boolean) $mapping['unique'];
            }
            $class->addIndex($keys, $options);
        }
        $class->mapField($mapping);
    }

    private function addEmbedMapping(ClassMetadata $class, $embed, $type)
    {
        $cascade = array_keys((array) $embed->cascade);
        if (1 === count($cascade)) {
            $cascade = current($cascade) ?: next($cascade);
        }
        $attributes = $embed->attributes();
        $mapping = array(
            'cascade'        => $cascade,
            'type'           => $type,
            'embedded'       => true,
            'targetDocument' => isset($attributes['target-document']) ? (string) $attributes['target-document'] : null,
            'name'           => (string) $attributes['field'],
        );
        $this->addFieldMapping($class, $mapping);
    }

    private function addReferenceMapping(ClassMetadata $class, $reference, $type)
    {
        $cascade = array_keys((array) $reference->cascade);
        if (1 === count($cascade)) {
            $cascade = current($cascade) ?: next($cascade);
        }
        $attributes = $reference->attributes();
        $mapping = array(
            'cascade'        => $cascade,
            'type'           => $type,
            'reference'      => true,
            'targetDocument' => isset($attributes['target-document']) ? (string) $attributes['target-document'] : null,
            'name'           => (string) $attributes['field'],
        );
        $this->addFieldMapping($class, $mapping);
    }

    private function addIndex(ClassMetadata $class, SimpleXmlElement $xmlIndex)
    {
        $attributes = $xmlIndex->attributes();
        $options = array();
        if (isset($attributes['name'])) {
            $options['name'] = (string) $attributes['name'];
        }
        if (isset($attributes['drop-dups'])) {
            $options['dropDups'] = (boolean) $attributes['drop-dups'];
        }
        if (isset($attributes['background'])) {
            $options['background'] = (boolean) $attributes['background'];
        }
        if (isset($attributes['safe'])) {
            $options['safe'] = (boolean) $attributes['safe'];
        }
        if (isset($attributes['unique'])) {
            $options['unique'] = (boolean) $attributes['unique'];
        }
        $index = array(
            'keys' => array(),
            'options' => $options
        );
        foreach ($xmlIndex->{'key'} as $key) {
            $index['keys'][(string) $key['name']] = isset($key['order']) ? (string) $key['order'] : 'asc';
        }
        if (isset($xmlIndex->{'option'})) {
            foreach ($xmlIndex->{'option'} as $option) {
                $value = (string) $option['value'];
                $value = $value === 'true' ? true : $value;
                $value = $value === 'false' ? false : $value;
                $index['options'][(string) $option['name']] = $value;
            }
        }
        $class->addIndex($index['keys'], $index['options']);
    }

    protected function loadMappingFile($file)
    {
        $result = array();
        $xmlElement = simplexml_load_file($file);

        foreach (array('document', 'embedded-document', 'mapped-superclass') as $type) {
            if (isset($xmlElement->$type)) {
                foreach ($xmlElement->$type as $documentElement) {
                    $documentName = (string) $documentElement['name'];
                    $result[$documentName] = $documentElement;
                }
            }
        }

        return $result;
    }
}
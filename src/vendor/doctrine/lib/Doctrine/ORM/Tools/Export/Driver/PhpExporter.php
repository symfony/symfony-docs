<?php

/*
 *  $Id$
 *
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

namespace Doctrine\ORM\Tools\Export\Driver;

use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * ClassMetadata exporter for PHP code
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link    www.doctrine-project.org
 * @since   2.0
 * @version $Revision$
 * @author  Jonathan Wage <jonwage@gmail.com>
 */
class PhpExporter extends AbstractExporter
{
    protected $_extension = '.php';

    /**
     * Converts a single ClassMetadata instance to the exported format
     * and returns it
     *
     * @param ClassMetadataInfo $metadata 
     * @return mixed $exported
     */
    public function exportClassMetadata(ClassMetadataInfo $metadata)
    {
        $lines = array();
        $lines[] = '<?php';
        $lines[] = null;
        $lines[] = 'use Doctrine\ORM\Mapping\ClassMetadataInfo;';
        $lines[] = null;

        if ($metadata->isMappedSuperclass) {
            $lines[] = '$metadata->isMappedSuperclass = true;';
        }

        if ($metadata->inheritanceType) {
            $lines[] = '$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_' . $this->_getInheritanceTypeString($metadata->inheritanceType) . ');';
        }

        if ($metadata->customRepositoryClassName) {
            $lines[] = "\$metadata->customRepositoryClassName = '" . $metadata->customRepositoryClassName . "';";
        }

        if ($metadata->table) {
            $lines[] = '$metadata->setPrimaryTable(' . $this->_varExport($metadata->table) . ');';
        }

        if ($metadata->discriminatorColumn) {
            $lines[] = '$metadata->setDiscriminatorColumn(' . $this->_varExport($metadata->discriminatorColumn) . ');';
        }

        if ($metadata->discriminatorMap) {
            $lines[] = '$metadata->setDiscriminatorMap(' . $this->_varExport($metadata->discriminatorMap) . ');';
        }

        if ($metadata->changeTrackingPolicy) {
            $lines[] = '$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_' . $this->_getChangeTrackingPolicyString($metadata->changeTrackingPolicy) . ');';
        }

        if ($metadata->lifecycleCallbacks) {
            foreach ($metadata->lifecycleCallbacks as $event => $callbacks) {
                foreach ($callbacks as $callback) {
                    $lines[] = "\$metadata->addLifecycleCallback('$callback', '$event');";
                }
            }
        }

        foreach ($metadata->fieldMappings as $fieldMapping) {
            $lines[] = '$metadata->mapField(' . $this->_varExport($fieldMapping) . ');';
        }

        if ($generatorType = $this->_getIdGeneratorTypeString($metadata->generatorType)) {
            $lines[] = '$metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_' . $generatorType . ');';
        }

        foreach ($metadata->associationMappings as $associationMapping) {
            $cascade = array('remove', 'persist', 'refresh', 'merge', 'detach');
            foreach ($cascade as $key => $value) {
                if ( ! $associationMapping['isCascade'.ucfirst($value)]) {
                    unset($cascade[$key]);
                }
            }
            $associationMappingArray = array(
                'fieldName'    => $associationMapping['fieldName'],
                'targetEntity' => $associationMapping['targetEntity'],
                'cascade'     => $cascade,
            );
            
            if ($associationMapping['type'] & ClassMetadataInfo::TO_ONE) {
                $method = 'mapOneToOne';
                $oneToOneMappingArray = array(
                    'mappedBy'      => $associationMapping['mappedBy'],
                    'inversedBy'    => $associationMapping['inversedBy'],
                    'joinColumns'   => $associationMapping['joinColumns'],
                    'orphanRemoval' => $associationMapping['orphanRemoval'],
                );
                
                $associationMappingArray = array_merge($associationMappingArray, $oneToOneMappingArray);
            } else if ($associationMapping['type'] == ClassMetadataInfo::ONE_TO_MANY) {
                $method = 'mapOneToMany';
                $oneToManyMappingArray = array(
                    'mappedBy'      => $associationMapping['mappedBy'],
                    'orphanRemoval' => $associationMapping['orphanRemoval'],
                    'orderBy' => $associationMapping['orderBy']
                );
                
                $associationMappingArray = array_merge($associationMappingArray, $oneToManyMappingArray);
            } else if ($associationMapping['type'] == ClassMetadataInfo::MANY_TO_MANY) {
                $method = 'mapManyToMany';
                $manyToManyMappingArray = array(
                    'mappedBy'  => $associationMapping['mappedBy'],
                    'joinTable' => $associationMapping['joinTable'],
                    'orderBy' => $associationMapping['orderBy']
                );
                
                $associationMappingArray = array_merge($associationMappingArray, $manyToManyMappingArray);
            }
            
            $lines[] = '$metadata->' . $method . '(' . $this->_varExport($associationMappingArray) . ');';
        }

        return implode("\n", $lines);
    }

    protected function _varExport($var)
    {
        $export = var_export($var, true);
        $export = str_replace("\n", PHP_EOL . str_repeat(' ', 8), $export);
        $export = str_replace('  ', ' ', $export);
        $export = str_replace('array (', 'array(', $export);
        $export = str_replace('array( ', 'array(', $export);
        $export = str_replace(',)', ')', $export);
        $export = str_replace(', )', ')', $export);
        $export = str_replace('  ', ' ', $export);

        return $export;
    }
}
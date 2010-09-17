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

namespace Doctrine\ORM\Tools;

use Doctrine\ORM\ORMException,
    Doctrine\DBAL\Types\Type,
    Doctrine\ORM\EntityManager,
    Doctrine\ORM\Mapping\ClassMetadata,
    Doctrine\ORM\Internal\CommitOrderCalculator,
    Doctrine\ORM\Tools\Event\GenerateSchemaTableEventArgs,
    Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;

/**
 * The SchemaTool is a tool to create/drop/update database schemas based on
 * <tt>ClassMetadata</tt> class descriptors.
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link    www.doctrine-project.org
 * @since   2.0
 * @version $Revision$
 * @author  Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author  Jonathan Wage <jonwage@gmail.com>
 * @author  Roman Borschel <roman@code-factory.org>
 * @author  Benjamin Eberlei <kontakt@beberlei.de>
 */
class SchemaTool
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $_em;

    /**
     * @var \Doctrine\DBAL\Platforms\AbstractPlatform
     */
    private $_platform;

    /**
     * Initializes a new SchemaTool instance that uses the connection of the
     * provided EntityManager.
     *
     * @param Doctrine\ORM\EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->_em = $em;
        $this->_platform = $em->getConnection()->getDatabasePlatform();
    }

    /**
     * Creates the database schema for the given array of ClassMetadata instances.
     *
     * @param array $classes
     */
    public function createSchema(array $classes)
    {
        $createSchemaSql = $this->getCreateSchemaSql($classes);
        $conn = $this->_em->getConnection();

        foreach ($createSchemaSql as $sql) {
            $conn->executeQuery($sql);
        }
    }

    /**
     * Gets the list of DDL statements that are required to create the database schema for
     * the given list of ClassMetadata instances.
     *
     * @param array $classes
     * @return array $sql The SQL statements needed to create the schema for the classes.
     */
    public function getCreateSchemaSql(array $classes)
    {
        $schema = $this->getSchemaFromMetadata($classes);
        return $schema->toSql($this->_platform);
    }

    /**
     * From a given set of metadata classes this method creates a Schema instance.
     *
     * @param array $classes
     * @return Schema
     */
    public function getSchemaFromMetadata(array $classes)
    {
        $processedClasses = array(); // Reminder for processed classes, used for hierarchies

        $sm = $this->_em->getConnection()->getSchemaManager();
        $metadataSchemaConfig = $sm->createSchemaConfig();
        $metadataSchemaConfig->setExplicitForeignKeyIndexes(false);
        $schema = new \Doctrine\DBAL\Schema\Schema(array(), array(), $metadataSchemaConfig);

        $evm = $this->_em->getEventManager();

        foreach ($classes as $class) {
            if (isset($processedClasses[$class->name]) || $class->isMappedSuperclass) {
                continue;
            }

            $table = $schema->createTable($class->getQuotedTableName($this->_platform));

            // TODO: Remove
            /**if ($class->isIdGeneratorIdentity()) {
                $table->setIdGeneratorType(\Doctrine\DBAL\Schema\Table::ID_IDENTITY);
            } else if ($class->isIdGeneratorSequence()) {
                $table->setIdGeneratorType(\Doctrine\DBAL\Schema\Table::ID_SEQUENCE);
            }*/

            $columns = array(); // table columns

            if ($class->isInheritanceTypeSingleTable()) {
                $columns = $this->_gatherColumns($class, $table);
                $this->_gatherRelationsSql($class, $table, $schema);

                // Add the discriminator column
                $discrColumnDef = $this->_getDiscriminatorColumnDefinition($class, $table);

                // Aggregate all the information from all classes in the hierarchy
                foreach ($class->parentClasses as $parentClassName) {
                    // Parent class information is already contained in this class
                    $processedClasses[$parentClassName] = true;
                }

                foreach ($class->subClasses as $subClassName) {
                    $subClass = $this->_em->getClassMetadata($subClassName);
                    $this->_gatherColumns($subClass, $table);
                    $this->_gatherRelationsSql($subClass, $table, $schema);
                    $processedClasses[$subClassName] = true;
                }
            } else if ($class->isInheritanceTypeJoined()) {
                // Add all non-inherited fields as columns
                $pkColumns = array();
                foreach ($class->fieldMappings as $fieldName => $mapping) {
                    if ( ! isset($mapping['inherited'])) {
                        $columnName = $class->getQuotedColumnName($mapping['fieldName'], $this->_platform);
                        $this->_gatherColumn($class, $mapping, $table);

                        if ($class->isIdentifier($fieldName)) {
                            $pkColumns[] = $columnName;
                        }
                    }
                }

                $this->_gatherRelationsSql($class, $table, $schema);

                // Add the discriminator column only to the root table
                if ($class->name == $class->rootEntityName) {
                    $discrColumnDef = $this->_getDiscriminatorColumnDefinition($class, $table);
                } else {
                    // Add an ID FK column to child tables
                    /* @var Doctrine\ORM\Mapping\ClassMetadata $class */
                    $idMapping = $class->fieldMappings[$class->identifier[0]];
                    $this->_gatherColumn($class, $idMapping, $table);
                    $columnName = $class->getQuotedColumnName($class->identifier[0], $this->_platform);

                    $pkColumns[] = $columnName;
                    // TODO: REMOVE
                    /*if ($table->isIdGeneratorIdentity()) {
                       $table->setIdGeneratorType(\Doctrine\DBAL\Schema\Table::ID_NONE);
                    }*/

                    // Add a FK constraint on the ID column
                    $table->addUnnamedForeignKeyConstraint(
                        $this->_em->getClassMetadata($class->rootEntityName)->getQuotedTableName($this->_platform),
                        array($columnName), array($columnName), array('onDelete' => 'CASCADE')
                    );
                }

                $table->setPrimaryKey($pkColumns);

            } else if ($class->isInheritanceTypeTablePerClass()) {
                throw ORMException::notSupported();
            } else {
                $this->_gatherColumns($class, $table);
                $this->_gatherRelationsSql($class, $table, $schema);
            }

            if (isset($class->table['indexes'])) {
                foreach ($class->table['indexes'] AS $indexName => $indexData) {
                    $table->addIndex($indexData['columns'], $indexName);
                }
            }

            if (isset($class->table['uniqueConstraints'])) {
                foreach ($class->table['uniqueConstraints'] AS $indexName => $indexData) {
                    $table->addUniqueIndex($indexData['columns'], $indexName);
                }
            }

            $processedClasses[$class->name] = true;

            if ($class->isIdGeneratorSequence() && $class->name == $class->rootEntityName) {
                $seqDef = $class->sequenceGeneratorDefinition;

                if (!$schema->hasSequence($seqDef['sequenceName'])) {
                    $schema->createSequence(
                        $seqDef['sequenceName'],
                        $seqDef['allocationSize'],
                        $seqDef['initialValue']
                    );
                }
            }

            if ($evm->hasListeners(ToolEvents::postGenerateSchemaTable)) {
                $evm->dispatchEvent(ToolEvents::postGenerateSchemaTable, new GenerateSchemaTableEventArgs($class, $schema, $table));
            }
        }

        if ($evm->hasListeners(ToolEvents::postGenerateSchema)) {
            $evm->dispatchEvent(ToolEvents::postGenerateSchema, new GenerateSchemaEventArgs($this->_em, $schema));
        }

        return $schema;
    }

    /**
     * Gets a portable column definition as required by the DBAL for the discriminator
     * column of a class.
     *
     * @param ClassMetadata $class
     * @return array The portable column definition of the discriminator column as required by
     *              the DBAL.
     */
    private function _getDiscriminatorColumnDefinition($class, $table)
    {
        $discrColumn = $class->discriminatorColumn;

        if (!isset($discrColumn['type']) || (strtolower($discrColumn['type']) == 'string' && $discrColumn['length'] === null)) {
            $discrColumn['type'] = 'string';
            $discrColumn['length'] = 255;
        }

        $table->addColumn(
            $discrColumn['name'],
            $discrColumn['type'],
            array('length' => $discrColumn['length'], 'notnull' => true)
        );
    }

    /**
     * Gathers the column definitions as required by the DBAL of all field mappings
     * found in the given class.
     *
     * @param ClassMetadata $class
     * @param Table $table
     * @return array The list of portable column definitions as required by the DBAL.
     */
    private function _gatherColumns($class, $table)
    {
        $columns = array();
        $pkColumns = array();

        foreach ($class->fieldMappings as $fieldName => $mapping) {
            $column = $this->_gatherColumn($class, $mapping, $table);

            if ($class->isIdentifier($mapping['fieldName'])) {
                $pkColumns[] = $class->getQuotedColumnName($mapping['fieldName'], $this->_platform);
            }
        }
        // For now, this is a hack required for single table inheritence, since this method is called
        // twice by single table inheritence relations
        if(!$table->hasIndex('primary')) {
            $table->setPrimaryKey($pkColumns);
        }

        return $columns;
    }

    /**
     * Creates a column definition as required by the DBAL from an ORM field mapping definition.
     *
     * @param ClassMetadata $class The class that owns the field mapping.
     * @param array $mapping The field mapping.
     * @param Table $table
     * @return array The portable column definition as required by the DBAL.
     */
    private function _gatherColumn($class, array $mapping, $table)
    {
        $columnName = $class->getQuotedColumnName($mapping['fieldName'], $this->_platform);
        $columnType = $mapping['type'];

        $options = array();
        $options['length'] = isset($mapping['length']) ? $mapping['length'] : null;
        $options['notnull'] = isset($mapping['nullable']) ? ! $mapping['nullable'] : true;

        $options['platformOptions'] = array();
        $options['platformOptions']['version'] = $class->isVersioned && $class->versionField == $mapping['fieldName'] ? true : false;

        if(strtolower($columnType) == 'string' && $options['length'] === null) {
            $options['length'] = 255;
        }

        if (isset($mapping['precision'])) {
            $options['precision'] = $mapping['precision'];
        }

        if (isset($mapping['scale'])) {
            $options['scale'] = $mapping['scale'];
        }

        if (isset($mapping['default'])) {
            $options['default'] = $mapping['default'];
        }

        if (isset($mapping['columnDefinition'])) {
            $options['columnDefinition'] = $mapping['columnDefinition'];
        }

        if ($class->isIdGeneratorIdentity() && $class->getIdentifierFieldNames() == array($mapping['fieldName'])) {
            $options['autoincrement'] = true;
        }

        if ($table->hasColumn($columnName)) {
            // required in some inheritance scenarios
            $table->changeColumn($columnName, $options);
        } else {
            $table->addColumn($columnName, $columnType, $options);
        }

        $isUnique = isset($mapping['unique']) ? $mapping['unique'] : false;
        if ($isUnique) {
            $table->addUniqueIndex(array($columnName));
        }
    }

    /**
     * Gathers the SQL for properly setting up the relations of the given class.
     * This includes the SQL for foreign key constraints and join tables.
     *
     * @param ClassMetadata $class
     * @param \Doctrine\DBAL\Schema\Table $table
     * @param \Doctrine\DBAL\Schema\Schema $schema
     * @return void
     */
    private function _gatherRelationsSql($class, $table, $schema)
    {
        foreach ($class->associationMappings as $fieldName => $mapping) {
            if (isset($mapping['inherited'])) {
                continue;
            }

            $foreignClass = $this->_em->getClassMetadata($mapping['targetEntity']);

            if ($mapping['type'] & ClassMetadata::TO_ONE && $mapping['isOwningSide']) {
                $primaryKeyColumns = $uniqueConstraints = array(); // PK is unnecessary for this relation-type

                $this->_gatherRelationJoinColumns($mapping['joinColumns'], $table, $foreignClass, $mapping, $primaryKeyColumns, $uniqueConstraints);

                foreach($uniqueConstraints AS $indexName => $unique) {
                    $table->addUniqueIndex($unique['columns'], is_numeric($indexName) ? null : $indexName);
                }
            } else if ($mapping['type'] == ClassMetadata::ONE_TO_MANY && $mapping['isOwningSide']) {
                //... create join table, one-many through join table supported later
                throw ORMException::notSupported();
            } else if ($mapping['type'] == ClassMetadata::MANY_TO_MANY && $mapping['isOwningSide']) {
                // create join table
                $joinTable = $mapping['joinTable'];

                $theJoinTable = $schema->createTable($foreignClass->getQuotedJoinTableName($mapping, $this->_platform));

                $primaryKeyColumns = $uniqueConstraints = array();

                // Build first FK constraint (relation table => source table)
                $this->_gatherRelationJoinColumns($joinTable['joinColumns'], $theJoinTable, $class, $mapping, $primaryKeyColumns, $uniqueConstraints);

                // Build second FK constraint (relation table => target table)
                $this->_gatherRelationJoinColumns($joinTable['inverseJoinColumns'], $theJoinTable, $foreignClass, $mapping, $primaryKeyColumns, $uniqueConstraints);

                $theJoinTable->setPrimaryKey($primaryKeyColumns);

                foreach($uniqueConstraints AS $indexName => $unique) {
                    $theJoinTable->addUniqueIndex($unique['columns'], is_numeric($indexName) ? null : $indexName);
                }
            }
        }
    }

    /**
     * Gather columns and fk constraints that are required for one part of relationship.
     *
     * @param array $joinColumns
     * @param \Doctrine\DBAL\Schema\Table $theJoinTable
     * @param ClassMetadata $class
     * @param \Doctrine\ORM\Mapping\AssociationMapping $mapping
     * @param array $primaryKeyColumns
     * @param array $uniqueConstraints
     */
    private function _gatherRelationJoinColumns($joinColumns, $theJoinTable, $class, $mapping, &$primaryKeyColumns, &$uniqueConstraints)
    {
        $localColumns = array();
        $foreignColumns = array();
        $fkOptions = array();

        foreach ($joinColumns as $joinColumn) {
            $columnName = $joinColumn['name'];
            $referencedFieldName = $class->getFieldName($joinColumn['referencedColumnName']);

            if ( ! $class->hasField($referencedFieldName)) {
                throw new \Doctrine\ORM\ORMException(
                    "Column name `".$joinColumn['referencedColumnName']."` referenced for relation from ".
                    $mapping['sourceEntity'] . " towards ". $mapping['targetEntity'] . " does not exist."
                );
            }

            $primaryKeyColumns[] = $columnName;
            $localColumns[] = $columnName;
            $foreignColumns[] = $joinColumn['referencedColumnName'];

            if ( ! $theJoinTable->hasColumn($joinColumn['name'])) {
                // Only add the column to the table if it does not exist already.
                // It might exist already if the foreign key is mapped into a regular
                // property as well.

                $fieldMapping = $class->getFieldMapping($referencedFieldName);

                $columnDef = null;
                if (isset($joinColumn['columnDefinition'])) {
                    $columnDef = $joinColumn['columnDefinition'];
                } else if (isset($fieldMapping['columnDefinition'])) {
                    $columnDef = $fieldMapping['columnDefinition'];
                }
                $columnOptions = array('notnull' => false, 'columnDefinition' => $columnDef);
                if (isset($joinColumn['nullable'])) {
                    $columnOptions['notnull'] = !$joinColumn['nullable'];
                }

                $theJoinTable->addColumn(
                    $columnName, $class->getTypeOfColumn($joinColumn['referencedColumnName']), $columnOptions
                );
            }

            if (isset($joinColumn['unique']) && $joinColumn['unique'] == true) {
                $uniqueConstraints[] = array('columns' => array($columnName));
            }

            if (isset($joinColumn['onUpdate'])) {
                $fkOptions['onUpdate'] = $joinColumn['onUpdate'];
            }

            if (isset($joinColumn['onDelete'])) {
                $fkOptions['onDelete'] = $joinColumn['onDelete'];
            }
        }

        $theJoinTable->addUnnamedForeignKeyConstraint(
            $class->getQuotedTableName($this->_platform), $localColumns, $foreignColumns, $fkOptions
        );
    }

    /**
     * Drops the database schema for the given classes.
     *
     * In any way when an exception is thrown it is supressed since drop was
     * issued for all classes of the schema and some probably just don't exist.
     *
     * @param array $classes
     * @return void
     */
    public function dropSchema(array $classes)
    {
        $dropSchemaSql = $this->getDropSchemaSql($classes);
        $conn = $this->_em->getConnection();

        foreach ($dropSchemaSql as $sql) {
            $conn->executeQuery($sql);
        }
    }

    /**
     * Gets the SQL needed to drop the database schema for the given classes.
     *
     * @param array $classes
     * @return array
     */
    public function getDropSchemaSql(array $classes)
    {
        $sm = $this->_em->getConnection()->getSchemaManager();
        $schema = $sm->createSchema();

        $visitor = new \Doctrine\DBAL\Schema\Visitor\DropSchemaSqlCollector($this->_platform);
        /* @var $schema \Doctrine\DBAL\Schema\Schema */
        $schema->visit($visitor);
        return $visitor->getQueries();
    }

    /**
     * Drop all tables of the database connection.
     *
     * @return array
     */
    private function _getDropSchemaTablesDatabaseMode($classes)
    {
        $conn = $this->_em->getConnection();

        $sm = $conn->getSchemaManager();
        /* @var $sm \Doctrine\DBAL\Schema\AbstractSchemaManager */

        $allTables = $sm->listTables();

        $orderedTables = $this->_getDropSchemaTablesMetadataMode($classes);
        foreach($allTables AS $tableName) {
            if(!in_array($tableName, $orderedTables)) {
                $orderedTables[] = $tableName;
            }
        }

        return $orderedTables;
    }

    private function _getDropSchemaTablesMetadataMode(array $classes)
    {
        $orderedTables = array();

        $commitOrder = $this->_getCommitOrder($classes);
        $associationTables = $this->_getAssociationTables($commitOrder);

        // Drop association tables first
        foreach ($associationTables as $associationTable) {
            $orderedTables[] = $associationTable;
        }

        // Drop tables in reverse commit order
        for ($i = count($commitOrder) - 1; $i >= 0; --$i) {
            $class = $commitOrder[$i];

            if (($class->isInheritanceTypeSingleTable() && $class->name != $class->rootEntityName)
                || $class->isMappedSuperclass) {
                continue;
            }

            $orderedTables[] = $class->getTableName();
        }

        //TODO: Drop other schema elements, like sequences etc.

        return $orderedTables;
    }

    /**
     * Updates the database schema of the given classes by comparing the ClassMetadata
     * instances to the current database schema that is inspected.
     *
     * @param array $classes
     * @return void
     */
    public function updateSchema(array $classes, $saveMode=false)
    {
        $updateSchemaSql = $this->getUpdateSchemaSql($classes, $saveMode);
        $conn = $this->_em->getConnection();

        foreach ($updateSchemaSql as $sql) {
            $conn->executeQuery($sql);
        }
    }

    /**
     * Gets the sequence of SQL statements that need to be performed in order
     * to bring the given class mappings in-synch with the relational schema.
     *
     * @param array $classes The classes to consider.
     * @return array The sequence of SQL statements.
     */
    public function getUpdateSchemaSql(array $classes, $saveMode=false)
    {
        $sm = $this->_em->getConnection()->getSchemaManager();

        $fromSchema = $sm->createSchema();
        $toSchema = $this->getSchemaFromMetadata($classes);

        $comparator = new \Doctrine\DBAL\Schema\Comparator();
        $schemaDiff = $comparator->compare($fromSchema, $toSchema);

        if ($saveMode) {
            return $schemaDiff->toSaveSql($this->_platform);
        } else {
            return $schemaDiff->toSql($this->_platform);
        }
    }

    private function _getCommitOrder(array $classes)
    {
        $calc = new CommitOrderCalculator;

        // Calculate dependencies
        foreach ($classes as $class) {
            $calc->addClass($class);

            foreach ($class->associationMappings as $assoc) {
                if ($assoc->isOwningSide) {
                    $targetClass = $this->_em->getClassMetadata($assoc['targetEntity']);

                    if ( ! $calc->hasClass($targetClass->name)) {
                        $calc->addClass($targetClass);
                    }

                    // add dependency ($targetClass before $class)
                    $calc->addDependency($targetClass, $class);
                }
            }
        }

        return $calc->getCommitOrder();
    }

    private function _getAssociationTables(array $classes)
    {
        $associationTables = array();

        foreach ($classes as $class) {
            foreach ($class->associationMappings as $assoc) {
                if ($assoc->isOwningSide && $assoc['type'] == ClassMetadata::MANY_TO_MANY) {
                    $associationTables[] = $assoc->joinTable['name'];
                }
            }
        }

        return $associationTables;
    }
}

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

namespace Doctrine\ORM\Query;

/**
 * A ResultSetMapping describes how a result set of an SQL query maps to a Doctrine result.
 *
 * IMPORTANT NOTE:
 * The properties of this class are only public for fast internal READ access and to (drastically)
 * reduce the size of serialized instances for more effective caching due to better (un-)serialization
 * performance.
 * 
 * <b>Users should use the public methods.</b>
 *
 * @author Roman Borschel <roman@code-factory.org>
 * @since 2.0
 * @todo Think about whether the number of lookup maps can be reduced.
 */
class ResultSetMapping
{
    /**
     * Whether the result is mixed (contains scalar values together with field values).
     * 
     * @ignore
     * @var boolean
     */
    public $isMixed = false;
    /**
     * Maps alias names to class names.
     *
     * @ignore
     * @var array
     */
    public $aliasMap = array();
    /**
     * Maps alias names to related association field names.
     * 
     * @ignore
     * @var array
     */
    public $relationMap = array();
    /**
     * Maps alias names to parent alias names.
     * 
     * @ignore
     * @var array
     */
    public $parentAliasMap = array();
    /**
     * Maps column names in the result set to field names for each class.
     * 
     * @ignore
     * @var array
     */
    public $fieldMappings = array();
    /**
     * Maps column names in the result set to the alias/field name to use in the mapped result.
     * 
     * @ignore
     * @var array
     */
    public $scalarMappings = array();
    /**
     * Maps column names of meta columns (foreign keys, discriminator columns, ...) to field names.
     * 
     * @ignore
     * @var array
     */
    public $metaMappings = array();
    /**
     * Maps column names in the result set to the alias they belong to.
     * 
     * @ignore
     * @var array
     */
    public $columnOwnerMap = array();
    /**
     * List of columns in the result set that are used as discriminator columns.
     * 
     * @ignore
     * @var array
     */
    public $discriminatorColumns = array();
    /**
     * Maps alias names to field names that should be used for indexing.
     * 
     * @ignore
     * @var array
     */
    public $indexByMap = array();
    /**
     * Map from column names to class names that declare the field the column is mapped to.
     * 
     * @ignore
     * @var array
     */
    public $declaringClasses = array();

    /**
     * Adds an entity result to this ResultSetMapping.
     *
     * @param string $class The class name of the entity.
     * @param string $alias The alias for the class. The alias must be unique among all entity
     *                      results or joined entity results within this ResultSetMapping.
     * @todo Rename: addRootEntity
     */
    public function addEntityResult($class, $alias)
    {
        $this->aliasMap[$alias] = $class;
    }

    /**
     * Sets a discriminator column for an entity result or joined entity result.
     * The discriminator column will be used to determine the concrete class name to
     * instantiate.
     *
     * @param string $alias The alias of the entity result or joined entity result the discriminator
     *                      column should be used for.
     * @param string $discrColumn The name of the discriminator column in the SQL result set.
     * @todo Rename: addDiscriminatorColumn
     */
    public function setDiscriminatorColumn($alias, $discrColumn)
    {
        $this->discriminatorColumns[$alias] = $discrColumn;
        $this->columnOwnerMap[$discrColumn] = $alias;
    }

    /**
     * Sets a field to use for indexing an entity result or joined entity result.
     *
     * @param string $alias The alias of an entity result or joined entity result.
     * @param string $fieldName The name of the field to use for indexing.
     */
    public function addIndexBy($alias, $fieldName)
    {
        $this->indexByMap[$alias] = $fieldName;
    }

    /**
     * Checks whether an entity result or joined entity result with a given alias has
     * a field set for indexing.
     *
     * @param string $alias
     * @return boolean
     * @todo Rename: isIndexed($alias)
     */
    public function hasIndexBy($alias)
    {
        return isset($this->indexByMap[$alias]);
    }

    /**
     * Checks whether the column with the given name is mapped as a field result
     * as part of an entity result or joined entity result.
     *
     * @param string $columnName The name of the column in the SQL result set.
     * @return boolean
     * @todo Rename: isField
     */
    public function isFieldResult($columnName)
    {
        return isset($this->fieldMappings[$columnName]);
    }

    /**
     * Adds a field to the result that belongs to an entity or joined entity.
     *
     * @param string $alias The alias of the root entity or joined entity to which the field belongs.
     * @param string $columnName The name of the column in the SQL result set.
     * @param string $fieldName The name of the field on the declaring class.
     * @param string $declaringClass The name of the class that declares/owns the specified field.
     *                               When $alias refers to a superclass in a mapped hierarchy but
     *                               the field $fieldName is defined on a subclass, specify that here.
     *                               If not specified, the field is assumed to belong to the class
     *                               designated by $alias.
     * @todo Rename: addField
     */
    public function addFieldResult($alias, $columnName, $fieldName, $declaringClass = null)
    {
        // column name (in result set) => field name
        $this->fieldMappings[$columnName] = $fieldName;
        // column name => alias of owner
        $this->columnOwnerMap[$columnName] = $alias;
        // field name => class name of declaring class
        $this->declaringClasses[$columnName] = $declaringClass ?: $this->aliasMap[$alias];
        if ( ! $this->isMixed && $this->scalarMappings) {
            $this->isMixed = true;
        }
    }

    /**
     * Adds a joined entity result.
     *
     * @param string $class The class name of the joined entity.
     * @param string $alias The unique alias to use for the joined entity.
     * @param string $parentAlias The alias of the entity result that is the parent of this joined result.
     * @param object $relation The association field that connects the parent entity result with the joined entity result.
     * @todo Rename: addJoinedEntity
     */
    public function addJoinedEntityResult($class, $alias, $parentAlias, $relation)
    {
        $this->aliasMap[$alias] = $class;
        $this->parentAliasMap[$alias] = $parentAlias;
        $this->relationMap[$alias] = $relation;
    }
    
    /**
     * Adds a scalar result mapping.
     *
     * @param string $columnName The name of the column in the SQL result set.
     * @param string $alias The result alias with which the scalar result should be placed in the result structure.
     * @todo Rename: addScalar
     */
    public function addScalarResult($columnName, $alias)
    {
        $this->scalarMappings[$columnName] = $alias;
        if ( ! $this->isMixed && $this->fieldMappings) {
            $this->isMixed = true;
        }
    }

    /**
     * Checks whether a column with a given name is mapped as a scalar result.
     * 
     * @param string $columName The name of the column in the SQL result set.
     * @return boolean
     * @todo Rename: isScalar
     */
    public function isScalarResult($columnName)
    {
        return isset($this->scalarMappings[$columnName]);
    }

    /**
     * Gets the name of the class of an entity result or joined entity result,
     * identified by the given unique alias.
     *
     * @param string $alias
     * @return string
     */
    public function getClassName($alias)
    {
        return $this->aliasMap[$alias];
    }

    /**
     * Gets the field alias for a column that is mapped as a scalar value.
     *
     * @param string $columnName The name of the column in the SQL result set.
     * @return string
     */
    public function getScalarAlias($columnName)
    {
        return $this->scalarMappings[$columnName];
    }

    /**
     * Gets the name of the class that owns a field mapping for the specified column.
     *
     * @param string $columnName
     * @return string
     */
    public function getDeclaringClass($columnName)
    {
        return $this->declaringClasses[$columnName];
    }

    /**
     *
     * @param string $alias
     * @return AssociationMapping
     */
    public function getRelation($alias)
    {
        return $this->relationMap[$alias];
    }

    /**
     *
     * @param string $alias
     * @return boolean
     */
    public function isRelation($alias)
    {
        return isset($this->relationMap[$alias]);
    }

    /**
     * Gets the alias of the class that owns a field mapping for the specified column.
     *
     * @param string $columnName
     * @return string
     */
    public function getEntityAlias($columnName)
    {
        return $this->columnOwnerMap[$columnName];
    }

    /**
     * Gets the parent alias of the given alias.
     *
     * @param string $alias
     * @return string
     */
    public function getParentAlias($alias)
    {
        return $this->parentAliasMap[$alias];
    }

    /**
     * Checks whether the given alias has a parent alias.
     *
     * @param string $alias
     * @return boolean
     */
    public function hasParentAlias($alias)
    {
        return isset($this->parentAliasMap[$alias]);
    }

    /**
     * Gets the field name for a column name.
     *
     * @param string $columnName
     * @return string
     */
    public function getFieldName($columnName)
    {
        return $this->fieldMappings[$columnName];
    }

    /**
     *
     * @return array
     */
    public function getAliasMap()
    {
        return $this->aliasMap;
    }

    /**
     * Gets the number of different entities that appear in the mapped result.
     *
     * @return integer
     */
    public function getEntityResultCount()
    {
        return count($this->aliasMap);
    }

    /**
     * Checks whether this ResultSetMapping defines a mixed result.
     * Mixed results can only occur in object and array (graph) hydration. In such a
     * case a mixed result means that scalar values are mixed with objects/array in
     * the result.
     *
     * @return boolean
     */
    public function isMixedResult()
    {
        return $this->isMixed;
    }
    
    /**
     * Adds a meta column (foreign key or discriminator column) to the result set.
     * 
     * @param $alias
     * @param $columnName
     * @param $fieldName
     */
    public function addMetaResult($alias, $columnName, $fieldName)
    {
        $this->metaMappings[$columnName] = $fieldName;
        $this->columnOwnerMap[$columnName] = $alias;
    }
}


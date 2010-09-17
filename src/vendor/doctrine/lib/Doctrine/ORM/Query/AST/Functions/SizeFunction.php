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

namespace Doctrine\ORM\Query\AST\Functions;

use Doctrine\ORM\Query\Lexer;

/**
 * "SIZE" "(" CollectionValuedPathExpression ")"
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link    www.doctrine-project.org
 * @since   2.0
 * @author  Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author  Jonathan Wage <jonwage@gmail.com>
 * @author  Roman Borschel <roman@code-factory.org>
 * @author  Benjamin Eberlei <kontakt@beberlei.de>
 */
class SizeFunction extends FunctionNode
{
    public $collectionPathExpression;

    /**
     * @override
     * @todo If the collection being counted is already joined, the SQL can be simpler (more efficient).
     */
    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        $platform = $sqlWalker->getConnection()->getDatabasePlatform();
        $dqlAlias = $this->collectionPathExpression->identificationVariable;
        $assocField = $this->collectionPathExpression->field;
        
        $qComp = $sqlWalker->getQueryComponent($dqlAlias);
        $class = $qComp['metadata'];
        $assoc = $class->associationMappings[$assocField];
        $sql = 'SELECT COUNT(*) FROM ';

        if ($assoc['type'] == \Doctrine\ORM\Mapping\ClassMetadata::ONE_TO_MANY) {
            $targetClass = $sqlWalker->getEntityManager()->getClassMetadata($assoc['targetEntity']);
            $targetTableAlias = $sqlWalker->getSqlTableAlias($targetClass->table['name']);
            $sourceTableAlias = $sqlWalker->getSqlTableAlias($class->table['name'], $dqlAlias);

            $sql .= $targetClass->getQuotedTableName($platform) . ' ' . $targetTableAlias . ' WHERE ';

            $owningAssoc = $targetClass->associationMappings[$assoc['mappedBy']];

            $first = true;
            
            foreach ($owningAssoc['targetToSourceKeyColumns'] as $targetColumn => $sourceColumn) {
                if ($first) $first = false; else $sql .= ' AND ';

                $sql .= $targetTableAlias . '.' . $sourceColumn
                      . ' = '
                      . $sourceTableAlias . '.' . $class->getQuotedColumnName($class->fieldNames[$targetColumn], $platform);
            }
        } else { // many-to-many
            $targetClass = $sqlWalker->getEntityManager()->getClassMetadata($assoc['targetEntity']);

            $owningAssoc = $assoc['isOwningSide'] ? $assoc : $targetClass->associationMappings[$assoc['mappedBy']];
            $joinTable = $owningAssoc['joinTable'];

            // SQL table aliases
            $joinTableAlias = $sqlWalker->getSqlTableAlias($joinTable['name']);
            $sourceTableAlias = $sqlWalker->getSqlTableAlias($class->table['name'], $dqlAlias);

            // join to target table
            $sql .= $targetClass->getQuotedJoinTableName($owningAssoc, $platform) . ' ' . $joinTableAlias . ' WHERE ';

            $joinColumns = $assoc['isOwningSide']
                ? $joinTable['joinColumns']
                : $joinTable['inverseJoinColumns'];

            $first = true;

            foreach ($joinColumns as $joinColumn) {
                if ($first) $first = false; else $sql .= ' AND ';

                $sourceColumnName = $class->getQuotedColumnName(
                    $class->fieldNames[$joinColumn['referencedColumnName']], $platform
                );

                $sql .= $joinTableAlias . '.' . $joinColumn['name']
                      . ' = '
                      . $sourceTableAlias . '.' . $sourceColumnName;
            }
        }
        
        return '(' . $sql . ')';
    }

    /**
     * @override
     */
    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $lexer = $parser->getLexer();
        
        $parser->match(Lexer::T_SIZE);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        
        $this->collectionPathExpression = $parser->CollectionValuedPathExpression();
        
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}


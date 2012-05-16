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

namespace Doctrine\ORM\Query\Exec;

use Doctrine\DBAL\Connection,
    Doctrine\ORM\Query\AST\SelectStatement,
    Doctrine\ORM\Query\SqlWalker;

/**
 * Executor that executes the SQL statement for simple DQL SELECT statements.
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @author      Roman Borschel <roman@code-factory.org>
 * @version     $Revision$
 * @link        www.doctrine-project.org
 * @since       2.0
 */
class SingleSelectExecutor extends AbstractSqlExecutor
{
    public function __construct(SelectStatement $AST, SqlWalker $sqlWalker)
    {
        $this->_sqlStatements = $sqlWalker->walkSelectStatement($AST);
    }

    public function execute(Connection $conn, array $params, array $types)
    {
        return $conn->executeQuery($this->_sqlStatements, $params, $types);
    }
}

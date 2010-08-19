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

/**
 * An DocumentRepository serves as a repository for documents with generic as well as
 * business specific methods for retrieving documents.
 *
 * This class is designed for inheritance and users can subclass this class to
 * write their own repositories with business-specific methods to locate documents.
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.com
 * @since       1.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Roman Borschel <roman@code-factory.org>
 */
class DocumentRepository
{
    /**
     * @var string
     */
    protected $documentName;

    /**
     * @var DocumentManager
     */
    protected $dm;

    /**
     * @var Doctrine\ODM\MongoDB\Mapping\ClassMetadata
     */
    protected $class;

    /**
     * Initializes a new <tt>DocumentRepository</tt>.
     *
     * @param DocumentManager $dm The DocumentManager to use.
     * @param ClassMetadata $classMetadata The class descriptor.
     */
    public function __construct($dm, Mapping\ClassMetadata $class)
    {
        $this->documentName = $class->name;
        $this->dm = $dm;
        $this->class = $class;
    }

    /**
     * Create a new Query instance that is prepopulated for this document name
     *
     * @return Query $qb
     */
    public function createQuery()
    {
        return $this->dm->createQuery()
            ->from($this->documentName);
    }

    /**
     * Clears the repository, causing all managed documents to become detached.
     */
    public function clear()
    {
        $this->dm->clear($this->class->rootDocumentName);
    }

    /**
     * Find a single document by its identifier or multiple by a given criteria.
     *
     * @param mixed $query A single identifier or an array of criteria.
     * @param array $select The fields to select.
     * @return Doctrine\ODM\MongoDB\MongoCursor $cursor
     * @return object $document
     */
    public function find($query = array(), array $select = array())
    {
        if (is_scalar($query)) {
            if ($document = $this->dm->getUnitOfWork()->tryGetById($query, $this->documentName)) {
                return $document; // Hit!
            }

            return $this->dm->getUnitOfWork()->getDocumentPersister($this->documentName)->loadById($query);
        } else {
            return $this->dm->getUnitOfWork()->getDocumentPersister($this->documentName)->loadAll($query, $select);
        }
    }

    /**
     * Find a single document with the given query and select fields.
     *
     * @param string $documentName The document to find.
     * @param array $query The query criteria.
     * @param array $select The fields to select
     * @return object $document
     */
    public function findOne(array $query = array(), array $select = array())
    {
        return $this->dm->getUnitOfWork()->getDocumentPersister($this->documentName)->load($query, $select);
    }

    /**
     * Finds all documents in the repository.
     *
     * @param int $hydrationMode
     * @return array The documents.
     */
    public function findAll()
    {
        return $this->find();
    }

    /**
     * Finds documents by a set of criteria.
     *
     * @param array $criteria
     * @return array
     */
    public function findBy(array $criteria)
    {
        return $this->find($criteria);
    }

    /**
     * Finds a single document by a set of criteria.
     *
     * @param array $criteria
     * @return object
     */
    public function findOneBy(array $criteria)
    {
       return $this->findOne($criteria);
    }

    /**
     * Adds support for magic finders.
     *
     * @return array|object The found document/documents.
     * @throws BadMethodCallException  If the method called is an invalid find* method
     *                                 or no find* method at all and therefore an invalid
     *                                 method call.
     */
    public function __call($method, $arguments)
    {
        if (substr($method, 0, 6) == 'findBy') {
            $by = substr($method, 6, strlen($method));
            $method = 'findBy';
        } elseif (substr($method, 0, 9) == 'findOneBy') {
            $by = substr($method, 9, strlen($method));
            $method = 'findOneBy';
        } else {
            throw new \BadMethodCallException(
                "Undefined method '$method'. The method name must start with ".
                "either findBy or findOneBy!"
            );
        }

        if ( ! isset($arguments[0])) {
            throw MongoDBException::findByRequiresParameter($method.$by);
        }

        $fieldName = lcfirst(\Doctrine\Common\Util\Inflector::classify($by));

        if ($this->class->hasField($fieldName)) {
            return $this->$method(array($fieldName => $arguments[0]));
        } else {
            throw MongoDBException::invalidFindByCall($this->documentName, $fieldName, $method.$by);
        }
    }

    /**
     * @return string
     */
    public function getDocumentName()
    {
        return $this->documentName;
    }

    /**
     * @return DocumentManager
     */
    public function getDocumentManager()
    {
        return $this->dm;
    }

    /**
     * @return Mapping\ClassMetadata
     */
    public function getClassMetadata()
    {
        return $this->class;
    }
}

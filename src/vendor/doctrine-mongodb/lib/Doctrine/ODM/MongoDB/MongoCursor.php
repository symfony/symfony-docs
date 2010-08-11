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

use Doctrine\ODM\MongoDB\Mapping\ClassMetadata,
    Doctrine\ODM\MongoDB\Hydrator;

/**
 * Wrapper for the PHP MongoCursor class.
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       1.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
class MongoCursor implements \Iterator, \Countable
{
    /** The DocumentManager instance. */
    private $dm;

    /** The UnitOfWork instance. */
    private $uow;

    /** The ClassMetadata instance. */
    private $class;

    /** The PHP MongoCursor being wrapped */
    private $mongoCursor;

    /** Whether or not to try and hydrate the returned data */
    private $hydrate = true;

    /**
     * Create a new MongoCursor which wraps around a given PHP MongoCursor.
     *
     * @param DocumentManager $dm
     * @param Hydrator $hydrator
     * @param ClassMetadata $class
     * @param MongoCursor $mongoCursor
     */
    public function __construct(DocumentManager $dm, Hydrator $hydrator, ClassMetadata $class, \MongoCursor $mongoCursor)
    {
        $this->dm = $dm;
        $this->uow = $this->dm->getUnitOfWork();
        $this->hydrator = $hydrator;
        $this->class = $class;
        $this->mongoCursor = $mongoCursor;
    }

    /**
     * Returns the MongoCursor instance being wrapped.
     *
     * @return MongoCursor $mongoCursor The MongoCursor instance being wrapped.
     */
    public function getMongoCursor()
    {
        return $this->mongoCursor;
    }

    /**
     * Whether or not to try and hydrate the returned data
     *
     * @param boolean $bool
     */
    public function hydrate($bool = null)
    {
        if ($bool !== null)
        {
            $this->hydrate = $bool;
            return $this;
        } else {
            return $this->hydrate;
        }
    }

    /** @override */
    public function current()
    {
        if ($this->mongoCursor instanceof \MongoGridFSCursor) {
            $file = $this->mongoCursor->current();
            $current = $file->file;
            $current[$this->class->file] = $file;
        } else {
            $current = $this->mongoCursor->current();
        }
        if ($this->hydrate) {
            return $this->uow->getOrCreateDocument($this->class->name, $current);
        } else {
            return $current;
        }
    }

    /** @proxy */
    public function next()
    {
        return $this->mongoCursor->next();
    }

    /** @proxy */
    public function key()
    {
        return $this->mongoCursor->key();
    }

    /** @proxy */
    public function valid()
    {
        return $this->mongoCursor->valid();
    }

    /** @proxy */
    public function rewind()
    {
        return $this->mongoCursor->rewind();
    }

    /** @proxy */
    public function count()
    {
        return $this->mongoCursor->count();
    }

    /**
     * Returns an array by converting the iterator to an array.
     *
     * @return array $results
     */
    public function getResults()
    {
        return iterator_to_array($this);
    }

    /**
     * Get the first single result from the cursor.
     *
     * @return object $document  The single document.
     */
    public function getSingleResult()
    {
        if ($results = $this->getResults()) {
            return array_shift($results);
        }
        return null;
    }

    /** @proxy */
    public function __call($method, $arguments)
    {
        if (method_exists($this->mongoCursor, $method)) {
            $return = call_user_func_array(array($this->mongoCursor, $method), $arguments);
            if ($return === $this->mongoCursor) {
                return $this;
            }
            return $return;
        }
        throw new \BadMethodCallException(sprintf('Method %s does not exist on %s', $method, get_class($this)));
    }
}
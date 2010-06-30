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

use Doctrine\Common\Collections\Collection,
    Doctrine\ODM\MongoDB\Mapping\ClassMetadata,
    Doctrine\ODM\MongoDB\Proxy\Proxy,
    Closure;

/**
 * A PersistentCollection represents a collection of elements that have persistent state.
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @since       1.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Roman Borschel <roman@code-factory.org>
 */
final class PersistentCollection implements Collection
{
    /**
     * A snapshot of the collection at the moment it was fetched from the database.
     * This is used to create a diff of the collection at commit time.
     *
     * @var array
     */
    private $_snapshot = array();

    protected $_owner;

    protected $_mapping;

    /**
     * The DocumentManager that manages the persistence of the collection.
     *
     * @var Doctrine\ODM\MongoDB\DocumentManager
     */
    private $_dm;

    /**
     * The class descriptor of the collection's document type.
     */
    private $_typeClass;

    /**
     * Whether the collection is dirty and needs to be synchronized with the database
     * when the UnitOfWork that manages its persistent state commits.
     *
     * @var boolean
     */
    private $_isDirty = false;

    /**
     * Whether the collection has already been initialized.
     * 
     * @var boolean
     */
    private $_initialized = true;
    
    /**
     * The wrapped Collection instance.
     * 
     * @var Collection
     */
    private $_coll;

    /**
     * Mongo command prefix
     * @var string
     */
    private $_cmd;

    public function __construct(DocumentManager $dm, ClassMetadata $class, Collection $coll)
    {
        $this->_coll = $coll;
        $this->_dm = $dm;
        $this->_typeClass = $class;
        $this->_cmd = $dm->getConfiguration()->getMongoCmd();
    }

    /**
     * Initializes the collection by loading its contents from the database
     * if the collection is not yet initialized.
     */
    private function _initialize()
    {
        if ( ! $this->_initialized) {
            $collection = $this->_dm->getDocumentCollection($this->_typeClass->name);

            $ids = array();
            foreach ($this->_coll as $document) {
                $ids[] = $this->_typeClass->getIdentifierObject($document);
            }

            $data = $collection->find(array('_id' => array($this->_cmd . 'in' => $ids)));
            $hints = array(Query::HINT_REFRESH => Query::HINT_REFRESH);
            foreach ($data as $id => $document) {
                $document = $this->_dm->getUnitOfWork()->getOrCreateDocument($this->_typeClass->name, $document, $hints);
                if ($document instanceof Proxy) {
                    $document->__isInitialized__ = true;
                    unset($document->__dm);
                    unset($document->__identifier);
                }
            }

            $this->_initialized = true;
        }
    }

    /**
     * Marks this collection as changed/dirty.
     */
    private function _changed()
    {
        if ( ! $this->_isDirty) {
            $this->_isDirty = true;
        }
    }

    /**
     * Gets a boolean flag indicating whether this colleciton is dirty which means
     * its state needs to be synchronized with the database.
     *
     * @return boolean TRUE if the collection is dirty, FALSE otherwise.
     */
    public function isDirty()
    {
        return $this->_isDirty;
    }

    /**
     * Sets a boolean flag, indicating whether this collection is dirty.
     *
     * @param boolean $dirty Whether the collection should be marked dirty or not.
     */
    public function setDirty($dirty)
    {
        $this->_isDirty = $dirty;
    }

    /**
     * INTERNAL:
     * Sets the collection's owning entity together with the AssociationMapping that
     * describes the association between the owner and the elements of the collection.
     *
     * @param object $document
     * @param AssociationMapping $mapping
     */
    public function setOwner($document, array $mapping)
    {
        $this->_owner = $document;
        $this->_mapping = $mapping;
    }

    /**
     * INTERNAL:
     * Tells this collection to take a snapshot of its current state.
     */
    public function takeSnapshot()
    {
        $this->_snapshot = $this->_coll->toArray();
        $this->_isDirty = false;
    }

    /**
     * INTERNAL:
     * Returns the last snapshot of the elements in the collection.
     *
     * @return array The last snapshot of the elements.
     */
    public function getSnapshot()
    {
        return $this->_snapshot;
    }

    /**
     * INTERNAL:
     * getDeleteDiff
     *
     * @return array
     */
    public function getDeleteDiff()
    {
        return array_udiff_assoc($this->_snapshot, $this->_coll->toArray(),
                function($a, $b) {return $a === $b ? 0 : 1;});
    }

    /**
     * INTERNAL:
     * getInsertDiff
     *
     * @return array
     */
    public function getInsertDiff()
    {
        return array_udiff_assoc($this->_coll->toArray(), $this->_snapshot,
                function($a, $b) {return $a === $b ? 0 : 1;});
    }

    /**
     * INTERNAL:
     * Gets the collection owner.
     *
     * @return object
     */
    public function getOwner()
    {
        return $this->_owner;
    }

    public function getMapping()
    {
        return $this->_mapping;
    }

    public function getTypeClass()
    {
        return $this->_typeClass;
    }

    /**
     * Sets the initialized flag of the collection, forcing it into that state.
     * 
     * @param boolean $bool
     */
    public function setInitialized($bool)
    {
        $this->_initialized = $bool;
    }
    
    /**
     * Checks whether this collection has been initialized.
     *
     * @return boolean
     */
    public function isInitialized()
    {
        return $this->_initialized;
    }

    /** {@inheritdoc} */
    public function first()
    {
        $this->_initialize();
        return $this->_coll->first();
    }

    /** {@inheritdoc} */
    public function last()
    {
        $this->_initialize();
        return $this->_coll->last();
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        $this->_initialize();
        $removed = $this->_coll->remove($key);
        return $removed;
    }

    /**
     * {@inheritdoc}
     */
    public function removeElement($element)
    {
        $this->_initialize();
        $result = $this->_coll->removeElement($element);
        $this->_changed();
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function containsKey($key)
    {
        $this->_initialize();
        return $this->_coll->containsKey($key);
    }

    /**
     * {@inheritdoc}
     */
    public function contains($element)
    {
        $this->_initialize();
        return $this->_coll->contains($element);
    }

    /**
     * {@inheritdoc}
     */
    public function exists(Closure $p)
    {
        $this->_initialize();
        return $this->_coll->exists($p);
    }

    /**
     * {@inheritdoc}
     */
    public function indexOf($element)
    {
        $this->_initialize();
        return $this->_coll->indexOf($element);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        $this->_initialize();
        return $this->_coll->get($key);
    }

    /**
     * {@inheritdoc}
     */
    public function getKeys()
    {
        $this->_initialize();
        return $this->_coll->getKeys();
    }

    /**
     * {@inheritdoc}
     */
    public function getValues()
    {
        $this->_initialize();
        return $this->_coll->getValues();
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        $this->_initialize();
        return $this->_coll->count();
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        $this->_initialize();
        $this->_coll->set($key, $value);
        $this->_changed();
    }

    /**
     * {@inheritdoc}
     */
    public function add($value)
    {
        $this->_coll->add($value);
        $this->_changed();
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        $this->_initialize();
        return $this->_coll->isEmpty();
    }
    
    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        $this->_initialize();
        return $this->_coll->getIterator();
    }

    /**
     * {@inheritdoc}
     */
    public function map(Closure $func)
    {
        $this->_initialize();
        return $this->_coll->map($func);
    }

    /**
     * {@inheritdoc}
     */
    public function filter(Closure $p)
    {
        $this->_initialize();
        return $this->_coll->filter($p);
    }
    
    /**
     * {@inheritdoc}
     */
    public function forAll(Closure $p)
    {
        $this->_initialize();
        return $this->_coll->forAll($p);
    }

    /**
     * {@inheritdoc}
     */
    public function partition(Closure $p)
    {
        $this->_initialize();
        return $this->_coll->partition($p);
    }
    
    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $this->_initialize();
        return $this->_coll->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->_initialize();
        $result = $this->_coll->clear();
        if ($this->_mapping->isOwningSide) {
            $this->_changed();
            $this->_dm->getUnitOfWork()->scheduleCollectionDeletion($this);
        }
        
        return $result;
    }
    
    /**
     * Called by PHP when this collection is serialized. Ensures that only the
     * elements are properly serialized.
     *
     * @internal Tried to implement Serializable first but that did not work well
     *           with circular references. This solution seems simpler and works well.
     */
    public function __sleep()
    {
        return array('_coll');
    }
    
    /* ArrayAccess implementation */

    /**
     * @see containsKey()
     */
    public function offsetExists($offset)
    {
        return $this->containsKey($offset);
    }

    /**
     * @see get()
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @see add()
     * @see set()
     */
    public function offsetSet($offset, $value)
    {
        if ( ! isset($offset)) {
            return $this->add($value);
        }
        return $this->set($offset, $value);
    }

    /**
     * @see remove()
     */
    public function offsetUnset($offset)
    {
        return $this->remove($offset);
    }
    
    public function key()
    {
        return $this->_coll->key();
    }
    
    /**
     * Gets the element of the collection at the current iterator position.
     */
    public function current()
    {
        return $this->_coll->current();
    }
    
    /**
     * Moves the internal iterator position to the next element.
     */
    public function next()
    {
        return $this->_coll->next();
    }
    
    /**
     * Retrieves the wrapped Collection instance.
     */
    public function unwrap()
    {
        return $this->_coll;
    }
}
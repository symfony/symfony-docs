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
    Doctrine\ODM\MongoDB\Mapping\Types\Type,
    Doctrine\ODM\MongoDB\Event\CollectionEventArgs,
    Doctrine\ODM\MongoDB\Event\CollectionUpdateEventArgs;

/**
 * Wrapper for the PHP MongoCollection class.
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       1.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
class MongoCollection
{
    /** The PHP MongoCollection being wrapped. */
    private $_mongoCollection;

    /** The ClassMetadata instance for this collection. */
    private $_class;

    /** A callable for logging statements. */
    private $_loggerCallable;

    /**
     * The event manager that is the central point of the event system.
     *
     * @var Doctrine\Common\EventManager
     */
    private $_eventManager;

    /**
     * Mongo command prefix
     * @var string
     */
    private $_cmd;

    /**
     * Create a new MongoCollection instance that wraps a PHP MongoCollection instance
     * for a given ClassMetadata instance.
     *
     * @param MongoCollection $mongoColleciton The MongoCollection instance.
     * @param ClassMetadata $class The ClassMetadata instance.
     * @param DocumentManager $dm The DocumentManager instance.
     */
    public function __construct(\MongoCollection $mongoCollection, ClassMetadata $class, DocumentManager $dm)
    {
        $this->_mongoCollection = $mongoCollection;
        $this->_class = $class;
        $this->_loggerCallable = $dm->getConfiguration()->getLoggerCallable();
        $this->_cmd = $dm->getConfiguration()->getMongoCmd();
        $this->_eventManager = $dm->getEventManager();
    }

    /**
     * Log something using the configured logger callable.
     *
     * @param array $log The array of data to log.
     */
    public function log(array $log)
    {
        if ( ! $this->_loggerCallable) {
            return;
        }
        $log['class'] = $this->_class->name;
        $log['db'] = $this->_class->db;
        $log['collection'] = $this->_class->collection;
        call_user_func_array($this->_loggerCallable, array($log));
    }

    /**
     * Returns teh ClassMetadata instance for this collection.
     *
     * @return Doctrine\ODM\MongoDB\MongoCollection
     */
    public function getMongoCollection()
    {
        return $this->_mongoCollection;
    }

    /** @override */
    public function batchInsert(array &$a, array $options = array())
    {
        if ($this->_eventManager->hasListeners(CollectionEvents::preBatchInsert)) {
            $this->_eventManager->dispatchEvent(CollectionEvents::preBatchInsert, new CollectionEventArgs($this, $a));
        }

        if ($this->_mongoCollection instanceof \MongoGridFS) {
            foreach ($a as $key => $array) {
                $this->saveFile($array);
                $a[$key] = $array;
            }
            return $a;
        }
        if ($this->_loggerCallable) {
            $this->log(array(
                'batchInsert' => true,
                'num' => count($a),
                'data' => $a
            ));
        }
        $result = $this->_mongoCollection->batchInsert($a, $options);

        if ($this->_eventManager->hasListeners(CollectionEvents::postBatchInsert)) {
            $this->_eventManager->dispatchEvent(CollectionEvents::postBatchInsert, new CollectionEventArgs($this, $result));
        }

        return $result;
    }

    /**
     * Save a file whether it exists or not already. Deletes previous file 
     * contents before trying to store new file contents.
     *
     * @param array $a Array to store
     * @return array $a
     */
    public function saveFile(array &$a)
    {
        if ($this->_eventManager->hasListeners(CollectionEvents::preSaveFile)) {
            $this->_eventManager->dispatchEvent(CollectionEvents::preSaveFile, new CollectionEventArgs($this, $a));
        }

        $fileName = $this->_class->fieldMappings[$this->_class->file]['fieldName'];
        $file = $a[$fileName];
        unset($a[$fileName]);
        if ($file instanceof \MongoGridFSFile) {
            $id = $a['_id'];
            unset($a['_id']);
            $set = array($this->_cmd . 'set' => $a);

            if ($this->_loggerCallable) {
                $this->log(array(
                    'updating' => true,
                    'file' => true,
                    'id' => $id,
                    'set' => $set
                ));
            }

            $this->_mongoCollection->update(array('_id' => $id), $set);
        } else {
            if (isset($a['_id'])) {
                $this->_mongoCollection->chunks->remove(array('files_id' => $a['_id']));
            }
            if (file_exists($file)) {
                if ($this->_loggerCallable) {
                    $this->log(array(
                        'storing' => true,
                        'file' => $file,
                        'document' => $a
                    ));
                }

                $id = $this->_mongoCollection->storeFile($file, $a);
            } elseif (is_string($file)) {
                if ($this->_loggerCallable) {
                    $this->log(array(
                        'storing' => true,
                        'bytes' => true,
                        'document' => $a
                    ));
                }

                $id = $this->_mongoCollection->storeBytes($file, $a);
            }

            $file = $this->_mongoCollection->findOne(array('_id' => $id));
        }

        if ($this->_eventManager->hasListeners(CollectionEvents::postSaveFile)) {
            $this->_eventManager->dispatchEvent(CollectionEvents::postSaveFile, new CollectionEventArgs($this, $file));
        }

        $a = $file->file;
        $a[$this->_class->file] = $file;
        return $a;
    }

    /** @override */
    public function getDBRef(array $reference)
    {
        if ($this->_eventManager->hasListeners(CollectionEvents::preGetDBRef)) {
            $this->_eventManager->dispatchEvent(CollectionEvents::preGetDBRef, new CollectionEventArgs($this, $reference));
        }

        if ($this->_loggerCallable) {
            $this->log(array(
                'get' => true,
                'reference' => $reference,
            ));
        }

        if ($this->_class->isFile()) {
            $ref = $this->_mongoCollection->getDBRef($reference);
            $file = $this->_mongoCollection->findOne(array('_id' => $ref['_id']));
            $data = $file->file;
            $data[$this->_class->file] = $file;
            return $data;
        }
        $dbRef = $this->_mongoCollection->getDBRef($reference);

        if ($this->_eventManager->hasListeners(CollectionEvents::postGetDBRef)) {
            $this->_eventManager->dispatchEvent(CollectionEvents::postGetDBRef, new CollectionEventArgs($this, $dbRef));
        }

        return $dbRef;
    }

    /** @override */
    public function save(array &$a, array $options = array())
    {
        if ($this->_eventManager->hasListeners(CollectionEvents::preSave)) {
            $this->_eventManager->dispatchEvent(CollectionEvents::preSave, new CollectionEventArgs($this, $a));
        }

        if ($this->_loggerCallable) {
            $this->log(array(
                'save' => true,
                'document' => $a,
                'options' => $options
            ));
        }
        if ($this->_class->isFile()) {
            $result = $this->saveFile($a);
        } else {
            $result = $this->_mongoCollection->save($a, $options);
        }

        if ($this->_eventManager->hasListeners(CollectionEvents::postSave)) {
            $this->_eventManager->dispatchEvent(CollectionEvents::postSave, new CollectionEventArgs($this, $result));
        }

        return $result;
    }

    /** @override */
    public function update(array $criteria, array $newObj, array $options = array())
    {
        if ($this->_eventManager->hasListeners(CollectionEvents::preUpdate)) {
            $this->_eventManager->dispatchEvent(CollectionEvents::preUpdate, new CollectionUpdateEventArgs($this, $criteria, $newObj, $options));
        }

        if ($this->_loggerCallable) {
            $this->log(array(
                'update' => true,
                'criteria' => $criteria,
                'newObj' => $newObj,
                'options' => $options
            ));
        }
        $result = $this->_mongoCollection->update($criteria, $newObj, $options);

        if ($this->_eventManager->hasListeners(CollectionEvents::postUpdate)) {
            $this->_eventManager->dispatchEvent(CollectionEvents::postUpdate, new CollectionEventArgs($this, $result));
        }

        return $result;
    }

    /** @override */
    public function find(array $query = array(), array $fields = array())
    {
        if ($this->_eventManager->hasListeners(CollectionEvents::preFind)) {
            $this->_eventManager->dispatchEvent(CollectionEvents::preFind, new CollectionEventArgs($this, $query));
        }

        if ($this->_loggerCallable) {
            $this->log(array(
                'find' => true,
                'query' => $query,
                'fields' => $fields
            ));
        }
        $result = $this->_mongoCollection->find($query, $fields);

        if ($this->_eventManager->hasListeners(CollectionEvents::postFind)) {
            $this->_eventManager->dispatchEvent(CollectionEvents::postFind, new CollectionEventArgs($this, $result));
        }

        return $result;
    }

    /** @override */
    public function findOne(array $query = array(), array $fields = array())
    {
        if ($this->_eventManager->hasListeners(CollectionEvents::preFindOne)) {
            $this->_eventManager->dispatchEvent(CollectionEvents::preFindOne, new CollectionEventArgs($this, $query));
        }

        if ($this->_loggerCallable) {
            $this->log(array(
                'findOne' => true,
                'query' => $query,
                'fields' => $fields
            ));
        }

        if ($this->_mongoCollection instanceof \MongoGridFS) {
            $file = $this->_mongoCollection->findOne($query);
            $data = $file->file;
            $data[$this->_class->file] = $file;
            return $data;
        }
        $result = $this->_mongoCollection->findOne($query, $fields);

        if ($this->_eventManager->hasListeners(CollectionEvents::postFindOne)) {
            $this->_eventManager->dispatchEvent(CollectionEvents::postFindOne, new CollectionEventArgs($this, $result));
        }

        return $result;
    }

    /** @proxy */
    public function __call($method, $arguments)
    {
        if (method_exists($this->_mongoCollection, $method)) {
            return call_user_func_array(array($this->_mongoCollection, $method), $arguments);
        }
        throw new \BadMethodCallException(sprintf('Method %s does not exist on %s', $method, get_class($this)));
    }
}
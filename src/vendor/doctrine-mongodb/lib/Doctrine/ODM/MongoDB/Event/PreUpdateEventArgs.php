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

namespace Doctrine\ODM\MongoDB\Event;

use Doctrine\ODM\MongoDB\DocumentManager;

/**
 * Class that holds event arguments for a preInsert/preUpdate event.
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.com
 * @since       1.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Roman Borschel <roman@code-factory.org>
 */
class PreUpdateEventArgs extends LifecycleEventArgs
{
    /**
     * @var array
     */
    private $documentChangeSet;

    /**
     *
     * @param object $document
     * @param DocumentManager $dm
     * @param array $changeSet
     */
    public function __construct($document, $dm, array &$changeSet)
    {
        parent::__construct($document, $dm);
        $this->documentChangeSet = &$changeSet;
    }

    public function getDocumentChangeSet()
    {
        return $this->documentChangeSet;
    }

    /**
     * Field has a changeset?
     *
     * @return bool
     */
    public function hasChangedField($field)
    {
        return isset($this->documentChangeSet[$field]);
    }

    /**
     * Get the old value of the changeset of the changed field.
     * 
     * @param  string $field
     * @return mixed
     */
    public function getOldValue($field)
    {
        $this->assertValidField($field);

        return $this->documentChangeSet[$field][0];
    }

    /**
     * Get the new value of the changeset of the changed field.
     *
     * @param  string $field
     * @return mixed
     */
    public function getNewValue($field)
    {
        $this->assertValidField($field);

        return $this->documentChangeSet[$field][1];
    }

    /**
     * Set the new value of this field.
     * 
     * @param string $field
     * @param mixed $value
     */
    public function setNewValue($field, $value)
    {
        $this->assertValidField($field);

        $this->documentChangeSet[$field][1] = $value;
    }

    private function assertValidField($field)
    {
        if ( ! isset($this->documentChangeSet[$field])) {
            throw new \InvalidArgumentException(
                "Field '".$field."' is not a valid field of the document ".
                "'".get_class($this->getDocument())."' in PreInsertUpdateEventArgs."
            );
        }
    }
}
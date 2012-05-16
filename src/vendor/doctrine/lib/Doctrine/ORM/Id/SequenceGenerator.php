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

namespace Doctrine\ORM\Id;

use Serializable, Doctrine\ORM\EntityManager;

/**
 * Represents an ID generator that uses a database sequence.
 *
 * @since 2.0
 * @author Roman Borschel <roman@code-factory.org>
 */
class SequenceGenerator extends AbstractIdGenerator implements Serializable
{
    private $_allocationSize;
    private $_sequenceName;
    private $_nextValue = 0;
    private $_maxValue = null;

    /**
     * Initializes a new sequence generator.
     *
     * @param Doctrine\ORM\EntityManager $em The EntityManager to use.
     * @param string $sequenceName The name of the sequence.
     * @param integer $allocationSize The allocation size of the sequence.
     */
    public function __construct($sequenceName, $allocationSize)
    {
        $this->_sequenceName = $sequenceName;
        $this->_allocationSize = $allocationSize;
    }
    
    /**
     * Generates an ID for the given entity.
     *
     * @param object $entity
     * @return integer|float The generated value.
     * @override
     */
    public function generate(EntityManager $em, $entity)
    {
        if ($this->_maxValue === null || $this->_nextValue == $this->_maxValue) {
            // Allocate new values
            $conn = $em->getConnection();
            $sql = $conn->getDatabasePlatform()->getSequenceNextValSQL($this->_sequenceName);
            $this->_nextValue = $conn->fetchColumn($sql);
            $this->_maxValue = $this->_nextValue + $this->_allocationSize;
        }
        return $this->_nextValue++;
    }

    /**
     * Gets the maximum value of the currently allocated bag of values.
     *
     * @return integer|float
     */
    public function getCurrentMaxValue()
    {
        return $this->_maxValue;
    }

    /**
     * Gets the next value that will be returned by generate().
     *
     * @return integer|float
     */
    public function getNextValue()
    {
        return $this->_nextValue;
    }

    public function serialize()
    {
        return serialize(array(
            'allocationSize' => $this->_allocationSize,
            'sequenceName' => $this->_sequenceName
        ));
    }

    public function unserialize($serialized)
    {
        $array = unserialize($serialized);
        $this->_sequenceName = $array['sequenceName'];
        $this->_allocationSize = $array['allocationSize'];
    }
}

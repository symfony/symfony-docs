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

namespace Doctrine\ORM\Internal;

/**
 * The CommitOrderCalculator is used by the UnitOfWork to sort out the
 * correct order in which changes to entities need to be persisted.
 *
 * @since 2.0
 * @author Roman Borschel <roman@code-factory.org> 
 */
class CommitOrderCalculator
{
    const NOT_VISITED = 1;
    const IN_PROGRESS = 2;
    const VISITED = 3;
    
    private $_nodeStates = array();
    private $_classes = array(); // The nodes to sort
    private $_relatedClasses = array();
    private $_sorted = array();
    
    /**
     * Clears the current graph.
     *
     * @return void
     */
    public function clear()
    {
        $this->_classes =
        $this->_relatedClasses = array();
    }
    
    /**
     * Gets a valid commit order for all current nodes.
     * 
     * Uses a depth-first search (DFS) to traverse the graph.
     * The desired topological sorting is the reverse postorder of these searches.
     *
     * @return array The list of ordered classes.
     */
    public function getCommitOrder()
    {
        // Check whether we need to do anything. 0 or 1 node is easy.
        $nodeCount = count($this->_classes);
        if ($nodeCount == 0) {
            return array();
        } else if ($nodeCount == 1) {
            return array_values($this->_classes);
        }
        
        // Init
        foreach ($this->_classes as $node) {
            $this->_nodeStates[$node->name] = self::NOT_VISITED;
        }
        
        // Go
        foreach ($this->_classes as $node) {
            if ($this->_nodeStates[$node->name] == self::NOT_VISITED) {
                $this->_visitNode($node);
            }
        }

        $sorted = array_reverse($this->_sorted);

        $this->_sorted = $this->_nodeStates = array();

        return $sorted;
    }

    private function _visitNode($node)
    {
        $this->_nodeStates[$node->name] = self::IN_PROGRESS;

        if (isset($this->_relatedClasses[$node->name])) {
            foreach ($this->_relatedClasses[$node->name] as $relatedNode) {
                if ($this->_nodeStates[$relatedNode->name] == self::NOT_VISITED) {
                    $this->_visitNode($relatedNode);
                }
            }
        }

        $this->_nodeStates[$node->name] = self::VISITED;
        $this->_sorted[] = $node;
    }
    
    public function addDependency($fromClass, $toClass)
    {
        $this->_relatedClasses[$fromClass->name][] = $toClass;
    }
    
    public function hasClass($className)
    {
        return isset($this->_classes[$className]);
    }
    
    public function addClass($class)
    {
        $this->_classes[$class->name] = $class;
    }
}
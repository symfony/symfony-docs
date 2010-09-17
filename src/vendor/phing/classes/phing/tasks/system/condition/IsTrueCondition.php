<?php
/*
 *  $Id: IsTrueCondition.php 43 2006-03-10 14:31:51Z mrook $
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
 * and is licensed under the LGPL. For more information please see
 * <http://phing.info>.
 */

require_once 'phing/ProjectComponent.php';
require_once 'phing/tasks/system/condition/Condition.php';

/**
 * Condition that tests whether a given string evals to true.
 *
 * @author Hans Lellelid <hans@xmpl.org> (Phing)
 * @author Steve Loughran (Ant)
 * @package phing.tasks.system.condition
 */
class IsTrueCondition extends ProjectComponent implements Condition {

    /**  
     * what we eval
     */ 
    private $value;

    /**
     * Set the value to be tested.
     * @param boolean $value
     */ 
    public function setValue($value) {
        $this->value = $value;
    }

    /**
     * return the inverted value;
     * @throws BuildException if someone forgot to spec a value
     */ 
    public function evaluate() {
        if ($this->value === null) {
            throw new BuildException("Nothing to test for falsehood");
        }
        return $this->value;
    }

}


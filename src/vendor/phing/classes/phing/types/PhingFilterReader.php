<?php
/*
 *  $Id: PhingFilterReader.php 325 2007-12-20 15:44:58Z hans $
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

include_once 'phing/types/DataType.php';
include_once 'phing/types/Parameter.php';

/*
 * A PhingFilterReader is a wrapper class that encloses the className
 * and configuration of a Configurable FilterReader.
 *
 * @author    Yannick Lecaillez <yl@seasonfive.com>
 * @version   $Revision: 1.9 $
 * @see       FilterReader
 * @package   phing.types
*/
class PhingFilterReader extends DataType {

    private $className;
    private $parameters = array();
    private $classPath;

    function setClassName($className) {
        $this->className = $className;
    }

    function getClassName() {
        return $this->className;
    }

    /**
     * Set the classpath to load the FilterReader through (attribute).
     * @param Path $classpath
     */
    function setClasspath(Path $classpath) {
        if ( $this->isReference() ) {
            throw $this->tooManyAttributes();
        }
        if ( $this->classPath === null ) {
            $this->classPath = $classpath;
        } else {
            $this->classPath->append($classpath);
        }
    }

    /*
     * Set the classpath to load the FilterReader through (nested element).
    */
    function createClasspath() {
        if ( $this->isReference() ) {
            throw $this->noChildrenAllowed();
        }        
        if ( $this->classPath === null ) {
            $this->classPath = new Path($this->project);
        }
        return $this->classPath->createPath();
    }

    function getClasspath() {
        return $this->classPath;
    }

    function setClasspathRef(Reference $r) {
        if ( $this->isReference() ) {
            throw $this->tooManyAttributes();
        }
        $o = $this->createClasspath();
        $o->setRefid($r);
    }
	
	function addParam(Parameter $param) {
		$this->parameters[] = $param;
	}

    function createParam() {
        $num = array_push($this->parameters, new Parameter());
        return $this->parameters[$num-1];
    }
		
    function getParams() {
        // We return a COPY
        $ret = array();
        for($i=0,$size=count($this->parameters); $i < $size; $i++) {
            $ret[] = clone $this->parameters[$i];
        }
        return $ret;
    }

    /*
     * Makes this instance in effect a reference to another PhingFilterReader 
     * instance.
     *
     * <p>You must not set another attribute or nest elements inside
     * this element if you make it a reference.</p>
     *
     * @param Reference $r the reference to which this instance is associated
     * @exception BuildException if this instance already has been configured.
    */
    function setRefid(Reference $r) {
        if ( (count($this->parameters) !== 0) || ($this->className !== null) ) {
            throw $this->tooManyAttributes();
        }
        $o = $r->getReferencedObject($this->getProject());
        if ( $o instanceof PhingFilterReader ) {
            $this->setClassName($o->getClassName());
            $this->setClasspath($o->getClassPath());
            foreach($o->getParams() as $p) {
                $this->addParam($p);
            }
        } else {
            $msg = $r->getRefId()." doesn\'t refer to a PhingFilterReader";
            throw new BuildException($msg);
        }

        parent::setRefid($r);
    }
}



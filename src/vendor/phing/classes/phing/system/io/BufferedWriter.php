<?php
/*
 *  $Id: BufferedWriter.php 227 2007-08-28 02:17:00Z hans $
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
 
include_once 'phing/system/io/Writer.php';

/**
 * Convenience class for writing files.
 *
 * @author    Hans Lellelid <hans@xmpl.org>
 * @version   $Revision: 1.10 $
 * @package   phing.system.io 
 */
class BufferedWriter extends Writer {
    
    /**
     * The size of the buffer in kb.
     */
    private $bufferSize    = 0;
    
    /**
     * @var Writer The Writer we are buffering output to.
     */
    private $out;

    public function __construct(Writer $writer, $buffsize = 8192) {
        $this->out = $writer;
        $this->bufferSize = $buffsize;
    }

    public function write($buf, $off = null, $len = null) {
        return $this->out->write($buf, $off, $len);
    }
    
    public function newLine() {
        $this->write(PHP_EOL);
    }
    
    public function getResource() {
        return $this->out->getResource();
    }
    
    public function flush() {
    	$this->out->flush();
    }
	
    /**
     * Close attached stream.
     */
    public function close() {
        return $this->out->close();
    }
    
}

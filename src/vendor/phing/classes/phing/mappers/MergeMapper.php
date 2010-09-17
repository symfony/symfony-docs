<?php
/* 
 *  $Id: MergeMapper.php 123 2006-09-14 20:19:08Z mrook $
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

include_once 'phing/mappers/FileNameMapper.php';

/**
 * For merging files into a single file.  In practice just returns whatever value
 * was set for "to".
 *
 * @author    Andreas Aderhold <andi@binarycloud.com>
 * @version   $Revision: 1.8 $
 * @package   phing.mappers
 */
class MergeMapper implements FileNameMapper {
    
    /** the merge */
    private $mergedFile;

    /**
     * The mapper implementation. Basically does nothing in this case.
     *
     * @param    mixed     The data the mapper works on
     * @returns  mixed     The data after the mapper has been applied
     * @access   public
     * @author   Andreas Aderhold, andi@binarycloud.com
     */
    function main($sourceFileName) {
        if ($this->mergedFile === null) {            
            throw new BuildException("MergeMapper error, to attribute not set");            
        }         
        return array($this->mergedFile);
    }

    /**
     * Accessor. Sets the to property
     *
     * @param    string     To what this mapper should convert the from string
     * @returns  boolean    True
     * @access   public
     * @author   Andreas Aderhold, andi@binarycloud.com
     */
    function setTo($to) {
        $this->mergedFile = $to;
    }    

    /**
     * Ignored.
     */
    function setFrom($from) {}

}

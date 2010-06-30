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

/**
 * Class for all exceptions related to the Doctrine MongoDB ODM
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       1.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
class MongoDBException extends \Exception
{
    public static function invalidFindByCall($documentName, $fieldName, $method)
    {
        return new self(sprintf('Invalid find by call %s::$fieldName (%s)', $documentName, $fieldName, $method));
    }

    public static function removedDocumentInCollectionDetected($document, $mapping)
    {
        return new self(sprintf('Removed document in collection detected "%s"', get_class($document), $mapping['fieldName']));
    }

    public static function detachedDocumentCannotBeRemoved()
    {
        return new self('Detached document cannot be removed');
    }

    public static function invalidDocumentState($state)
    {
        return new self(sprintf('Invalid document state "%s"', $state));
    }

    public static function mappingFileNotFound($className, $fileName)
    {
        return new self(sprintf('Could not find mapping file "%s" for class "%s".', $fileName, $className));
    }

    public static function documentNotMappedToDB($className)
    {
        return new self(sprintf('The "%s" document is not mapped to a MongoDB database.', $className));
    }

    public static function documentNotMappedToCollection($className)
    {
        return new self(sprintf('The "%s" document is not mapped to a MongoDB database collection.', $className));
    }

    public static function documentNotFound($className, $identifier)
    {
        return new self(sprintf('The "%s" document with identifier "%s" could not be found.', $className, $identifier));
    }

    public static function documentManagerClosed()
    {
        return new self('The DocumentManager is closed.');
    }

    public static function typeExists($name)
    {
        return new self('Type '.$name.' already exists.');
    }

    public static function unknownFieldType($name)
    {
        return new self('Unknown field type '.$name.' requested.');
    }

    public static function typeNotFound($name)
    {
        return new self('Type to be overwritten '.$name.' does not exist.');
    }

    public static function unknownDocumentNamespace($documentNamespaceAlias)
    {
        return new self("Unknown Document namespace alias '$documentNamespaceAlias'.");
    }
}
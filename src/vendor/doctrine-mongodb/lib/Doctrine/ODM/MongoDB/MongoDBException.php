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

    public static function findByRequiresParameter($methodName)
    {
        return new self("You need to pass a parameter to '".$methodName."'");
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

    public static function cannotPersistEmbeddedDocumentOrMappedSuperclass($className)
    {
        return new self('Cannot persist an embedded document or mapped superclass ' . $className);
    }

    public static function mappingNotFound($fieldName)
    {
        return new self("No mapping found for field '$fieldName'.");
    }

    public static function duplicateFieldMapping($document, $fieldName)
    {
        return new self('Property "'.$fieldName.'" in "'.$document.'" was already declared, but it must be declared only once');
    }

    /**
     * Throws an exception that indicates that a class used in a discriminator map does not exist.
     * An example would be an outdated (maybe renamed) classname.
     *
     * @param string $className The class that could not be found
     * @param string $owningClass The class that declares the discriminator map.
     * @return self
     */
    public static function invalidClassInDiscriminatorMap($className, $owningClass)
    {
        return new self(
            "Document class '$className' used in the discriminator map of class '$owningClass' ".
            "does not exist."
        );
    }

    public static function missingFieldName($className)
    {
        return new self("The Document class '$className' field mapping misses the 'fieldName' attribute.");
    }

    public static function classIsNotAValidDocument($className)
    {
        return new self('Class '.$className.' is not a valid document or mapped super class.');
    }

    public static function pathRequired()
    {
        return new self("Specifying the paths to your documents is required ".
            "in the AnnotationDriver to retrieve all class names.");
    }

    public static function fileMappingDriversRequireConfiguredDirectoryPath()
    {
        return new self('File mapping drivers must have a valid directory path, however the given path seems to be incorrect!');
    }

    /**
     * Exception for reflection exceptions - adds the document name,
     * because there might be long classnames that will be shortened
     * within the stacktrace
     *
     * @param string $document The document's name
     * @param \ReflectionException $previousException
     */
    public static function reflectionFailure($document, \ReflectionException $previousException)
    {
        return new self('An error occurred in ' . $document, 0, $previousException);
    }


    public static function identifierRequired($documentName)
    {
        return new self("No identifier/primary key specified for Document '$documentName'."
                . " Every Document must have an identifier/primary key.");
    }

}
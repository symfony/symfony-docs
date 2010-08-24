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

namespace Doctrine\ODM\MongoDB\Mapping\Types;

use Doctrine\ODM\MongoDB\MongoDBException;

/**
 * The Type interface.
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       1.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Roman Borschel <roman@code-factory.org>
 */
abstract class Type
{
    /**
     * Array of string types mapped to their type class.
     */
    private static $typesMap = array(
        'id' => 'Doctrine\ODM\MongoDB\Mapping\Types\IdType',
        'custom_id' => 'Doctrine\ODM\MongoDB\Mapping\Types\CustomIdType',
        'boolean' => 'Doctrine\ODM\MongoDB\Mapping\Types\BooleanType',
        'int' => 'Doctrine\ODM\MongoDB\Mapping\Types\IntType',
        'float' => 'Doctrine\ODM\MongoDB\Mapping\Types\FloatType',
        'string' => 'Doctrine\ODM\MongoDB\Mapping\Types\StringType',
        'date' => 'Doctrine\ODM\MongoDB\Mapping\Types\DateType',
        'key' => 'Doctrine\ODM\MongoDB\Mapping\Types\KeyType',
        'timestamp' => 'Doctrine\ODM\MongoDB\Mapping\Types\TimestampType',
        'bin' => 'Doctrine\ODM\MongoDB\Mapping\Types\BinDataType',
        'bin_func' => 'Doctrine\ODM\MongoDB\Mapping\Types\BinDataFuncType',
        'bin_uuid' => 'Doctrine\ODM\MongoDB\Mapping\Types\BinDataUUIDType',
        'bin_md5' => 'Doctrine\ODM\MongoDB\Mapping\Types\BinDataMD5Type',
        'custom' => 'Doctrine\ODM\MongoDB\Mapping\Types\BinDataCustomType',
        'file' => 'Doctrine\ODM\MongoDB\Mapping\Types\FileType',
        'hash' => 'Doctrine\ODM\MongoDB\Mapping\Types\HashType',
        'collection' => 'Doctrine\ODM\MongoDB\Mapping\Types\CollectionType',
        'increment' => 'Doctrine\ODM\MongoDB\Mapping\Types\IncrementType'
    );

    
    /**
     * Converts a value from its PHP representation to its database representation
     * of this type.
     *
     * @param mixed $value The value to convert.
     * @return mixed The database representation of the value.
     */
    public function convertToDatabaseValue($value)
    {
        return $value;
    }

    /**
     * Converts a value from its database representation to its PHP representation
     * of this type.
     *
     * @param mixed $value The value to convert.
     * @return mixed The PHP representation of the value.
     */
    public function convertToPHPValue($value)
    {
        return $value;
    }

    /**
     * Array of instantiated type classes.
     */
    private static $types = array();

    /**
     * Register a new type in the type map.
     *
     * @param string $name The name of the type.
     * @param string $class The class name.
     */
    public static function registerType($name, $class)
    {
        self::$typesMap[$name] = $class;
    }

    /**
     * Get a Type instance.
     *
     * @param string $type The type name.
     * @return Doctrine\ODM\MongoDB\Mapping\Types\Type $type
     * @throws InvalidArgumentException
     */
    public static function getType($type)
    {
        if ( ! isset(self::$typesMap[$type])) {
            throw new \InvalidArgumentException(sprintf('Invalid type specified "%s".', $type));
        }
        if ( ! isset(self::$types[$type])) {
            $className = self::$typesMap[$type];
            self::$types[$type] = new $className;
        }
        return self::$types[$type];
    }

    /**
     * Adds a custom type to the type map.
     *
     * @static
     * @param string $name Name of the type. This should correspond to what getName() returns.
     * @param string $className The class name of the custom type.
     * @throws MongoDBException
     */
    public static function addType($name, $className)
    {
        if (isset(self::$typesMap[$name])) {
            throw MongoDBException::typeExists($name);
        }

        self::$typesMap[$name] = $className;
    }

    /**
     * Checks if exists support for a type.
     *
     * @static
     * @param string $name Name of the type
     * @return boolean TRUE if type is supported; FALSE otherwise
     */
    public static function hasType($name)
    {
        return isset(self::$typesMap[$name]);
    }

    /**
     * Overrides an already defined type to use a different implementation.
     *
     * @static
     * @param string $name
     * @param string $className
     * @throws MongoDBException
     */
    public static function overrideType($name, $className)
    {
        if ( ! isset(self::$typesMap[$name])) {
            throw MongoDBException::typeNotFound($name);
        }

        self::$typesMap[$name] = $className;
    }

    /**
     * Get the types array map which holds all registered types and the corresponding
     * type class
     *
     * @return array $typesMap
     */
    public static function getTypesMap()
    {
        return self::$typesMap;
    }
}
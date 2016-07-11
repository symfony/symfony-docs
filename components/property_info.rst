.. index::
    single: PropertyInfo
    single: Components; PropertyInfo

The PropertyInfo Component
==========================

    The PropertyInfo component provides the functionality to get information
    about class properties using metadata of popular sources.

While the :doc:`PropertyAccess component </components/property_access/introduction>`
allows you to read and write values to/from objects and arrays, the PropertyInfo
component works solely with class definitions to provide information such
as data type and visibility about properties within that class.

Similar to PropertyAccess, the PropertyInfo component combines both class
properties (such as ``$property``) and properties defined via accessor and
mutator methods such as  ``getProperty()``, ``isProperty()``, ``setProperty()``,
``addProperty()``, ``removeProperty()``, etc.

.. versionadded:: 2.8
    The PropertyInfo component was introduced in Symfony 2.8.

.. _`components-property-information-installation`:

Installation
------------

You can install the component in two different ways:

* :doc:`Install it via Composer </components/using_components>` (``symfony/property-info``
  on `Packagist`_);
* Use the official Git repository (https://github.com/symfony/property-info).

.. include:: /components/require_autoload.rst.inc

Additional dependencies may be required for some of the
:ref:`extractors provided with this component <components-property-info-extractors>`.

.. _`components-property-information-usage`:

Usage
-----

The entry point of this component is a new instance of the
:class:`Symfony\\Component\\PropertyInfo\\PropertyInfoExtractor`
class, providing sets of information extractors.

.. code-block:: php

    use Symfony\Component\PropertyInfo\PropertyInfoExtractor;

    $propertyInfo = new PropertyInfoExtractor(
        $arrayOfListExtractors,
        $arrayOfTypeExtractors,
        $arrayOfDescriptionExtractors,
        $arrayOfAccessExtractors
    );

The order of extractor instances within an array matters, as the first non-null
result will be returned. That is why you must provide each category of extractors
as a separate array, even if an extractor provides information for more than
one category.

For example, while the :class:`Symfony\\Component\\PropertyInfo\\Extractor\\ReflectionExtractor`
and :class:`Symfony\\Bridge\\Doctrine\\PropertyInfo\\DoctrineExtractor`
both provide list and type information it is probably better that:

* The :class:`Symfony\\Component\\PropertyInfo\\Extractor\\ReflectionExtractor`
  has priority for list information so that all properties in a class (not
  just mapped properties) are returned.
* The :class:`Symfony\\Bridge\\Doctrine\\PropertyInfo\\DoctrineExtractor`
  has priority for type information so that entity metadata is used instead
  of type-hinting to provide more accurate type information.

.. code-block:: php

    use Symfony\Bridge\Doctrine\PropertyInfo\DoctrineExtractor;
    use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
    use Symfony\Component\PropertyInfo\PropertyInfoExtractor;

    $reflectionExtractor = new ReflectionExtractor();
    $doctrineExtractor = new DoctrineExtractor(/* ... */);

    $propertyInfo = new PropertyInfoExtractor(
        // List extractors
        array(
            $reflectionExtractor,
            $doctrineExtractor
        ),
        // Type extractors
        array(
            $doctrineExtractor,
            $reflectionExtractor
        )
    );

.. _`components-property-information-extractable-information`:

Extractable Information
-----------------------

The :class:`Symfony\\Component\\PropertyInfo\\PropertyInfoExtractor`
class exposes public methods to extract four types of information: list,
type, description and access information. The first type of information is
about the class, while the remaining three are about the individual properties.

.. note::

    When specifiying a class that the PropertyInfo component should work
    with, use the fully-qualified class name. Do not directly pass an object
    as some extractors (the
    :class:`Symfony\\Component\\PropertyInfo\\Extractor\\SerializerExtractor`
    is an example) may not automatically resolve objects to their class
    names - use the ``get_class()`` function.

    Since the PropertyInfo component requires PHP 5.5 or greater, you can
    also make use of the `class constant`_.

List Information
~~~~~~~~~~~~~~~~

Extractors that implement :class:`Symfony\\Component\\PropertyInfo\\PropertyListExtractorInterface`
provide the list of properties that are available on a class as an array
containing each property name as a string.

.. code-block:: php

    $properties = $propertyInfo->getProperties($class);
    /*
      Example Result
      --------------
      array(3) {
        [0] => string(8) "username"
        [1] => string(8) "password"
        [2] => string(6) "active"
      }
    */

Type Information
~~~~~~~~~~~~~~~~

Extractors that implement :class:`Symfony\\Component\\PropertyInfo\\PropertyTypeExtractorInterface`
provide :ref:`extensive data type information <components-property-info-type>`
for a property.

.. code-block:: php

    $types = $propertyInfo->getTypes($class, $property);

    /*
      Example Result
      --------------
      array(1) {
        [0] =>
        class Symfony\Component\PropertyInfo\Type (6) {
          private $builtinType          => string(6) "string"
          private $nullable             => bool(false)
          private $class                => NULL
          private $collection           => bool(false)
          private $collectionKeyType    => NULL
          private $collectionValueType  => NULL
        }
      }
    */

Description Information
~~~~~~~~~~~~~~~~~~~~~~~

Extractors that implement :class:`Symfony\\Component\\PropertyInfo\\PropertyDescriptionExtractorInterface`
provide long and short descriptions from a properties annotations as
strings.

.. code-block:: php

    $title = $propertyInfo->getShortDescription($class, $property);
    /*
      Example Result
      --------------
      string(41) "This is the first line of the DocComment."
    */

    $paragraph = $propertyInfo->getLongDescription($class, $property);
    /*
      Example Result
      --------------
      string(79):
        These is the subsequent paragraph in the DocComment.
        It can span multiple lines.
    */

Access Information
~~~~~~~~~~~~~~~~~~

Extractors that implement :class:`Symfony\\Component\\PropertyInfo\\PropertyAccessExtractorInterface`
provide whether properties are readable or writable as booleans.

.. code-block:: php

    $propertyInfo->isReadable($class, $property);
    // Example Result: bool(true)

    $propertyInfo->isWritable($class, $property);
    // Example Result: bool(false)

.. tip::

    The main :class:`Symfony\\Component\\PropertyInfo\\PropertyInfoExtractor`
    class implements all four interfaces, delegating the extraction of property
    information to the extractors that have been registered with it.

    This means that any method available on each of the extractors is also
    available on the main :class:`Symfony\\Component\\PropertyInfo\\PropertyInfoExtractor`
    class.

.. _`components-property-info-type`:

Type Objects
------------

Compared to the other extractors, type information extractors provide much
more information than can be represented as simple scalar values - because
of this, type extractors return an array of objects. The array will contain
an instance of the :class:`Symfony\\Component\\PropertyInfo\\Type`
class for each type that the property supports.

For example, if a property supports both ``integer`` and ``string`` (via
the ``@return int|string`` annotation),
:method:`PropertyInfoExtractor::getTypes() <Symfony\\Component\\PropertyInfo\\PropertyInfoExtractor::getTypes>`
will return an array containing **two** instances of the :class:`Symfony\\Component\\PropertyInfo\\Type`
class.

.. note::

    Most extractors will return only one :class:`Symfony\\Component\\PropertyInfo\\Type`
    instance. The :class:`Symfony\\Component\\PropertyInfo\\Extractor\\PhpDocExtractor`
    is currently the only extractor that returns multiple instances in the array.

Each object will provide 6 attributes; the first four (built-in type, nullable,
class and collection) are scalar values and the last two (collection key
type and collection value type) are more instances of the :class:`Symfony\\Component\\PropertyInfo\\Type`
class again if collection is ``true``.

.. _`components-property-info-type-builtin`:

Built-in Type
~~~~~~~~~~~~~

The :method:`Type::getBuiltinType() <Symfony\\Component\\PropertyInfo\\Type::getBuiltinType>`
method will return the built-in PHP data type, which can be one of 9 possible
string values: ``array``, ``bool``, ``callable``, ``float``, ``int``, ``null``,
``object``, ``resource`` or ``string``.

Constants inside the :class:`Symfony\\Component\\PropertyInfo\\Type`
class, in the form ``Type::BUILTIN_TYPE_*``, are provided for convenience.

Nullable
~~~~~~~~

The :method:`Type::isNullable() <Symfony\\Component\\PropertyInfo\\Type::isNullable>`
method will return a boolean value indicating whether the property parameter
can be set to ``null``.

Class
~~~~~

If the :ref:`built-in PHP data type <components-property-info-type-builtin>`
is ``object``, the :method:`Type::getClassName() <Symfony\\Component\\PropertyInfo\\Type::getClassName>`
method will return the fully-qualified class or interface name accepted.

Collection
~~~~~~~~~~

The :method:`Type::isCollection() <Symfony\\Component\\PropertyInfo\\Type::isCollection>`
method will return a boolean value indicating if the property parameter is
a collection - a non-scalar value capable of containing other values. Currently
this returns ``true`` if:

* The :ref:`built-in PHP data type <components-property-info-type-builtin>`
  is ``array``, or
* The mutator method the property is derived from has a prefix of ``add``
  or ``remove`` (which are defined as the list of array mutator prefixes).

Collection Key & Value Types
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If the property is a collection, additional type objects may be returned
for both the key and value types of the collection (if the information is
available), via the :method:`Type::getCollectionKeyType() <Symfony\\Component\\PropertyInfo\\Type::getCollectionKeyType>`
and :method:`Type::getCollectionValueType() <Symfony\\Component\\PropertyInfo\\Type::getCollectionValueType>`
methods.

.. _`components-property-info-extractors`:

Extractors
----------

The extraction of property information is performed by *extractor classes*.
An extraction class can provide one or more types of property information
by implementing the correct interface(s).

The :class:`Symfony\\Component\\PropertyInfo\\PropertyInfoExtractor` will
iterate over the relevant extractor classes in the order they were set, call
the appropriate method and return the first result that is not ``null``.

.. _`components-property-information-extractors-available`:

While you can create your own extractors, the following are already available
to cover most use-cases:

ReflectionExtractor
~~~~~~~~~~~~~~~~~~~

Using PHP reflection, the :class:`Symfony\\Component\\PropertyInfo\\Extractor\\ReflectionExtractor`
provides list, type and access information from setter and accessor methods.
It can also provide return and scalar types for PHP 7+.

This service is automatically registered with the ``property_info`` service.

.. code-block:: php

    use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;

    $reflectionExtractor = new ReflectionExtractor;

    // List information.
    $reflectionExtractor->getProperties($class);
    // Type information.
    $reflectionExtractor->getTypes($class, $property);
    // Access information.
    $reflectionExtractor->isReadable($class, $property);
    $reflectionExtractor->isWritable($class, $property);

PhpDocExtractor
~~~~~~~~~~~~~~~

.. note::

    This extractor depends on the `phpdocumentor/reflection`_ library.

Using `phpDocumentor Reflection`_ to parse property and method annotations,
the :class:`Symfony\\Component\\PropertyInfo\\Extractor\\PhpDocExtractor`
provides type and description information. This extractor is automatically
registered with the ``property_info`` providing its dependencies are detected.

.. code-block:: php

    use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;

    $phpDocExtractor = new PhpDocExtractor;

    // Type information.
    $phpDocExtractor->getTypes($class, $property);
    // Description information.
    $phpDocExtractor->getShortDescription($class, $property);
    $phpDocExtractor->getLongDescription($class, $property);

SerializerExtractor
~~~~~~~~~~~~~~~~~~~

.. note::

    This extractor depends on the `symfony/serializer`_ library.

Using groups metadata from the :doc:`Serializer component </components/serializer>`,
the :class:`Symfony\\Component\\PropertyInfo\\Extractor\\SerializerExtractor`
provides list information. This extractor is not registered automatically
with the ``property_info`` service.

.. code-block:: php

    use Doctrine\Common\Annotations\AnnotationReader;
    use Symfony\Component\PropertyInfo\Extractor\SerializerExtractor;
    use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
    use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;

    $serializerClassMetadataFactory = new ClassMetadataFactory(
        new AnnotationLoader(new AnnotationReader)
    );
    $serializerExtractor = new SerializerExtractor($serializerClassMetadataFactory);

    // List information.
    $serializerExtractor->getProperties($class);

DoctrineExtractor
~~~~~~~~~~~~~~~~~

.. note::

    This extractor depends on the `symfony/doctrine-bridge`_ and `doctrine/orm`_
    libraries.

Using entity mapping data from `Doctrine ORM`_, the
:class:`Symfony\\Bridge\\Doctrine\\PropertyInfo\\DoctrineExtractor`
- located in the Doctrine bridge - provides list and type information.
This extractor is not registered automatically with the ``property_info``
service.

.. code-block:: php

    use Doctrine\ORM\EntityManager;
    use Doctrine\ORM\Tools\Setup;
    use Symfony\Bridge\Doctrine\PropertyInfo\DoctrineExtractor;

    $config = Setup::createAnnotationMetadataConfiguration([__DIR__], true);
    $entityManager = EntityManager::create([
        'driver' => 'pdo_sqlite',
        // ...
    ], $config);
    $doctrineExtractor = new DoctrineExtractor($entityManager->getMetadataFactory());

    // List information.
    $doctrineExtractor->getProperties($class);
    // Type information.
    $doctrineExtractor->getTypes($class, $property);

.. _`components-property-information-extractors-creation`:

Creating Your Own Extractors
----------------------------

You can create your own property information extractors by creating a
class that implements one or more of the following interfaces:
:class:`Symfony\\Component\\PropertyInfo\\PropertyAccessExtractorInterface`,
:class:`Symfony\\Component\\PropertyInfo\\PropertyDescriptionExtractorInterface`,
:class:`Symfony\\Component\\PropertyInfo\\PropertyListExtractorInterface`
and :class:`Symfony\\Component\\PropertyInfo\\PropertyTypeExtractorInterface`.

If you have enabled the PropertyInfo component with the FrameworkBundle,
you can automatically register your extractor class with the ``property_info``
service by defining it as a service with one or more of the following
:ref:`tags <book-service-container-tags>`:

* ``property_info.list_extractor`` if it provides list information.
* ``property_info.type_extractor`` if it provides type information.
* ``property_info.description_extractor`` if it provides description information.
* ``property_info.access_extractor`` if it provides access information.

.. _Packagist: https://packagist.org/packages/symfony/property-info
.. _`phpDocumentor Reflection`: https://github.com/phpDocumentor/Reflection
.. _`phpdocumentor/reflection`: https://packagist.org/packages/phpdocumentor/reflection
.. _`Doctrine ORM`: http://www.doctrine-project.org/projects/orm.html
.. _`symfony/serializer`: https://packagist.org/packages/symfony/serializer
.. _`symfony/doctrine-bridge`: https://packagist.org/packages/symfony/doctrine-bridge
.. _`doctrine/orm`: https://packagist.org/packages/doctrine/orm
.. _`class constant`: http://php.net/manual/en/language.oop5.basic.php#language.oop5.basic.class.class

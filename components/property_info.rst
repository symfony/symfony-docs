.. index::
    single: PropertyInfo
    single: Components; PropertInfo

The PropertyInfo Component
==========================

    The PropertyInfo component extracts information about the properties of PHP
    classes using different sources of metadata.

The PropertyInfo component extracts the following information:

* List of properties exposed by a class;
* Type of each property (``int``, ``array``, ``callable``, etc.);
* The short and long DockBlock description (if available);
* Whether the property is readable and/or writable.

This information is obtained using several *extractors*, which work by parsing
different sources of properties metadata:

* ``ReflectionExtractor``: uses the built-in PHP Reflection API to parse setter
  type hints, return and scalar type hints (for PHP 7+) and accessor methods
  (*getXxx()*, *hasXxx()*, *isXxx()*);
* ``PhpDocExtractor``: parses the PHPDoc of properties and accessor methods;
* ``DoctrineExtractor``: gets the metadata provided by the Doctrine ORM;
* ``SerializerExtractor``: gets groups metadata from the Serializer component.

Besides these built-in extractors, you can create your own custom extractors,
as explained in the following sections.

Installation
------------

You can install the component in 2 different ways:

* :doc:`Install it via Composer </components/using_components>` (``symfony/property-info``
  on `Packagist`_);
* Use the official Git repository (https://github.com/symfony/PropertyInfo).

.. include:: /components/require_autoload.rst.inc

Optional dependencies
~~~~~~~~~~~~~~~~~~~~~

* To use the :class:`Symfony\\Component\\PropertyInfo\\Extractor\\PhpDocExtractor`,
install `phpDocumentator Reflection`_.
* To use the :class:`Symfony\\Component\\PropertyInfo\\Extractor\\SerializerExtractor`
extractor, install the :doc:`Serializer component </components/serializer>`.
* To use the :class:`Symfony\\Bridge\\Doctrine\\PropertyInfo\\DoctrineExtractor`,
install the Doctrine Bridge and the `Doctrine ORM`_.

Usage
-----

Before using the PropertyInfo component, you need to register the extractors by
passing them to the constructor of the :class:`Symfony\\Component\\PropertyInfo\\PropertyInfo`
class::

    use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
    use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
    use Symfony\Component\PropertyInfo\PropertyInfo;

    $reflectionExtractor = new ReflectionExtractor();
    $phpDocExtractor = new PhpDocExtractor();

    $propertyInfo = new PropertyInfo(
        array($reflectionExtractor),
        array($phpDocExtractor, $reflectionExtractor),
        array($phpDocExtractor),
        array($reflectionExtractor)
    );

The **first argument** must be an array of implementations of :class:`Symfony\\Component\\PropertyInfo\\PropertyListExtractorInterface`.
These extractors are responsible of extracting the list of properties of a class.

The **second argument** must be an array of implementations of :class:`Symfony\\Component\\PropertyInfo\\PropertyTypeExtractorInterface`.
These extractors are responsible of extracting types of a property.

The **third argument** must be an array of implementations of :class:`Symfony\\Component\\PropertyInfo\\PropertyDescriptionExtractorInterface`.
These extractors are responsible of extracting the DocBlock description of a
property.

The **fourth argument** must be an array of implementations of :class:`Symfony\\Component\\PropertyInfo\\PropertyAccessExtractorInterface`.
These extractors are responsible of guessing if a property is readable and/or
writable.

The order in which extractors are registered is important, because the returned
data will be the one returned by the first extractor which returns something
different than ``null``.

Once instantiated, use the ``PropertyInfo`` class to retrieve info about any of
the properties of a class. Consider the following example class::

    class MyClass
    {
        /**
         * The short description of foo.
         *
         * And here is its extended description.
         *
         * @var string
         */
        public $foo;

        /**
         * Virtual property.
         */
        private function setBar(array $bar)
        {
        }
    }

Given the previous ``$propertyInfo`` object, you can easily extract all the
information about any property::

    $properties = $propertyInfo->getProperties('MyClass');
    // $properties = array('foo')

    $barIsReadable = $propertyInfo->isReadable('MyClass', 'bar');
    // $barIsReadable = false

    $fooIsWritable = $propertyInfo->isWritable('MyClass', 'foo');
    // $fooIsWritable = true

Extraction Methods
------------------

These are the public methods exposed by the API of this component:

:method:`Symfony\\Component\\PropertyInfo\\PropertyInfoExtractorInterface::getProperties`
    It returns an array with the names of all the properties exposed by the given
    class:

        $result = $propertyInfo->getProperties('MyClass');

:method:`Symfony\\Component\\PropertyInfo\\PropertyInfoExtractorInterface::isReadable`
    It returns ``true`` when the value of the given property is readable in any
    way for the given class (through the property itself or through some access
    method)::

        $result = $propertyInfo->isReadable('MyClass', 'foo');

:method:`Symfony\\Component\\PropertyInfo\\PropertyInfoExtractorInterface::isWritable`
    It returns ``true`` when the value of the given property is writable in any
    way for the given class (through the property itself or through some access
    method):

        $result = $propertyInfo->isWritable('MyClass', 'foo');

:method:`Symfony\\Component\\PropertyInfo\\PropertyInfoExtractorInterface::getShortDescription`
    It returns the short PHPDoc description for the given property and class (or
    ``null`` if no short description is available). This short description corresponds
    to the first line of the full PHPDoc description::

        $result = $propertyInfo->getShortDescription('MyClass', 'foo');

:method:`Symfony\\Component\\PropertyInfo\\PropertyInfoExtractorInterface::getLongDescription`
    It returns the full PHPDoc description for the given property and class (or
    ``null`` if no description is available). This long description corresponds
    to the full PHPDoc description except its first line::

        $result = $propertyInfo->getLongDescription('MyClass', 'foo');

:method:`Symfony\\Component\\PropertyInfo\\PropertyInfoExtractorInterface::getTypes`
    It returns an array of :class:`Symfony\\Component\\PropertyInfo\\Type` objects
    describing the type of each property of the given class and property::

        $result = $propertyInfo->getTypes('MyClass', 'foo');

    Since PHP doesn't support explicit type definition, these ``Type`` objects
    represent complex PHP types. Using the same ``MyClass`` class as shown above,
    the content of the ``$result`` variable would be::

        array(1) {
          [0] =>
          class Symfony\Component\PropertyInfo\Type#7 (6) {
            private $builtinType => string(6) "string"
            private $nullable => bool(false)
            private $class => NULL
            private $collection => bool(false)
            private $collectionKeyType => NULL
            private $collectionValueType => NULL
          }
        }

Extractors
----------

Besides the basic ``ReflectionExtractor`` and ``PhpDocExtractors`` extractors,
Symfony framework includes two additional extractors: ``ReflectionExtractor``
and ``PhpDocExtractors``.

The ``DoctrineExtractor``
~~~~~~~~~~~~~~~~~~~~~~~~~

The Doctrine extractor reuses the metadata of the Doctrine ORM to extract the
list of properties and their type. It implements ``PropertyListExtractorInterface``
and ``PropertyTypeExtractorInterface`` interfaces.

First, instantiate the extractor::

    use Doctrine\ORM\EntityManager;
    use Doctrine\ORM\Tools\Setup;
    use Symfony\Bridge\Doctrine\PropertyInfo\DoctrineExtractor;

    $config = Setup::createAnnotationMetadataConfiguration([__DIR__], true);
    $entityManager = EntityManager::create([
        'driver' => 'pdo_sqlite',
        // ...
    ], $config);

    $doctrineExtractor = new DoctrineExtractor($entityManager->getMetadataFactory());

Then, retrieve information about an entity mapped with Doctrine::

    use Doctrine\ORM\Mapping\Column;
    use Doctrine\ORM\Mapping\Entity;
    use Doctrine\ORM\Mapping\Id;

    /**
     * @Entity
     */
    class MyEntity
    {
        /**
         * @Id
         * @Column(type="integer")
         */
        public $id;
    }

    var_dump($doctrineExtractor->getProperties('MyEntity'));
    var_dump($doctrineExtractor->getTypes('MyEntity', 'id'));

    /*
    Output:

    array(1) {
      [0] => string(2) "id"
    }

    array(1) {
      [0] =>
      class Symfony\Component\PropertyInfo\Type#27 (6) {
        private $builtinType => string(3) "int"
        private $nullable => bool(false)
        private $class => NULL
        private $collection => bool(false)
        private $collectionKeyType => NULL
        private $collectionValueType => NULL
      }
    }
    */

You can also register this extractor in the ``PropertyInfo`` class::

    $propertyInfo = new PropertyInfo(
        array($reflectionExtractor, $doctrineExtractor),
        array($doctrineExtractor, $phpDocExtractor, $reflectionExtractor)
    );

The ``SerializerExtractor``
~~~~~~~~~~~~~~~~~~~~~~~~~~~

The ``SerializerExtractor`` leverages groups metadata of the Symfony Serializer
Component (2.7+) to list properties having the groups passed in the context.

First, instantiate the extractor::

    use Doctrine\Common\Annotations\AnnotationReader;
    use Symfony\Component\PropertyInfo\Extractor\SerializerExtractor;
    use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
    use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;

    $serializerClassMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
    $serializerExtractor = new SerializerExtractor($serializerClassMetadataFactory);

Then, use it to extract the information::

    use Symfony\Component\Serializer\Annotation\Groups;

    class Foo
    {
        /**
         * @Groups({"a", "b"})
         */
        public $bar;
        public $baz;
    }

    $serializerExtractor->getProperties('Foo', array('serializer_groups' => array('a')));
    /*
    Output:
    array(1) {
      [0] => string(2) "bar"
    }
    */

.. _`Packagist`: https://packagist.org/packages/symfony/property-info
.. _`phpDocumentator Reflection`: https://github.com/phpDocumentor/Reflection
.. _`Doctrine ORM`: http://www.doctrine-project.org/projects/orm.html

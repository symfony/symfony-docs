.. index::
    single: PropertyInfo
    single: Components; PropertInfo

The PropertyInfo Component
==========================

    The PropertyInfo component extracts information about PHP class' properties
    using metadata of popular sources.

The PropertyInfo component is able to extract the following information:

* List of properties exposed by a class
* Types of a property
* It's short and long DockBlock description
* If the property is readable or writable

To do so, the component use extractors. It natively support the following
metadata sources:

* ``ReflectionExtractor``: use the PHP Reflection API (setter type hint,
  return and scalar type hint for PHP 7+, accessor methods)
* ``PhpDocExtractor``: use the PHPDoc of properties and accessor methods
* ``DoctrineExtractor``: use metadata provided by the Doctrine ORM
* ``SerializerExtractor``: use groups metadata of the Serializer component

Custom extractors can be also be registered.

Installation
------------

You can install the component in 2 different ways:

* :doc:`Install it via Composer </components/using_components>` (``symfony/property-info``
  on `Packagist`_);
* Use the official Git repository (https://github.com/symfony/PropertyInfo).

.. include:: /components/require_autoload.rst.inc

To use the :class:`Symfony\\Component\\PropertyInfo\\Extractor\\PhpDocExtractor`,
install `phpDocumentator Reflection`_.
To use the :class:`Symfony\\Component\\PropertyInfo\\Extractor\\SerializerExtractor`
extractor, install the :doc:`Serializer component </components/serializer>`.
To use the :class:`Symfony\\Bridge\\Doctrine\\PropertyInfo\\DoctrineExtractor`,
install the Doctrine Bridge and the `Doctrine ORM`_.

Usage
-----

Using the PropertyInfo component is straightforward. You need to register
all metadata extractors you want to use in the constructor of the :class:`Symfony\\Component\\PropertyInfo\\PropertyInfo`
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

An array of implementations of :class:`Symfony\\Component\\PropertyInfo\\PropertyListExtractorInterface`
must be passed as first parameter. These extractors are responsible of extracting
the list of properties of a class.

An array of implementations of :class:`Symfony\\Component\\PropertyInfo\\PropertyTypeExtractorInterface`
must be passed as second parameter. These extractors are responsible of extracting
types of a property.

An array of implementations of :class:`Symfony\\Component\\PropertyInfo\\PropertyDescriptionExtractorInterface`
must be passed as third parameter. These extractors are responsible of extracting
short and long DocBlock description of a property.

Finally, an array of implementations of :class:`Symfony\\Component\\PropertyInfo\\PropertyAccessExtractorInterface`
must be passed as fourth parameter. These extractors are responsible of guessing
if a property is readable or writable.

The order of registration matter: the data returned will be the one returned
by the first extractor if different than ``null``.


Once instantiated, the ``PropertyInfo`` class can be used to retrieve info
about properties of a class::

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

    var_dump($propertyInfo->getProperties('MyClass'));
    var_dump($propertyInfo->getTypes('MyClass', 'foo'));
    var_dump($propertyInfo->getTypes('MyClass', 'bar'));
    var_dump($propertyInfo->isReadable('MyClass', 'foo'));
    var_dump($propertyInfo->isReadable('MyClass', 'bar'));
    var_dump($propertyInfo->isWritable('MyClass', 'foo'));
    var_dump($propertyInfo->getShortDescription('MyClass', 'bar'));
    var_dump($propertyInfo->getLongDescription('MyClass', 'foo'));

    /*
    Output:
    array(1) {
      [0] =>
      string(3) "foo"
    }
    array(1) {
      [0] =>
      class Symfony\Component\PropertyInfo\Type#7 (6) {
        private $builtinType =>
        string(6) "string"
        private $nullable =>
        bool(false)
        private $class =>
        NULL
        private $collection =>
        bool(false)
        private $collectionKeyType =>
        NULL
        private $collectionValueType =>
        NULL
      }
    }
    array(1) {
      [0] =>
      class Symfony\Component\PropertyInfo\Type#129 (6) {
        private $builtinType =>
        string(5) "array"
        private $nullable =>
        bool(false)
        private $class =>
        NULL
        private $collection =>
        bool(true)
        private $collectionKeyType =>
        NULL
        private $collectionValueType =>
        NULL
      }
    }
    bool(true)
    bool(false)
    bool(true)
    string(17) "Virtual property."
    string(37) "And here is its extended description."
    */

As PHP doesn't support explicit type definition, ``PropertyInfo::getTypes``
use registered extractors to an array of :class:`Symfony\\Component\\PropertyInfo\\Type`
value objects.
Those object represent complex PHP types. Refer to the API documentation
of this class for more details.

Extractors
----------

Symfony is shipped with two extractors in addition to the already presented
``ReflectionExtractor`` and ``PhpDocExtractors``.
Moreover, custom extractors can be created by implementing the extractor
interfaces provided with the PropertyInfo component.

The ``DoctrineExtractor``
~~~~~~~~~~~~~~~~~~~~~~~~~

The Doctrine extractor reuse metadata of the Doctrine ORM to extract the
list of properties and their type. It implements ``PropertyListExtractorInterface``
and ``PropertyTypeExtractorInterface`` interfaces.

Instantiate it::

    use Doctrine\ORM\EntityManager;
    use Doctrine\ORM\Tools\Setup;
    use Symfony\Bridge\Doctrine\PropertyInfo\DoctrineExtractor;

    $config = Setup::createAnnotationMetadataConfiguration([__DIR__], true);
    $entityManager = EntityManager::create([
        'driver' => 'pdo_sqlite',
        // ...
    ], $config);

    $doctrineExtractor = new DoctrineExtractor($entityManager->getMetadataFactory());

You can now use it to retrieve information about an entity mapped with Doctrine::

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
      [0] =>
      string(2) "id"
    }
    array(1) {
      [0] =>
      class Symfony\Component\PropertyInfo\Type#27 (6) {
        private $builtinType =>
        string(3) "int"
        private $nullable =>
        bool(false)
        private $class =>
        NULL
        private $collection =>
        bool(false)
        private $collectionKeyType =>
        NULL
        private $collectionValueType =>
        NULL
      }
    }
    */

Of course you can also register this extractor in the ``PropertyInfo`` class::

    $propertyInfo = new PropertyInfo(
        array($reflectionExtractor, $doctrineExtractor),
        array($doctrineExtractor, $phpDocExtractor, $reflectionExtractor)
    );

The ``SerializerExtractor``
~~~~~~~~~~~~~~~~~~~~~~~~~~~

The ``SerializerExtractor`` leverages groups metadata of the Symfony Serializer
Component (2.7+) to list properties having the groups passed in the context.

Instantiate it::

    use Doctrine\Common\Annotations\AnnotationReader;
    use Symfony\Component\PropertyInfo\Extractor\SerializerExtractor;
    use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
    use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;

    $serializerClassMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
    $serializerExtractor = new SerializerExtractor($serializerClassMetadataFactory);

Usage::

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
      [0] =>
      string(2) "bar"
    }
    */

.. _`Packagist`: https://packagist.org/packages/symfony/property-info
.. _`phpDocumentator Reflection`: https://github.com/phpDocumentor/Reflection
.. _`Doctrine ORM`: http://www.doctrine-project.org/projects/orm.html

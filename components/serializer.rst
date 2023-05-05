.. index::
   single: Serializer
   single: Components; Serializer

The Serializer Component
========================

    The Serializer component is meant to be used to turn objects into a
    specific format (XML, JSON, YAML, ...) and the other way around.

In order to do so, the Serializer component follows the following schema.

.. raw:: html

    <object data="../_images/components/serializer/serializer_workflow.svg" type="image/svg+xml"></object>

As you can see in the picture above, an array is used as an intermediary between
objects and serialized contents. This way, encoders will only deal with turning
specific **formats** into **arrays** and vice versa. The same way, Normalizers
will deal with turning specific **objects** into **arrays** and vice versa.

Serialization is a complex topic. This component may not cover all your use cases out of the box,
but it can be useful for developing tools to serialize and deserialize your objects.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/serializer

.. include:: /components/require_autoload.rst.inc

To use the ``ObjectNormalizer``, the :doc:`PropertyAccess component </components/property_access>`
must also be installed.

Usage
-----

.. seealso::

    This article explains the philosophy of the Serializer and gets you familiar
    with the concepts of normalizers and encoders. The code examples assume
    that you use the Serializer as an independent component. If you are using
    the Serializer in a Symfony application, read :doc:`/serializer` after you
    finish this article.

To use the Serializer component, set up the
:class:`Symfony\\Component\\Serializer\\Serializer` specifying which encoders
and normalizer are going to be available::

    use Symfony\Component\Serializer\Encoder\JsonEncoder;
    use Symfony\Component\Serializer\Encoder\XmlEncoder;
    use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
    use Symfony\Component\Serializer\Serializer;

    $encoders = [new XmlEncoder(), new JsonEncoder()];
    $normalizers = [new ObjectNormalizer()];

    $serializer = new Serializer($normalizers, $encoders);

The preferred normalizer is the
:class:`Symfony\\Component\\Serializer\\Normalizer\\ObjectNormalizer`,
but other normalizers are available. All the examples shown below use
the ``ObjectNormalizer``.

Serializing an Object
---------------------

For the sake of this example, assume the following class already
exists in your project::

    namespace App\Model;

    class Person
    {
        private int $age;
        private string $name;
        private bool $sportsperson;
        private ?\DateTime $createdAt;

        // Getters
        public function getAge(): int
        {
            return $this->age;
        }

        public function getName(): string
        {
            return $this->name;
        }

        public function getCreatedAt()
        {
            return $this->createdAt;
        }

        // Issers
        public function isSportsperson(): bool
        {
            return $this->sportsperson;
        }

        // Setters
        public function setAge(int $age): void
        {
            $this->age = $age;
        }

        public function setName(string $name): void
        {
            $this->name = $name;
        }

        public function setSportsperson(bool $sportsperson): void
        {
            $this->sportsperson = $sportsperson;
        }

        public function setCreatedAt(\DateTime $createdAt = null): void
        {
            $this->createdAt = $createdAt;
        }
    }

Now, if you want to serialize this object into JSON, you only need to
use the Serializer service created before::

    use App\Model\Person;

    $person = new Person();
    $person->setName('foo');
    $person->setAge(99);
    $person->setSportsperson(false);

    $jsonContent = $serializer->serialize($person, 'json');

    // $jsonContent contains {"name":"foo","age":99,"sportsperson":false,"createdAt":null}

    echo $jsonContent; // or return it in a Response

The first parameter of the :method:`Symfony\\Component\\Serializer\\Serializer::serialize`
is the object to be serialized and the second is used to choose the proper encoder,
in this case :class:`Symfony\\Component\\Serializer\\Encoder\\JsonEncoder`.

Deserializing an Object
-----------------------

You'll now learn how to do the exact opposite. This time, the information
of the ``Person`` class would be encoded in XML format::

    use App\Model\Person;

    $data = <<<EOF
    <person>
        <name>foo</name>
        <age>99</age>
        <sportsperson>false</sportsperson>
    </person>
    EOF;

    $person = $serializer->deserialize($data, Person::class, 'xml');

In this case, :method:`Symfony\\Component\\Serializer\\Serializer::deserialize`
needs three parameters:

#. The information to be decoded
#. The name of the class this information will be decoded to
#. The encoder used to convert that information into an array

By default, additional attributes that are not mapped to the denormalized object
will be ignored by the Serializer component. If you prefer to throw an exception
when this happens, set the ``AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES`` context option to
``false`` and provide an object that implements ``ClassMetadataFactoryInterface``
when constructing the normalizer::

    use App\Model\Person;

    $data = <<<EOF
    <person>
        <name>foo</name>
        <age>99</age>
        <city>Paris</city>
    </person>
    EOF;

    // $loader is any of the valid loaders explained later in this article
    $classMetadataFactory = new ClassMetadataFactory($loader);
    $normalizer = new ObjectNormalizer($classMetadataFactory);
    $serializer = new Serializer([$normalizer]);

    // this will throw a Symfony\Component\Serializer\Exception\ExtraAttributesException
    // because "city" is not an attribute of the Person class
    $person = $serializer->deserialize($data, Person::class, 'xml', [
        AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => false,
    ]);

Deserializing in an Existing Object
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The serializer can also be used to update an existing object::

    // ...
    $person = new Person();
    $person->setName('bar');
    $person->setAge(99);
    $person->setSportsperson(true);

    $data = <<<EOF
    <person>
        <name>foo</name>
        <age>69</age>
    </person>
    EOF;

    $serializer->deserialize($data, Person::class, 'xml', [AbstractNormalizer::OBJECT_TO_POPULATE => $person]);
    // $person = App\Model\Person(name: 'foo', age: '69', sportsperson: true)

This is a common need when working with an ORM.

The ``AbstractNormalizer::OBJECT_TO_POPULATE`` is only used for the top level object. If that object
is the root of a tree structure, all child elements that exist in the
normalized data will be re-created with new instances.

When the ``AbstractObjectNormalizer::DEEP_OBJECT_TO_POPULATE`` option is set to
true, existing children of the root ``OBJECT_TO_POPULATE`` are updated from the
normalized data, instead of the denormalizer re-creating them. Note that
``DEEP_OBJECT_TO_POPULATE`` only works for single child objects, but not for
arrays of objects. Those will still be replaced when present in the normalized
data.

Context
-------

Many Serializer features can be configured :doc:`using a context </serializer#serializer-context>`.

.. _component-serializer-attributes-groups:

Attributes Groups
-----------------

Sometimes, you want to serialize different sets of attributes from your
entities. Groups are a handy way to achieve this need.

Assume you have the following plain-old-PHP object::

    namespace Acme;

    class MyObj
    {
        public $foo;

        private $bar;

        public function getBar()
        {
            return $this->bar;
        }

        public function setBar($bar)
        {
            return $this->bar = $bar;
        }
    }

The definition of serialization can be specified using annotations, XML
or YAML. The :class:`Symfony\\Component\\Serializer\\Mapping\\Factory\\ClassMetadataFactory`
that will be used by the normalizer must be aware of the format to use.

The following code shows how to initialize the :class:`Symfony\\Component\\Serializer\\Mapping\\Factory\\ClassMetadataFactory`
for each format:

* Annotations in PHP files::

    use Doctrine\Common\Annotations\AnnotationReader;
    use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
    use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;

    $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));

* YAML files::

    use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
    use Symfony\Component\Serializer\Mapping\Loader\YamlFileLoader;

    $classMetadataFactory = new ClassMetadataFactory(new YamlFileLoader('/path/to/your/definition.yaml'));

* XML files::

    use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
    use Symfony\Component\Serializer\Mapping\Loader\XmlFileLoader;

    $classMetadataFactory = new ClassMetadataFactory(new XmlFileLoader('/path/to/your/definition.xml'));

.. _component-serializer-attributes-groups-annotations:

Then, create your groups definition:

.. configuration-block::

    .. code-block:: php-attributes

        namespace Acme;

        use Symfony\Component\Serializer\Annotation\Groups;

        class MyObj
        {
            #[Groups(['group1', 'group2'])]
            public $foo;

            #[Groups(['group4'])]
            public $anotherProperty;

            #[Groups(['group3'])]
            public function getBar() // is* methods are also supported
            {
                return $this->bar;
            }

            // ...
        }

    .. code-block:: yaml

        Acme\MyObj:
            attributes:
                foo:
                    groups: ['group1', 'group2']
                anotherProperty:
                    groups: ['group4']
                bar:
                    groups: ['group3']

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <serializer xmlns="http://symfony.com/schema/dic/serializer-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/serializer-mapping
                https://symfony.com/schema/dic/serializer-mapping/serializer-mapping-1.0.xsd"
        >
            <class name="Acme\MyObj">
                <attribute name="foo">
                    <group>group1</group>
                    <group>group2</group>
                </attribute>

                <attribute name="anotherProperty">
                    <group>group4</group>
                </attribute>

                <attribute name="bar">
                    <group>group3</group>
                </attribute>
            </class>
        </serializer>

You are now able to serialize only attributes in the groups you want::

    use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
    use Symfony\Component\Serializer\Serializer;

    $obj = new MyObj();
    $obj->foo = 'foo';
    $obj->anotherProperty = 'anotherProperty';
    $obj->setBar('bar');

    $normalizer = new ObjectNormalizer($classMetadataFactory);
    $serializer = new Serializer([$normalizer]);

    $data = $serializer->normalize($obj, null, ['groups' => 'group1']);
    // $data = ['foo' => 'foo'];

    $obj2 = $serializer->denormalize(
        ['foo' => 'foo', 'anotherProperty' => 'anotherProperty', 'bar' => 'bar'],
        'MyObj',
        null,
        ['groups' => ['group1', 'group3']]
    );
    // $obj2 = MyObj(foo: 'foo', bar: 'bar')

    // To get all groups, use the special value `*` in `groups`
    $obj3 = $serializer->denormalize(
        ['foo' => 'foo', 'anotherProperty' => 'anotherProperty', 'bar' => 'bar'],
        'MyObj',
        null,
        ['groups' => ['*']]
    );
    // $obj2 = MyObj(foo: 'foo', anotherProperty: 'anotherProperty', bar: 'bar')

.. _ignoring-attributes-when-serializing:

Selecting Specific Attributes
-----------------------------

It is also possible to serialize only a set of specific attributes::

    use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
    use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
    use Symfony\Component\Serializer\Serializer;

    class User
    {
        public $familyName;
        public $givenName;
        public $company;
    }

    class Company
    {
        public $name;
        public $address;
    }

    $company = new Company();
    $company->name = 'Les-Tilleuls.coop';
    $company->address = 'Lille, France';

    $user = new User();
    $user->familyName = 'Dunglas';
    $user->givenName = 'Kévin';
    $user->company = $company;

    $serializer = new Serializer([new ObjectNormalizer()]);

    $data = $serializer->normalize($user, null, [AbstractNormalizer::ATTRIBUTES => ['familyName', 'company' => ['name']]]);
    // $data = ['familyName' => 'Dunglas', 'company' => ['name' => 'Les-Tilleuls.coop']];

Only attributes that are not ignored (see below) are available.
If some serialization groups are set, only attributes allowed by those groups can be used.

As for groups, attributes can be selected during both the serialization and deserialization process.

Ignoring Attributes
-------------------

All attributes are included by default when serializing objects. There are two
options to ignore some of those attributes.

Option 1: Using ``@Ignore`` Annotation
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. configuration-block::

    .. code-block:: php-attributes

        namespace App\Model;

        use Symfony\Component\Serializer\Annotation\Ignore;

        class MyClass
        {
            public $foo;

            #[Ignore]
            public $bar;
        }

    .. code-block:: yaml

        App\Model\MyClass:
            attributes:
                bar:
                    ignore: true

    .. code-block:: xml

        <?xml version="1.0" ?>
        <serializer xmlns="http://symfony.com/schema/dic/serializer-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/serializer-mapping
                https://symfony.com/schema/dic/serializer-mapping/serializer-mapping-1.0.xsd"
        >
            <class name="App\Model\MyClass">
                <attribute name="bar" ignore="true"/>
            </class>
        </serializer>

You can now ignore specific attributes during serialization::

    use App\Model\MyClass;
    use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
    use Symfony\Component\Serializer\Serializer;

    $obj = new MyClass();
    $obj->foo = 'foo';
    $obj->bar = 'bar';

    $normalizer = new ObjectNormalizer($classMetadataFactory);
    $serializer = new Serializer([$normalizer]);

    $data = $serializer->normalize($obj);
    // $data = ['foo' => 'foo'];

Option 2: Using the Context
~~~~~~~~~~~~~~~~~~~~~~~~~~~

Pass an array with the names of the attributes to ignore using the
``AbstractNormalizer::IGNORED_ATTRIBUTES`` key in the ``context`` of the
serializer method::

    use Acme\Person;
    use Symfony\Component\Serializer\Encoder\JsonEncoder;
    use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
    use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
    use Symfony\Component\Serializer\Serializer;

    $person = new Person();
    $person->setName('foo');
    $person->setAge(99);

    $normalizer = new ObjectNormalizer();
    $encoder = new JsonEncoder();

    $serializer = new Serializer([$normalizer], [$encoder]);
    $serializer->serialize($person, 'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => ['age']]); // Output: {"name":"foo"}

.. _component-serializer-converting-property-names-when-serializing-and-deserializing:

Converting Property Names when Serializing and Deserializing
------------------------------------------------------------

Sometimes serialized attributes must be named differently than properties
or getter/setter methods of PHP classes.

The Serializer component provides a handy way to translate or map PHP field
names to serialized names: The Name Converter System.

Given you have the following object::

    class Company
    {
        public $name;
        public $address;
    }

And in the serialized form, all attributes must be prefixed by ``org_`` like
the following::

    {"org_name": "Acme Inc.", "org_address": "123 Main Street, Big City"}

A custom name converter can handle such cases::

    use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

    class OrgPrefixNameConverter implements NameConverterInterface
    {
        public function normalize(string $propertyName): string
        {
            return 'org_'.$propertyName;
        }

        public function denormalize(string $propertyName): string
        {
            // removes 'org_' prefix
            return 'org_' === substr($propertyName, 0, 4) ? substr($propertyName, 4) : $propertyName;
        }
    }

The custom name converter can be used by passing it as second parameter of any
class extending :class:`Symfony\\Component\\Serializer\\Normalizer\\AbstractNormalizer`,
including :class:`Symfony\\Component\\Serializer\\Normalizer\\GetSetMethodNormalizer`
and :class:`Symfony\\Component\\Serializer\\Normalizer\\PropertyNormalizer`::

    use Symfony\Component\Serializer\Encoder\JsonEncoder;
    use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
    use Symfony\Component\Serializer\Serializer;

    $nameConverter = new OrgPrefixNameConverter();
    $normalizer = new ObjectNormalizer(null, $nameConverter);

    $serializer = new Serializer([$normalizer], [new JsonEncoder()]);

    $company = new Company();
    $company->name = 'Acme Inc.';
    $company->address = '123 Main Street, Big City';

    $json = $serializer->serialize($company, 'json');
    // {"org_name": "Acme Inc.", "org_address": "123 Main Street, Big City"}
    $companyCopy = $serializer->deserialize($json, Company::class, 'json');
    // Same data as $company

.. note::

    You can also implement
    :class:`Symfony\\Component\\Serializer\\NameConverter\\AdvancedNameConverterInterface`
    to access the current class name, format and context.

.. _using-camelized-method-names-for-underscored-attributes:

CamelCase to snake_case
~~~~~~~~~~~~~~~~~~~~~~~

In many formats, it's common to use underscores to separate words (also known
as snake_case). However, in Symfony applications is common to use CamelCase to
name properties (even though the `PSR-1 standard`_ doesn't recommend any
specific case for property names).

Symfony provides a built-in name converter designed to transform between
snake_case and CamelCased styles during serialization and deserialization
processes::

    use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
    use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

    $normalizer = new ObjectNormalizer(null, new CamelCaseToSnakeCaseNameConverter());

    class Person
    {
        private $firstName;

        public function __construct($firstName)
        {
            $this->firstName = $firstName;
        }

        public function getFirstName()
        {
            return $this->firstName;
        }
    }

    $kevin = new Person('Kévin');
    $normalizer->normalize($kevin);
    // ['first_name' => 'Kévin'];

    $anne = $normalizer->denormalize(['first_name' => 'Anne'], 'Person');
    // Person object with firstName: 'Anne'

Configure name conversion using metadata
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When using this component inside a Symfony application and the class metadata
factory is enabled as explained in the :ref:`Attributes Groups section <component-serializer-attributes-groups>`,
this is already set up and you only need to provide the configuration. Otherwise::

    // ...
    use Symfony\Component\Serializer\Encoder\JsonEncoder;
    use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
    use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
    use Symfony\Component\Serializer\Serializer;

    $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));

    $metadataAwareNameConverter = new MetadataAwareNameConverter($classMetadataFactory);

    $serializer = new Serializer(
        [new ObjectNormalizer($classMetadataFactory, $metadataAwareNameConverter)],
        ['json' => new JsonEncoder()]
    );

Now configure your name conversion mapping. Consider an application that
defines a ``Person`` entity with a ``firstName`` property:

.. configuration-block::

    .. code-block:: php-attributes

        namespace App\Entity;

        use Symfony\Component\Serializer\Annotation\SerializedName;

        class Person
        {
            #[SerializedName('customer_name')]
            private $firstName;

            public function __construct($firstName)
            {
                $this->firstName = $firstName;
            }

            // ...
        }

    .. code-block:: yaml

        App\Entity\Person:
            attributes:
                firstName:
                    serialized_name: customer_name

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <serializer xmlns="http://symfony.com/schema/dic/serializer-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/serializer-mapping
                https://symfony.com/schema/dic/serializer-mapping/serializer-mapping-1.0.xsd"
        >
            <class name="App\Entity\Person">
                <attribute name="firstName" serialized-name="customer_name"/>
            </class>
        </serializer>

This custom mapping is used to convert property names when serializing and
deserializing objects::

    $serialized = $serializer->serialize(new Person('Kévin'), 'json');
    // {"customer_name": "Kévin"}

Serializing Boolean Attributes
------------------------------

If you are using isser methods (methods prefixed by ``is``, like
``App\Model\Person::isSportsperson()``), the Serializer component will
automatically detect and use it to serialize related attributes.

The ``ObjectNormalizer`` also takes care of methods starting with ``has``, ``get``,
and ``can``.

.. versionadded:: 6.1

    The support of canners (methods prefixed by ``can``) was introduced in Symfony 6.1.

Using Callbacks to Serialize Properties with Object Instances
-------------------------------------------------------------

When serializing, you can set a callback to format a specific object property::

    use App\Model\Person;
    use Symfony\Component\Serializer\Encoder\JsonEncoder;
    use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
    use Symfony\Component\Serializer\Serializer;

    $encoder = new JsonEncoder();

    // all callback parameters are optional (you can omit the ones you don't use)
    $dateCallback = function ($innerObject, $outerObject, string $attributeName, string $format = null, array $context = []) {
        return $innerObject instanceof \DateTime ? $innerObject->format(\DateTime::ISO8601) : '';
    };

    $defaultContext = [
        AbstractNormalizer::CALLBACKS => [
            'createdAt' => $dateCallback,
        ],
    ];

    $normalizer = new GetSetMethodNormalizer(null, null, null, null, null, $defaultContext);

    $serializer = new Serializer([$normalizer], [$encoder]);

    $person = new Person();
    $person->setName('cordoval');
    $person->setAge(34);
    $person->setCreatedAt(new \DateTime('now'));

    $serializer->serialize($person, 'json');
    // Output: {"name":"cordoval", "age": 34, "createdAt": "2014-03-22T09:43:12-0500"}

.. _component-serializer-normalizers:

Normalizers
-----------

Normalizers turn **objects** into **arrays** and vice versa. They implement
:class:`Symfony\\Component\\Serializer\\Normalizer\\NormalizerInterface` for
normalizing (object to array) and
:class:`Symfony\\Component\\Serializer\\Normalizer\\DenormalizerInterface` for
denormalizing (array to object).

Normalizers are enabled in the serializer passing them as its first argument::

    use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
    use Symfony\Component\Serializer\Serializer;

    $normalizers = [new ObjectNormalizer()];
    $serializer = new Serializer($normalizers, []);

Built-in Normalizers
~~~~~~~~~~~~~~~~~~~~

The Serializer component provides several built-in normalizers:

:class:`Symfony\\Component\\Serializer\\Normalizer\\ObjectNormalizer`
    This normalizer leverages the :doc:`PropertyAccess Component </components/property_access>`
    to read and write in the object. It means that it can access to properties
    directly and through getters, setters, hassers, issers, canners, adders and removers.
    It supports calling the constructor during the denormalization process.

    Objects are normalized to a map of property names and values (names are
    generated by removing the ``get``, ``set``, ``has``, ``is``, ``can``, ``add`` or ``remove``
    prefix from the method name and transforming the first letter to lowercase; e.g.
    ``getFirstName()`` -> ``firstName``).

    The ``ObjectNormalizer`` is the most powerful normalizer. It is configured by
    default in Symfony applications with the Serializer component enabled.

:class:`Symfony\\Component\\Serializer\\Normalizer\\GetSetMethodNormalizer`
    This normalizer reads the content of the class by calling the "getters"
    (public methods starting with "get"). It will denormalize data by calling
    the constructor and the "setters" (public methods starting with "set").

    Objects are normalized to a map of property names and values (names are
    generated by removing the ``get`` prefix from the method name and transforming
    the first letter to lowercase; e.g. ``getFirstName()`` -> ``firstName``).

:class:`Symfony\\Component\\Serializer\\Normalizer\\PropertyNormalizer`
    This normalizer directly reads and writes public properties as well as
    **private and protected** properties (from both the class and all of its
    parent classes) by using `PHP reflection`_. It supports calling the constructor
    during the denormalization process.

    Objects are normalized to a map of property names to property values.

    If you prefer to only normalize certain properties (e.g. only public properties)
    set the ``PropertyNormalizer::NORMALIZE_VISIBILITY`` context option and
    combine the following values: ``PropertyNormalizer::NORMALIZE_PUBLIC``,
    ``PropertyNormalizer::NORMALIZE_PROTECTED`` or ``PropertyNormalizer::NORMALIZE_PRIVATE``.

    .. versionadded:: 6.2

        The ``PropertyNormalizer::NORMALIZE_VISIBILITY`` context option and its
        values were introduced in Symfony 6.2.

:class:`Symfony\\Component\\Serializer\\Normalizer\\JsonSerializableNormalizer`
    This normalizer works with classes that implement :phpclass:`JsonSerializable`.

    It will call the :phpmethod:`JsonSerializable::jsonSerialize` method and
    then further normalize the result. This means that nested
    :phpclass:`JsonSerializable` classes will also be normalized.

    This normalizer is particularly helpful when you want to gradually migrate
    from an existing codebase using simple :phpfunction:`json_encode` to the Symfony
    Serializer by allowing you to mix which normalizers are used for which classes.

    Unlike with :phpfunction:`json_encode` circular references can be handled.

:class:`Symfony\\Component\\Serializer\\Normalizer\\DateTimeNormalizer`
    This normalizer converts :phpclass:`DateTimeInterface` objects (e.g.
    :phpclass:`DateTime` and :phpclass:`DateTimeImmutable`) into strings.
    By default, it uses the `RFC3339`_ format.

:class:`Symfony\\Component\\Serializer\\Normalizer\\DateTimeZoneNormalizer`
    This normalizer converts :phpclass:`DateTimeZone` objects into strings that
    represent the name of the timezone according to the `list of PHP timezones`_.

:class:`Symfony\\Component\\Serializer\\Normalizer\\DataUriNormalizer`
    This normalizer converts :phpclass:`SplFileInfo` objects into a `data URI`_
    string (``data:...``) such that files can be embedded into serialized data.

:class:`Symfony\\Component\\Serializer\\Normalizer\\DateIntervalNormalizer`
    This normalizer converts :phpclass:`DateInterval` objects into strings.
    By default, it uses the ``P%yY%mM%dDT%hH%iM%sS`` format.

:class:`Symfony\\Component\\Serializer\\Normalizer\\BackedEnumNormalizer`
    This normalizer converts a \BackedEnum objects into strings or integers.

:class:`Symfony\\Component\\Serializer\\Normalizer\\FormErrorNormalizer`
    This normalizer works with classes that implement
    :class:`Symfony\\Component\\Form\\FormInterface`.

    It will get errors from the form and normalize them into a normalized array.

:class:`Symfony\\Component\\Serializer\\Normalizer\\ConstraintViolationListNormalizer`
    This normalizer converts objects that implement
    :class:`Symfony\\Component\\Validator\\ConstraintViolationListInterface`
    into a list of errors according to the `RFC 7807`_ standard.

:class:`Symfony\\Component\\Serializer\\Normalizer\\ProblemNormalizer`
    Normalizes errors according to the API Problem spec `RFC 7807`_.

:class:`Symfony\\Component\\Serializer\\Normalizer\\CustomNormalizer`
    Normalizes a PHP object using an object that implements :class:`Symfony\\Component\\Serializer\\Normalizer\\NormalizableInterface`.

:class:`Symfony\\Component\\Serializer\\Normalizer\\UidNormalizer`
    This normalizer converts objects that implement
    :class:`Symfony\\Component\\Uid\\AbstractUid` into strings.
    The default normalization format for objects that implement :class:`Symfony\\Component\\Uid\\Uuid`
    is the `RFC 4122`_ format (example: ``d9e7a184-5d5b-11ea-a62a-3499710062d0``).
    The default normalization format for objects that implement :class:`Symfony\\Component\\Uid\\Ulid`
    is the Base 32 format (example: ``01E439TP9XJZ9RPFH3T1PYBCR8``).
    You can change the string format by setting the serializer context option
    ``UidNormalizer::NORMALIZATION_FORMAT_KEY`` to ``UidNormalizer::NORMALIZATION_FORMAT_BASE_58``,
    ``UidNormalizer::NORMALIZATION_FORMAT_BASE_32`` or ``UidNormalizer::NORMALIZATION_FORMAT_RFC_4122``.

    Also it can denormalize ``uuid`` or ``ulid`` strings to :class:`Symfony\\Component\\Uid\\Uuid`
    or :class:`Symfony\\Component\\Uid\\Ulid`. The format does not matter.

.. note::

    You can also create your own Normalizer to use another structure. Read more at
    :doc:`/serializer/custom_normalizer`.

Certain normalizers are enabled by default when using the Serializer component
in a Symfony application, additional ones can be enabled by tagging them with
:ref:`serializer.normalizer <reference-dic-tags-serializer-normalizer>`.

Here is an example of how to enable the built-in
:class:`Symfony\\Component\\Serializer\\Normalizer\\GetSetMethodNormalizer`, a
faster alternative to the
:class:`Symfony\\Component\\Serializer\\Normalizer\\ObjectNormalizer`:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            get_set_method_normalizer:
                class: Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer
                tags: [serializer.normalizer]

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd"
        >
            <services>
                <!-- ... -->

                <service id="get_set_method_normalizer" class="Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer">
                    <tag name="serializer.normalizer"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

        return static function (ContainerConfigurator $containerConfigurator) {
            $containerConfigurator->services()
                // ...
                ->set('get_set_method_normalizer', GetSetMethodNormalizer::class)
                    ->tag('serializer.normalizer')
            ;
        };

.. _component-serializer-encoders:

Encoders
--------

Encoders turn **arrays** into **formats** and vice versa. They implement
:class:`Symfony\\Component\\Serializer\\Encoder\\EncoderInterface`
for encoding (array to format) and
:class:`Symfony\\Component\\Serializer\\Encoder\\DecoderInterface` for decoding
(format to array).

You can add new encoders to a Serializer instance by using its second constructor argument::

    use Symfony\Component\Serializer\Encoder\JsonEncoder;
    use Symfony\Component\Serializer\Encoder\XmlEncoder;
    use Symfony\Component\Serializer\Serializer;

    $encoders = [new XmlEncoder(), new JsonEncoder()];
    $serializer = new Serializer([], $encoders);

Built-in Encoders
~~~~~~~~~~~~~~~~~

The Serializer component provides several built-in encoders:

:class:`Symfony\\Component\\Serializer\\Encoder\\JsonEncoder`
    This class encodes and decodes data in `JSON`_.

:class:`Symfony\\Component\\Serializer\\Encoder\\XmlEncoder`
    This class encodes and decodes data in `XML`_.

:class:`Symfony\\Component\\Serializer\\Encoder\\YamlEncoder`
    This encoder encodes and decodes data in `YAML`_. This encoder requires the
    :doc:`Yaml Component </components/yaml>`.

:class:`Symfony\\Component\\Serializer\\Encoder\\CsvEncoder`
    This encoder encodes and decodes data in `CSV`_.

.. note::

    You can also create your own Encoder to use another structure. Read more at
    :doc:`/serializer/custom_encoders`.

All these encoders are enabled by default when using the Serializer component
in a Symfony application.

The ``JsonEncoder``
~~~~~~~~~~~~~~~~~~~

The ``JsonEncoder`` encodes to and decodes from JSON strings, based on the PHP
:phpfunction:`json_encode` and :phpfunction:`json_decode` functions. It can be
useful to modify how these functions operate in certain instances by providing
options such as ``JSON_PRESERVE_ZERO_FRACTION``. You can use the serialization
context to pass in these options using the key ``json_encode_options`` or
``json_decode_options`` respectively::

    $this->serializer->serialize($data, 'json', ['json_encode_options' => \JSON_PRESERVE_ZERO_FRACTION]);

The ``CsvEncoder``
~~~~~~~~~~~~~~~~~~~

The ``CsvEncoder`` encodes to and decodes from CSV.

The ``CsvEncoder`` Context Options
..................................

The ``encode()`` method defines a third optional parameter called ``context``
which defines the configuration options for the CsvEncoder an associative array::

    $csvEncoder->encode($array, 'csv', $context);

These are the options available:

======================= =====================================================  ==========================
Option                  Description                                            Default
======================= =====================================================  ==========================
``csv_delimiter``       Sets the field delimiter separating values (one        ``,``
                        character only)
``csv_enclosure``       Sets the field enclosure (one character only)          ``"``
``csv_end_of_line``     Sets the character(s) used to mark the end of each     ``\n``
                        line in the CSV file
``csv_escape_char``     Sets the escape character (at most one character)      empty string
``csv_key_separator``   Sets the separator for array's keys during its         ``.``
                        flattening
``csv_headers``         Sets the order of the header and data columns
                        E.g.: if ``$data = ['c' => 3, 'a' => 1, 'b' => 2]``
                        and ``$options = ['csv_headers' => ['a', 'b', 'c']]``
                        then ``serialize($data, 'csv', $options)`` returns
                        ``a,b,c\n1,2,3``                                       ``[]``, inferred from input data's keys
``csv_escape_formulas`` Escapes fields containing formulas by prepending them  ``false``
                        with a ``\t`` character
``as_collection``       Always returns results as a collection, even if only   ``true``
                        one line is decoded.
``no_headers``          Disables header in the encoded CSV                     ``false``
``output_utf8_bom``     Outputs special `UTF-8 BOM`_ along with encoded data   ``false``
======================= =====================================================  ==========================

The ``XmlEncoder``
~~~~~~~~~~~~~~~~~~

This encoder transforms arrays into XML and vice versa.

For example, take an object normalized as following::

    ['foo' => [1, 2], 'bar' => true];

The ``XmlEncoder`` will encode this object like that:

.. code-block:: xml

    <?xml version="1.0" encoding="UTF-8" ?>
    <response>
        <foo>1</foo>
        <foo>2</foo>
        <bar>1</bar>
    </response>

The special ``#`` key can be used to define the data of a node::

    ['foo' => ['@bar' => 'value', '#' => 'baz']];

    // is encoded as follows:
    // <?xml version="1.0"?>
    // <response>
    //     <foo bar="value">
    //        baz
    //     </foo>
    // </response>

Furthermore, keys beginning with ``@`` will be considered attributes, and
the key  ``#comment`` can be used for encoding XML comments::

    $encoder = new XmlEncoder();
    $encoder->encode([
        'foo' => ['@bar' => 'value'],
        'qux' => ['#comment' => 'A comment'],
    ], 'xml');
    // will return:
    // <?xml version="1.0"?>
    // <response>
    //     <foo bar="value"/>
    //     <qux><!-- A comment --!><qux>
    // </response>

You can pass the context key ``as_collection`` in order to have the results
always as a collection.

.. tip::

    XML comments are ignored by default when decoding contents, but this
    behavior can be changed with the optional context key ``XmlEncoder::DECODER_IGNORED_NODE_TYPES``.

    Data with ``#comment`` keys are encoded to XML comments by default. This can be
    changed by adding the ``\XML_COMMENT_NODE`` option to the ``XmlEncoder::ENCODER_IGNORED_NODE_TYPES``
    key of the ``$defaultContext`` of the ``XmlEncoder`` constructor or
    directly to the ``$context`` argument of the ``encode()`` method::

        $xmlEncoder->encode($array, 'xml', [XmlEncoder::ENCODER_IGNORED_NODE_TYPES => [\XML_COMMENT_NODE]]);

The ``XmlEncoder`` Context Options
..................................

The ``encode()`` method defines a third optional parameter called ``context``
which defines the configuration options for the XmlEncoder an associative array::

    $xmlEncoder->encode($array, 'xml', $context);

These are the options available:

==============================  =================================================  ==========================
Option                          Description                                        Default
==============================  =================================================  ==========================
``xml_format_output``           If set to true, formats the generated XML with     ``false``
                                line breaks and indentation
``xml_version``                 Sets the XML version attribute                     ``1.0``
``xml_encoding``                Sets the XML encoding attribute                    ``utf-8``
``xml_standalone``              Adds standalone attribute in the generated XML     ``true``
``xml_type_cast_attributes``    This provides the ability to forget the attribute  ``true``
                                type casting
``xml_root_node_name``          Sets the root node name                            ``response``
``as_collection``               Always returns results as a collection, even if    ``false``
                                only one line is decoded
``decoder_ignored_node_types``  Array of node types (`DOM XML_* constants`_)       ``[\XML_PI_NODE, \XML_COMMENT_NODE]``
                                to be ignored while decoding
``encoder_ignored_node_types``  Array of node types (`DOM XML_* constants`_)       ``[]``
                                to be ignored while encoding
``load_options``                XML loading `options with libxml`_                 ``\LIBXML_NONET | \LIBXML_NOBLANKS``
``save_options``                XML saving `options with libxml`_                  ``0``
``remove_empty_tags``           If set to true, removes all empty tags in the      ``false``
                                generated XML
==============================  =================================================  ==========================

Example with custom ``context``::

    use Symfony\Component\Serializer\Encoder\XmlEncoder;

    // create encoder with specified options as new default settings
    $xmlEncoder = new XmlEncoder(['xml_format_output' => true]);

    $data = [
        'id' => 'IDHNQIItNyQ',
        'date' => '2019-10-24',
    ];

    // encode with default context
    $xmlEncoder->encode($data, 'xml');
    // outputs:
    // <?xml version="1.0"?>
    // <response>
    //   <id>IDHNQIItNyQ</id>
    //   <date>2019-10-24</date>
    // </response>

    // encode with modified context
    $xmlEncoder->encode($data, 'xml', [
        'xml_root_node_name' => 'track',
        'encoder_ignored_node_types' => [
            \XML_PI_NODE, // removes XML declaration (the leading xml tag)
        ],
    ]);
    // outputs:
    // <track>
    //   <id>IDHNQIItNyQ</id>
    //   <date>2019-10-24</date>
    // </track>

The ``YamlEncoder``
~~~~~~~~~~~~~~~~~~~

This encoder requires the :doc:`Yaml Component </components/yaml>` and
transforms from and to Yaml.

The ``YamlEncoder`` Context Options
...................................

The ``encode()`` method, like other encoder, uses ``context`` to set
configuration options for the YamlEncoder an associative array::

    $yamlEncoder->encode($array, 'yaml', $context);

These are the options available:

=============== ========================================================  ==========================
Option          Description                                               Default
=============== ========================================================  ==========================
``yaml_inline`` The level where you switch to inline YAML                 ``0``
``yaml_indent`` The level of indentation (used internally)                ``0``
``yaml_flags``  A bit field of ``Yaml::DUMP_*`` / ``PARSE_*`` constants   ``0``
                to customize the encoding / decoding YAML string
=============== ========================================================  ==========================

.. _component-serializer-context-builders:

Context Builders
----------------

Instead of passing plain PHP arrays to the :ref:`serialization context <serializer-context>`,
you can use "context builders" to define the context using a fluent interface::

    use Symfony\Component\Serializer\Context\Encoder\CsvEncoderContextBuilder;
    use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;

    $initialContext = [
        'custom_key' => 'custom_value',
    ];

    $contextBuilder = (new ObjectNormalizerContextBuilder())
        ->withContext($initialContext)
        ->withGroups(['group1', 'group2']);

    $contextBuilder = (new CsvEncoderContextBuilder())
        ->withContext($contextBuilder)
        ->withDelimiter(';');

    $serializer->serialize($something, 'csv', $contextBuilder->toArray());

.. versionadded:: 6.1

    Context builders were introduced in Symfony 6.1.

.. note::

    The Serializer component provides a context builder
    for each :ref:`normalizer <component-serializer-normalizers>`
    and :ref:`encoder <component-serializer-encoders>`.

    You can also :doc:`create custom context builders </serializer/custom_context_builders>`
    to deal with your context values.

Skipping ``null`` Values
------------------------

By default, the Serializer will preserve properties containing a ``null`` value.
You can change this behavior by setting the ``AbstractObjectNormalizer::SKIP_NULL_VALUES`` context option
to ``true``::

    $dummy = new class {
        public $foo;
        public $bar = 'notNull';
    };

    $normalizer = new ObjectNormalizer();
    $result = $normalizer->normalize($dummy, 'json', [AbstractObjectNormalizer::SKIP_NULL_VALUES => true]);
    // ['bar' => 'notNull']

Require all Properties
----------------------

By default, the Serializer will add ``null`` to nullable properties when the parameters for those are not provided.
You can change this behavior by setting the ``AbstractNormalizer::REQUIRE_ALL_PROPERTIES`` context option
to ``true``::

    class Dummy
    {
        public function __construct(
            public string $foo,
            public ?string $bar,
        ) {
        }
    }

    $data = ['foo' => 'notNull'];

    $normalizer = new ObjectNormalizer();
    $result = $normalizer->denormalize($data, Dummy::class, 'json', [AbstractNormalizer::REQUIRE_ALL_PROPERTIES => true]);
    // throws Symfony\Component\Serializer\Exception\MissingConstructorArgumentException

.. versionadded:: 6.3

    The ``AbstractNormalizer::PREVENT_NULLABLE_FALLBACK`` context option
    was introduced in Symfony 6.3.

Skipping Uninitialized Properties
---------------------------------

In PHP, typed properties have an ``uninitialized`` state which is different
from the default ``null`` of untyped properties. When you try to access a typed
property before giving it an explicit value, you get an error.

To avoid the Serializer throwing an error when serializing or normalizing an
object with uninitialized properties, by default the object normalizer catches
these errors and ignores such properties.

You can disable this behavior by setting the ``AbstractObjectNormalizer::SKIP_UNINITIALIZED_VALUES``
context option to ``false``::

    class Dummy {
        public string $foo = 'initialized';
        public string $bar; // uninitialized
    }

    $normalizer = new ObjectNormalizer();
    $result = $normalizer->normalize(new Dummy(), 'json', [AbstractObjectNormalizer::SKIP_UNINITIALIZED_VALUES => false]);
    // throws Symfony\Component\PropertyAccess\Exception\UninitializedPropertyException as normalizer cannot read uninitialized properties

.. note::

    Calling ``PropertyNormalizer::normalize`` or ``GetSetMethodNormalizer::normalize``
    with ``AbstractObjectNormalizer::SKIP_UNINITIALIZED_VALUES`` context option set
    to ``false`` will throw an ``\Error`` instance if the given object has uninitialized
    properties as the normalizer cannot read them (directly or via getter/isser methods).

.. _component-serializer-handling-circular-references:

Collecting Type Errors While Denormalizing
------------------------------------------

When denormalizing a payload to an object with typed properties, you'll get an
exception if the payload contains properties that don't have the same type as
the object.

In those situations, use the ``COLLECT_DENORMALIZATION_ERRORS`` option to
collect all exceptions at once, and to get the object partially denormalized::

    try {
        $dto = $serializer->deserialize($request->getContent(), MyDto::class, 'json', [
            DenormalizerInterface::COLLECT_DENORMALIZATION_ERRORS => true,
        ]);
    } catch (PartialDenormalizationException $e) {
        $violations = new ConstraintViolationList();
        /** @var NotNormalizableValueException $exception */
        foreach ($e->getErrors() as $exception) {
            $message = sprintf('The type must be one of "%s" ("%s" given).', implode(', ', $exception->getExpectedTypes()), $exception->getCurrentType());
            $parameters = [];
            if ($exception->canUseMessageForUser()) {
                $parameters['hint'] = $exception->getMessage();
            }
            $violations->add(new ConstraintViolation($message, '', $parameters, null, $exception->getPath(), null));
        }

        return $this->json($violations, 400);
    }

Handling Circular References
----------------------------

Circular references are common when dealing with entity relations::

    class Organization
    {
        private $name;
        private $members;

        public function setName($name)
        {
            $this->name = $name;
        }

        public function getName()
        {
            return $this->name;
        }

        public function setMembers(array $members)
        {
            $this->members = $members;
        }

        public function getMembers()
        {
            return $this->members;
        }
    }

    class Member
    {
        private $name;
        private $organization;

        public function setName($name)
        {
            $this->name = $name;
        }

        public function getName()
        {
            return $this->name;
        }

        public function setOrganization(Organization $organization)
        {
            $this->organization = $organization;
        }

        public function getOrganization()
        {
            return $this->organization;
        }
    }

To avoid infinite loops, :class:`Symfony\\Component\\Serializer\\Normalizer\\GetSetMethodNormalizer`
or :class:`Symfony\\Component\\Serializer\\Normalizer\\ObjectNormalizer`
throw a :class:`Symfony\\Component\\Serializer\\Exception\\CircularReferenceException`
when such a case is encountered::

    $member = new Member();
    $member->setName('Kévin');

    $organization = new Organization();
    $organization->setName('Les-Tilleuls.coop');
    $organization->setMembers([$member]);

    $member->setOrganization($organization);

    echo $serializer->serialize($organization, 'json'); // Throws a CircularReferenceException

The key ``circular_reference_limit`` in the default context sets the number of
times it will serialize the same object before considering it a circular
reference. The default value is ``1``.

Instead of throwing an exception, circular references can also be handled
by custom callables. This is especially useful when serializing entities
having unique identifiers::

    $encoder = new JsonEncoder();
    $defaultContext = [
        AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
            return $object->getName();
        },
    ];
    $normalizer = new ObjectNormalizer(null, null, null, null, null, null, $defaultContext);

    $serializer = new Serializer([$normalizer], [$encoder]);
    var_dump($serializer->serialize($org, 'json'));
    // {"name":"Les-Tilleuls.coop","members":[{"name":"K\u00e9vin", organization: "Les-Tilleuls.coop"}]}

Handling Serialization Depth
----------------------------

The Serializer component is able to detect and limit the serialization depth.
It is especially useful when serializing large trees. Assume the following data
structure::

    namespace Acme;

    class MyObj
    {
        public $foo;

        /**
         * @var self
         */
        public $child;
    }

    $level1 = new MyObj();
    $level1->foo = 'level1';

    $level2 = new MyObj();
    $level2->foo = 'level2';
    $level1->child = $level2;

    $level3 = new MyObj();
    $level3->foo = 'level3';
    $level2->child = $level3;

The serializer can be configured to set a maximum depth for a given property.
Here, we set it to 2 for the ``$child`` property:

.. configuration-block::

    .. code-block:: php-attributes

        namespace Acme;

        use Symfony\Component\Serializer\Annotation\MaxDepth;

        class MyObj
        {
            #[MaxDepth(2)]
            public $child;

            // ...
        }

    .. code-block:: yaml

        Acme\MyObj:
            attributes:
                child:
                    max_depth: 2

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <serializer xmlns="http://symfony.com/schema/dic/serializer-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/serializer-mapping
                https://symfony.com/schema/dic/serializer-mapping/serializer-mapping-1.0.xsd"
        >
            <class name="Acme\MyObj">
                <attribute name="child" max-depth="2"/>
            </class>
        </serializer>

The metadata loader corresponding to the chosen format must be configured in
order to use this feature. It is done automatically when using the Serializer component
in a Symfony application. When using the standalone component, refer to
:ref:`the groups documentation <component-serializer-attributes-groups>` to
learn how to do that.

The check is only done if the ``AbstractObjectNormalizer::ENABLE_MAX_DEPTH`` key of the serializer context
is set to ``true``. In the following example, the third level is not serialized
because it is deeper than the configured maximum depth of 2::

    $result = $serializer->normalize($level1, null, [AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true]);
    /*
    $result = [
        'foo' => 'level1',
        'child' => [
            'foo' => 'level2',
            'child' => [
                'child' => null,
            ],
        ],
    ];
    */

Instead of throwing an exception, a custom callable can be executed when the
maximum depth is reached. This is especially useful when serializing entities
having unique identifiers::

    use Doctrine\Common\Annotations\AnnotationReader;
    use Symfony\Component\Serializer\Annotation\MaxDepth;
    use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
    use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
    use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
    use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
    use Symfony\Component\Serializer\Serializer;

    class Foo
    {
        public $id;

        #[MaxDepth(1)]
        public $child;
    }

    $level1 = new Foo();
    $level1->id = 1;

    $level2 = new Foo();
    $level2->id = 2;
    $level1->child = $level2;

    $level3 = new Foo();
    $level3->id = 3;
    $level2->child = $level3;

    $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));

    // all callback parameters are optional (you can omit the ones you don't use)
    $maxDepthHandler = function ($innerObject, $outerObject, string $attributeName, string $format = null, array $context = []) {
        return '/foos/'.$innerObject->id;
    };

    $defaultContext = [
        AbstractObjectNormalizer::MAX_DEPTH_HANDLER => $maxDepthHandler,
    ];
    $normalizer = new ObjectNormalizer($classMetadataFactory, null, null, null, null, null, $defaultContext);

    $serializer = new Serializer([$normalizer]);

    $result = $serializer->normalize($level1, null, [AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true]);
    /*
    $result = [
        'id' => 1,
        'child' => [
            'id' => 2,
            'child' => '/foos/3',
        ],
    ];
    */

Handling Arrays
---------------

The Serializer component is capable of handling arrays of objects as well.
Serializing arrays works just like serializing a single object::

    use Acme\Person;

    $person1 = new Person();
    $person1->setName('foo');
    $person1->setAge(99);
    $person1->setSportsman(false);

    $person2 = new Person();
    $person2->setName('bar');
    $person2->setAge(33);
    $person2->setSportsman(true);

    $persons = [$person1, $person2];
    $data = $serializer->serialize($persons, 'json');

    // $data contains [{"name":"foo","age":99,"sportsman":false},{"name":"bar","age":33,"sportsman":true}]

If you want to deserialize such a structure, you need to add the
:class:`Symfony\\Component\\Serializer\\Normalizer\\ArrayDenormalizer`
to the set of normalizers. By appending ``[]`` to the type parameter of the
:method:`Symfony\\Component\\Serializer\\Serializer::deserialize` method,
you indicate that you're expecting an array instead of a single object::

    use Symfony\Component\Serializer\Encoder\JsonEncoder;
    use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
    use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
    use Symfony\Component\Serializer\Serializer;

    $serializer = new Serializer(
        [new GetSetMethodNormalizer(), new ArrayDenormalizer()],
        [new JsonEncoder()]
    );

    $data = ...; // The serialized data from the previous example
    $persons = $serializer->deserialize($data, 'Acme\Person[]', 'json');

Handling Constructor Arguments
------------------------------

If the class constructor defines arguments, as usually happens with
`Value Objects`_, the serializer won't be able to create the object if some
arguments are missing. In those cases, use the ``default_constructor_arguments``
context option::

    use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
    use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
    use Symfony\Component\Serializer\Serializer;

    class MyObj
    {
        private $foo;
        private $bar;

        public function __construct($foo, $bar)
        {
            $this->foo = $foo;
            $this->bar = $bar;
        }
    }

    $normalizer = new ObjectNormalizer($classMetadataFactory);
    $serializer = new Serializer([$normalizer]);

    $data = $serializer->denormalize(
        ['foo' => 'Hello'],
        'MyObj',
        null,
        [AbstractNormalizer::DEFAULT_CONSTRUCTOR_ARGUMENTS => [
            'MyObj' => ['foo' => '', 'bar' => ''],
        ]]
    );
    // $data = new MyObj('Hello', '');

Recursive Denormalization and Type Safety
-----------------------------------------

The Serializer component can use the :doc:`PropertyInfo Component </components/property_info>` to denormalize
complex types (objects). The type of the class' property will be guessed using the provided
extractor and used to recursively denormalize the inner data.

When using this component in a Symfony application, all normalizers are automatically configured to use the registered extractors.
When using the component standalone, an implementation of :class:`Symfony\\Component\\PropertyInfo\\PropertyTypeExtractorInterface`,
(usually an instance of :class:`Symfony\\Component\\PropertyInfo\\PropertyInfoExtractor`) must be passed as the 4th
parameter of the ``ObjectNormalizer``::

    namespace Acme;

    use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
    use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
    use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
    use Symfony\Component\Serializer\Serializer;

    class ObjectOuter
    {
        private $inner;
        private $date;

        public function getInner()
        {
            return $this->inner;
        }

        public function setInner(ObjectInner $inner)
        {
            $this->inner = $inner;
        }

        public function setDate(\DateTimeInterface $date)
        {
            $this->date = $date;
        }

        public function getDate()
        {
            return $this->date;
        }
    }

    class ObjectInner
    {
        public $foo;
        public $bar;
    }

    $normalizer = new ObjectNormalizer(null, null, null, new ReflectionExtractor());
    $serializer = new Serializer([new DateTimeNormalizer(), $normalizer]);

    $obj = $serializer->denormalize(
        ['inner' => ['foo' => 'foo', 'bar' => 'bar'], 'date' => '1988/01/21'],
        'Acme\ObjectOuter'
    );

    dump($obj->getInner()->foo); // 'foo'
    dump($obj->getInner()->bar); // 'bar'
    dump($obj->getDate()->format('Y-m-d')); // '1988-01-21'

When a ``PropertyTypeExtractor`` is available, the normalizer will also check that the data to denormalize
matches the type of the property (even for primitive types). For instance, if a ``string`` is provided, but
the type of the property is ``int``, an :class:`Symfony\\Component\\Serializer\\Exception\\UnexpectedValueException`
will be thrown. The type enforcement of the properties can be disabled by setting
the serializer context option ``ObjectNormalizer::DISABLE_TYPE_ENFORCEMENT``
to ``true``.

Serializing Interfaces and Abstract Classes
-------------------------------------------

When dealing with objects that are fairly similar or share properties, you may
use interfaces or abstract classes. The Serializer component allows you to
serialize and deserialize these objects using a *"discriminator class mapping"*.

The discriminator is the field (in the serialized string) used to differentiate
between the possible objects. In practice, when using the Serializer component,
pass a :class:`Symfony\\Component\\Serializer\\Mapping\\ClassDiscriminatorResolverInterface`
implementation to the :class:`Symfony\\Component\\Serializer\\Normalizer\\ObjectNormalizer`.

The Serializer component provides an implementation of ``ClassDiscriminatorResolverInterface``
called :class:`Symfony\\Component\\Serializer\\Mapping\\ClassDiscriminatorFromClassMetadata`
which uses the class metadata factory and a mapping configuration to serialize
and deserialize objects of the correct class.

When using this component inside a Symfony application and the class metadata factory is enabled
as explained in the :ref:`Attributes Groups section <component-serializer-attributes-groups>`,
this is already set up and you only need to provide the configuration. Otherwise::

    // ...
    use Symfony\Component\Serializer\Encoder\JsonEncoder;
    use Symfony\Component\Serializer\Mapping\ClassDiscriminatorFromClassMetadata;
    use Symfony\Component\Serializer\Mapping\ClassDiscriminatorMapping;
    use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
    use Symfony\Component\Serializer\Serializer;

    $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));

    $discriminator = new ClassDiscriminatorFromClassMetadata($classMetadataFactory);

    $serializer = new Serializer(
        [new ObjectNormalizer($classMetadataFactory, null, null, null, $discriminator)],
        ['json' => new JsonEncoder()]
    );

Now configure your discriminator class mapping. Consider an application that
defines an abstract ``CodeRepository`` class extended by ``GitHubCodeRepository``
and ``BitBucketCodeRepository`` classes:

.. configuration-block::

    .. code-block:: php-attributes

        namespace App;

        use App\BitBucketCodeRepository;
        use App\GitHubCodeRepository;
        use Symfony\Component\Serializer\Annotation\DiscriminatorMap;

        #[DiscriminatorMap(typeProperty: 'type', mapping: [
            'github' => GitHubCodeRepository::class,
            'bitbucket' => BitBucketCodeRepository::class,
        ])]
        abstract class CodeRepository
        {
            // ...
        }

    .. code-block:: yaml

        App\CodeRepository:
            discriminator_map:
                type_property: type
                mapping:
                    github: 'App\GitHubCodeRepository'
                    bitbucket: 'App\BitBucketCodeRepository'

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <serializer xmlns="http://symfony.com/schema/dic/serializer-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/serializer-mapping
                https://symfony.com/schema/dic/serializer-mapping/serializer-mapping-1.0.xsd"
        >
            <class name="App\CodeRepository">
                <discriminator-map type-property="type">
                    <mapping type="github" class="App\GitHubCodeRepository"/>
                    <mapping type="bitbucket" class="App\BitBucketCodeRepository"/>
                </discriminator-map>
            </class>
        </serializer>

Once configured, the serializer uses the mapping to pick the correct class::

    $serialized = $serializer->serialize(new GitHubCodeRepository(), 'json');
    // {"type": "github"}

    $repository = $serializer->deserialize($serialized, CodeRepository::class, 'json');
    // instanceof GitHubCodeRepository

Learn more
----------

.. toctree::
    :maxdepth: 1
    :glob:

    /serializer

.. seealso::

    Normalizers for the Symfony Serializer Component supporting popular web API formats
    (JSON-LD, GraphQL, OpenAPI, HAL, JSON:API) are available as part of the `API Platform`_ project.

.. seealso::

    A popular alternative to the Symfony Serializer component is the third-party
    library, `JMS serializer`_ (versions before ``v1.12.0`` were released under
    the Apache license, so incompatible with GPLv2 projects).

.. _`PSR-1 standard`: https://www.php-fig.org/psr/psr-1/
.. _`JMS serializer`: https://github.com/schmittjoh/serializer
.. _RFC3339: https://tools.ietf.org/html/rfc3339#section-5.8
.. _`options with libxml`: https://www.php.net/manual/en/libxml.constants.php
.. _`DOM XML_* constants`: https://www.php.net/manual/en/dom.constants.php
.. _JSON: http://www.json.org/
.. _XML: https://www.w3.org/XML/
.. _YAML: https://yaml.org/
.. _CSV: https://tools.ietf.org/html/rfc4180
.. _`RFC 7807`: https://tools.ietf.org/html/rfc7807
.. _`UTF-8 BOM`: https://en.wikipedia.org/wiki/Byte_order_mark
.. _`Value Objects`: https://en.wikipedia.org/wiki/Value_object
.. _`API Platform`: https://api-platform.com
.. _`list of PHP timezones`: https://www.php.net/manual/en/timezones.php
.. _`RFC 4122`: https://tools.ietf.org/html/rfc4122
.. _`PHP reflection`: https://php.net/manual/en/book.reflection.php
.. _`data URI`: https://developer.mozilla.org/en-US/docs/Web/HTTP/Basics_of_HTTP/Data_URIs

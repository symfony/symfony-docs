.. index::
   single: Serializer
   single: Components; Serializer

The Serializer Component
========================

    The Serializer component is meant to be used to turn objects into a
    specific format (XML, JSON, YAML, ...) and the other way around.

In order to do so, the Serializer component follows the following
simple schema.

.. image:: /_images/components/serializer/serializer_workflow.png

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

Using the Serializer component is really simple. You just need to set up
the :class:`Symfony\\Component\\Serializer\\Serializer` specifying
which encoders and normalizer are going to be available::


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
        private $age;
        private $name;
        private $sportsperson;
        private $createdAt;

        // Getters
        public function getName()
        {
            return $this->name;
        }

        public function getAge()
        {
            return $this->age;
        }

        public function getCreatedAt()
        {
            return $this->createdAt;
        }

        // Issers
        public function isSportsperson()
        {
            return $this->sportsperson;
        }

        // Setters
        public function setName($name)
        {
            $this->name = $name;
        }

        public function setAge($age)
        {
            $this->age = $age;
        }

        public function setSportsperson($sportsperson)
        {
            $this->sportsperson = $sportsperson;
        }

        public function setCreatedAt($createdAt)
        {
            $this->createdAt = $createdAt;
        }
    }

Now, if you want to serialize this object into JSON, you only need to
use the Serializer service created before::

    $person = new App\Model\Person();
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

.. versionadded:: 3.3

    Support for the ``allow_extra_attributes`` key in the context was introduced
    in Symfony 3.3.

By default, additional attributes that are not mapped to the denormalized object
will be ignored by the Serializer component. If you prefer to throw an exception
when this happens, set the ``allow_extra_attributes`` context option to
``false`` and provide an object that implements ``ClassMetadataFactoryInterface``
when constructing the normalizer::

    $data = <<<EOF
    <person>
        <name>foo</name>
        <age>99</age>
        <city>Paris</city>
    </person>
    EOF;

    // this will throw a Symfony\Component\Serializer\Exception\ExtraAttributesException
    // because "city" is not an attribute of the Person class
    $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
    $normalizer = new ObjectNormalizer($classMetadataFactory);
    $serializer = new Serializer([$normalizer]);
    $person = $serializer->deserialize($data, 'App\Model\Person', 'xml', [
        'allow_extra_attributes' => false,
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

    $serializer->deserialize($data, Person::class, 'xml', ['object_to_populate' => $person]);
    // $person = App\Model\Person(name: 'foo', age: '69', sportsperson: true)

This is a common need when working with an ORM.

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

    $classMetadataFactory = new ClassMetadataFactory(new YamlFileLoader('/path/to/your/definition.yml'));

* XML files::

    use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
    use Symfony\Component\Serializer\Mapping\Loader\XmlFileLoader;

    $classMetadataFactory = new ClassMetadataFactory(new XmlFileLoader('/path/to/your/definition.xml'));

.. _component-serializer-attributes-groups-annotations:

Then, create your groups definition:

.. configuration-block::

    .. code-block:: php-annotations

        namespace Acme;

        use Symfony\Component\Serializer\Annotation\Groups;

        class MyObj
        {
            /**
             * @Groups({"group1", "group2"})
             */
            public $foo;

            /**
             * @Groups("group3")
             */
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
                bar:
                    groups: ['group3']

    .. code-block:: xml

        <?xml version="1.0" ?>
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
    $obj->setBar('bar');

    $normalizer = new ObjectNormalizer($classMetadataFactory);
    $serializer = new Serializer([$normalizer]);

    $data = $serializer->normalize($obj, null, ['groups' => ['group1']]);
    // $data = ['foo' => 'foo'];

    $obj2 = $serializer->denormalize(
        ['foo' => 'foo', 'bar' => 'bar'],
        'MyObj',
        null,
        ['groups' => ['group1', 'group3']]
    );
    // $obj2 = MyObj(foo: 'foo', bar: 'bar')

.. include:: /_includes/_annotation_loader_tip.rst.inc

.. _ignoring-attributes-when-serializing:

Selecting Specific Attributes
-----------------------------

It is also possible to serialize only a set of specific attributes::

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

    $data = $serializer->normalize($user, null, ['attributes' => ['familyName', 'company' => ['name']]]);
    // $data = ['familyName' => 'Dunglas', 'company' => ['name' => 'Les-Tilleuls.coop']];

Only attributes that are not ignored (see below) are available.
If some serialization groups are set, only attributes allowed by those groups can be used.

As for groups, attributes can be selected during both the serialization and deserialization process.

Ignoring Attributes
-------------------

.. note::

    Using attribute groups instead of the :method:`Symfony\\Component\\Serializer\\Normalizer\\AbstractNormalizer::setIgnoredAttributes`
    method is considered best practice.

As an option, there's a way to ignore attributes from the origin object. To remove
those attributes use the
:method:`Symfony\\Component\\Serializer\\Normalizer\\AbstractNormalizer::setIgnoredAttributes`
method on the normalizer definition::

    use Symfony\Component\Serializer\Encoder\JsonEncoder;
    use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
    use Symfony\Component\Serializer\Serializer;

    $normalizer = new ObjectNormalizer();
    $normalizer->setIgnoredAttributes(['age']);
    $encoder = new JsonEncoder();

    $serializer = new Serializer([$normalizer], [$encoder]);
    $serializer->serialize($person, 'json'); // Output: {"name":"foo","sportsperson":false}

.. _component-serializer-converting-property-names-when-serializing-and-deserializing:

Converting Property Names when Serializing and Deserializing
------------------------------------------------------------

Sometimes serialized attributes must be named differently than properties
or getter/setter methods of PHP classes.

The Serializer Component provides a handy way to translate or map PHP field
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
        public function normalize($propertyName)
        {
            return 'org_'.$propertyName;
        }

        public function denormalize($propertyName)
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

Serializing Boolean Attributes
------------------------------

If you are using isser methods (methods prefixed by ``is``, like
``App\Model\Person::isSportsperson()``), the Serializer component will
automatically detect and use it to serialize related attributes.

The ``ObjectNormalizer`` also takes care of methods starting with ``has``, ``add``
and ``remove``.

Using Callbacks to Serialize Properties with Object Instances
-------------------------------------------------------------

When serializing, you can set a callback to format a specific object property::

    use App\Model\Person;
    use Symfony\Component\Serializer\Encoder\JsonEncoder;
    use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
    use Symfony\Component\Serializer\Serializer;

    $encoder = new JsonEncoder();
    $normalizer = new GetSetMethodNormalizer();

    $callback = function ($dateTime) {
        return $dateTime instanceof \DateTime
            ? $dateTime->format(\DateTime::ISO8601)
            : '';
    };

    $normalizer->setCallbacks(['createdAt' => $callback]);

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

There are several types of normalizers available:

:class:`Symfony\\Component\\Serializer\\Normalizer\\ObjectNormalizer`
    This normalizer leverages the :doc:`PropertyAccess Component </components/property_access>`
    to read and write in the object. It means that it can access to properties
    directly and through getters, setters, hassers, adders and removers. It supports
    calling the constructor during the denormalization process.

    Objects are normalized to a map of property names and values (names are
    generated removing the ``get``, ``set``, ``has``, ``is`` or ``remove`` prefix from
    the method name and lowercasing the first letter; e.g. ``getFirstName()`` ->
    ``firstName``).

    The ``ObjectNormalizer`` is the most powerful normalizer. It is configured by
    default when using the Symfony Standard Edition with the serializer enabled.

:class:`Symfony\\Component\\Serializer\\Normalizer\\GetSetMethodNormalizer`
    This normalizer reads the content of the class by calling the "getters"
    (public methods starting with "get"). It will denormalize data by calling
    the constructor and the "setters" (public methods starting with "set").

    Objects are normalized to a map of property names and values (names are
    generated removing the ``get`` prefix from the method name and lowercasing
    the first letter; e.g. ``getFirstName()`` -> ``firstName``).

:class:`Symfony\\Component\\Serializer\\Normalizer\\PropertyNormalizer`
    This normalizer directly reads and writes public properties as well as
    **private and protected** properties (from both the class and all of its
    parent classes). It supports calling the constructor during the denormalization process.

    Objects are normalized to a map of property names to property values.

    .. versionadded:: 3.4

        The ability to handle parent classes for ``PropertyNormalizer`` was
        introduced in Symfony 3.4.

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
    By default, it uses the RFC3339_ format.

    .. versionadded:: 3.2

        Support for specifying datetime format during denormalization was
        introduced in the ``DateTimeNormalizer`` in Symfony 3.2.

:class:`Symfony\\Component\\Serializer\\Normalizer\\DataUriNormalizer`
    This normalizer converts :phpclass:`SplFileInfo` objects into a data URI
    string (``data:...``) such that files can be embedded into serialized data.

:class:`Symfony\\Component\\Serializer\\Normalizer\\DateIntervalNormalizer`
    This normalizer converts :phpclass:`DateInterval` objects into strings.
    By default, it uses the ``P%yY%mM%dDT%hH%iM%sS`` format.

    .. versionadded:: 3.4

        The ``DateIntervalNormalizer`` normalizer was introduced in Symfony 3.4.

.. _component-serializer-encoders:

Encoders
--------

The Serializer component supports many formats out of the box:

:class:`Symfony\\Component\\Serializer\\Encoder\\JsonEncoder`
    This class encodes and decodes data in JSON_.

:class:`Symfony\\Component\\Serializer\\Encoder\\XmlEncoder`
    This class encodes and decodes data in XML_.

:class:`Symfony\\Component\\Serializer\\Encoder\\YamlEncoder`
    This encoder encodes and decodes data in YAML_. This encoder requires the
    :doc:`Yaml Component </components/yaml>`.

:class:`Symfony\\Component\\Serializer\\Encoder\\CsvEncoder`
    This encoder encodes and decodes data in CSV_.

All these encoders are enabled by default when using the Symfony Standard Edition
with the serializer enabled.

.. versionadded:: 3.2

    The ``YamlEncoder`` and ``CsvEncoder`` encoders were introduced in Symfony 3.2

.. _component-serializer-handling-circular-references:

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

The ``setCircularReferenceLimit()`` method of this normalizer sets the number
of times it will serialize the same object before considering it a circular
reference. Its default value is ``1``.

Instead of throwing an exception, circular references can also be handled
by custom callables. This is especially useful when serializing entities
having unique identifiers::

    $encoder = new JsonEncoder();
    $normalizer = new ObjectNormalizer();

    $normalizer->setCircularReferenceHandler(function ($object) {
        return $object->getName();
    });

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

    .. code-block:: php-annotations

        namespace Acme;

        use Symfony\Component\Serializer\Annotation\MaxDepth;

        class MyObj
        {
            /**
             * @MaxDepth(2)
             */
            public $child;

            // ...
        }

    .. code-block:: yaml

        Acme\MyObj:
            attributes:
                child:
                    max_depth: 2

    .. code-block:: xml

        <?xml version="1.0" ?>
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
order to use this feature. It is done automatically when using the Symfony
Standard Edition. When using the standalone component, refer to
:ref:`the groups documentation <component-serializer-attributes-groups>` to
learn how to do that.

The check is only done if the ``enable_max_depth`` key of the serializer context
is set to ``true``. In the following example, the third level is not serialized
because it is deeper than the configured maximum depth of 2::

    $result = $serializer->normalize($level1, null, ['enable_max_depth' => true]);
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

The ``XmlEncoder``
------------------

This encoder transforms arrays into XML and vice versa. For example, take an
object normalized as following::

    ['foo' => [1, 2], 'bar' => true];

The ``XmlEncoder`` encodes this object as follows:

.. code-block:: xml

    <?xml version="1.0"?>
    <response>
        <foo>1</foo>
        <foo>2</foo>
        <bar>1</bar>
    </response>

The array keys beginning with ``@`` are considered XML attributes::

    ['foo' => ['@bar' => 'value']];

    // is encoded as follows:
    // <?xml version="1.0"?>
    // <response>
    //     <foo bar="value"/>
    // </response>

Use the special ``#`` key to define the data of a node::

    ['foo' => ['@bar' => 'value', '#' => 'baz']];

    // is encoded as follows:
    // <?xml version="1.0"?>
    // <response>
    //     <foo bar="value">
    //        baz
    //     </foo>
    // </response>

Context
~~~~~~~

The ``encode()`` method defines a third optional parameter called ``context``
which defines the configuration options for the XmlEncoder an associative array::

    $xmlEncoder->encode($array, 'xml', $context);

These are the options available:

``xml_format_output``
    If set to true, formats the generated XML with line breaks and indentation.

``xml_version``
    Sets the XML version attribute (default: ``1.1``).

``xml_encoding``
    Sets the XML encoding attribute (default: ``utf-8``).

``xml_standalone``
    Adds standalone attribute in the generated XML (default: ``true``).

``xml_root_node_name``
    Sets the root node name (default: ``response``).

``remove_empty_tags``
    If set to true, removes all empty tags in the generated XML.

Recursive Denormalization and Type Safety
-----------------------------------------

The Serializer Component can use the :doc:`PropertyInfo Component </components/property_info>` to denormalize
complex types (objects). The type of the class' property will be guessed using the provided
extractor and used to recursively denormalize the inner data.

When using the Symfony Standard Edition, all normalizers are automatically configured to use the registered extractors.
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

Be aware that disabling type enforcement when denormalizing will not throw any exception if you try to cast a non well formatted datetime string into a real ``DateTimeInterface`` !

Learn more
----------

.. toctree::
    :maxdepth: 1
    :glob:

    /serializer

.. seealso::

    A popular alternative to the Symfony Serializer Component is the third-party
    library, `JMS serializer`_ (versions before `v1.12.0` were released under the Apache license, so incompatible with GPLv2 projects).

.. _`PSR-1 standard`: https://www.php-fig.org/psr/psr-1/
.. _`JMS serializer`: https://github.com/schmittjoh/serializer
.. _Packagist: https://packagist.org/packages/symfony/serializer
.. _RFC3339: https://tools.ietf.org/html/rfc3339#section-5.8
.. _JSON: http://www.json.org/
.. _XML: https://www.w3.org/XML/
.. _YAML: http://yaml.org/
.. _CSV: https://tools.ietf.org/html/rfc4180

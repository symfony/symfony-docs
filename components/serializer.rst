.. index::
   single: Serializer
   single: Components; Serializer

The Serializer Component
========================

   The Serializer component is meant to be used to turn objects into a
   specific format (XML, JSON, YAML, ...) and the other way around.

In order to do so, the Serializer component follows the following
simple schema.

.. _component-serializer-encoders:
.. _component-serializer-normalizers:

.. image:: /_images/components/serializer/serializer_workflow.png

As you can see in the picture above, an array is used as a man in
the middle. This way, Encoders will only deal with turning specific
**formats** into **arrays** and vice versa. The same way, Normalizers
will deal with turning specific **objects** into **arrays** and vice versa.

Serialization is a complicated topic, and while this component may not work
in all cases, it can be a useful tool while developing tools to serialize
and deserialize your objects.

Installation
------------

You can install the component in 2 different ways:

* :doc:`Install it via Composer </components/using_components>` (``symfony/serializer`` on `Packagist`_);
* Use the official Git repository (https://github.com/symfony/serializer).


.. include:: /components/require_autoload.rst.inc

To use the ``ObjectNormalizer``, the :doc:`PropertyAccess component </components/property_access>`
must also be installed.

Usage
-----

Using the Serializer component is really simple. You just need to set up
the :class:`Symfony\\Component\\Serializer\\Serializer` specifying
which encoders and normalizer are going to be available::

    use Symfony\Component\Serializer\Serializer;
    use Symfony\Component\Serializer\Encoder\XmlEncoder;
    use Symfony\Component\Serializer\Encoder\JsonEncoder;
    use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

    $encoders = array(new XmlEncoder(), new JsonEncoder());
    $normalizers = array(new ObjectNormalizer());

    $serializer = new Serializer($normalizers, $encoders);

The preferred normalizer is the
:class:`Symfony\\Component\\Serializer\\Normalizer\\ObjectNormalizer`,
but other normalizers are available. All the examples shown below use
the ``ObjectNormalizer``.

Serializing an Object
---------------------

For the sake of this example, assume the following class already
exists in your project::

    namespace Acme;

    class Person
    {
        private $age;
        private $name;
        private $sportsman;

        // Getters
        public function getName()
        {
            return $this->name;
        }

        public function getAge()
        {
            return $this->age;
        }

        // Issers
        public function isSportsman()
        {
            return $this->sportsman;
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

        public function setSportsman($sportsman)
        {
            $this->sportsman = $sportsman;
        }
    }

Now, if you want to serialize this object into JSON, you only need to
use the Serializer service created before::

    $person = new Acme\Person();
    $person->setName('foo');
    $person->setAge(99);
    $person->setSportsman(false);

    $jsonContent = $serializer->serialize($person, 'json');

    // $jsonContent contains {"name":"foo","age":99,"sportsman":false}

    echo $jsonContent; // or return it in a Response

The first parameter of the :method:`Symfony\\Component\\Serializer\\Serializer::serialize`
is the object to be serialized and the second is used to choose the proper encoder,
in this case :class:`Symfony\\Component\\Serializer\\Encoder\\JsonEncoder`.

Deserializing an Object
-----------------------

You'll now learn how to do the exact opposite. This time, the information
of the ``Person`` class would be encoded in XML format::

    use Acme\Person;

    $data = <<<EOF
    <person>
        <name>foo</name>
        <age>99</age>
        <sportsman>false</sportsman>
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

By default, additional attributes that are not mapped to the denormalized
object will be ignored by the Serializer component. Set the ``allow_extra_attributes``
key of the deserialization context to ``false`` to let the serializer throw
an exception when additional attributes are passed::

    $data = <<<EOF
    <person>
        <name>foo</name>
        <age>99</age>
        <city>Paris</city>
    </person>
    EOF;

    // this will throw a Symfony\Component\Serializer\Exception\ExtraAttributesException
    // because "city" is not an attribute of the Person class
    $person = $serializer->deserialize($data, 'Acme\Person', 'xml', array(
        'allow_extra_attributes' => false,
    ));


Deserializing in an Existing Object
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The serializer can also be used to update an existing object::

    // ...
    $person = new Person();
    $person->setName('bar');
    $person->setAge(99);
    $person->setSportsman(true);

    $data = <<<EOF
    <person>
        <name>foo</name>
        <age>69</age>
    </person>
    EOF;

    $serializer->deserialize($data, Person::class, 'xml', array('object_to_populate' => $person));
    // $person = Acme\Person(name: 'foo', age: '69', sportsman: true)

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

Initialize the :class:`Symfony\\Component\\Serializer\\Mapping\\Factory\\ClassMetadataFactory`
like the following::

    use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
    // For annotations
    use Doctrine\Common\Annotations\AnnotationReader;
    use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
    // For XML
    // use Symfony\Component\Serializer\Mapping\Loader\XmlFileLoader;
    // For YAML
    // use Symfony\Component\Serializer\Mapping\Loader\YamlFileLoader;

    $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
    // For XML
    // $classMetadataFactory = new ClassMetadataFactory(new XmlFileLoader('/path/to/your/definition.xml'));
    // For YAML
    // $classMetadataFactory = new ClassMetadataFactory(new YamlFileLoader('/path/to/your/definition.yml'));

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
             * @Groups({"group3"})
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
                http://symfony.com/schema/dic/serializer-mapping/serializer-mapping-1.0.xsd"
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

    use Symfony\Component\Serializer\Serializer;
    use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

    $obj = new MyObj();
    $obj->foo = 'foo';
    $obj->setBar('bar');

    $normalizer = new ObjectNormalizer($classMetadataFactory);
    $serializer = new Serializer(array($normalizer));

    $data = $serializer->normalize($obj, null, array('groups' => array('group1')));
    // $data = array('foo' => 'foo');

    $obj2 = $serializer->denormalize(
        array('foo' => 'foo', 'bar' => 'bar'),
        'MyObj',
        null,
        array('groups' => array('group1', 'group3'))
    );
    // $obj2 = MyObj(foo: 'foo', bar: 'bar')

.. _ignoring-attributes-when-serializing:

Ignoring Attributes
-------------------

.. note::

    Using attribute groups instead of the :method:`Symfony\\Component\\Serializer\\Normalizer\\AbstractNormalizer::setIgnoredAttributes`
    method is considered best practice.

As an option, there's a way to ignore attributes from the origin object. To remove
those attributes use the
:method:`Symfony\\Component\\Serializer\\Normalizer\\AbstractNormalizer::setIgnoredAttributes`
method on the normalizer definition::

    use Symfony\Component\Serializer\Serializer;
    use Symfony\Component\Serializer\Encoder\JsonEncoder;
    use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

    $normalizer = new ObjectNormalizer();
    $normalizer->setIgnoredAttributes(array('age'));
    $encoder = new JsonEncoder();

    $serializer = new Serializer(array($normalizer), array($encoder));
    $serializer->serialize($person, 'json'); // Output: {"name":"foo","sportsman":false}

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
            // remove org_ prefix
            return 'org_' === substr($propertyName, 0, 4) ? substr($propertyName, 4) : $propertyName;
        }
    }

The custom normalizer can be used by passing it as second parameter of any
class extending :class:`Symfony\\Component\\Serializer\\Normalizer\\AbstractNormalizer`,
including :class:`Symfony\\Component\\Serializer\\Normalizer\\GetSetMethodNormalizer`
and :class:`Symfony\\Component\\Serializer\\Normalizer\\PropertyNormalizer`::

    use Symfony\Component\Serializer\Encoder\JsonEncoder
    use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
    use Symfony\Component\Serializer\Serializer;

    $nameConverter = new OrgPrefixNameConverter();
    $normalizer = new ObjectNormalizer(null, $nameConverter);

    $serializer = new Serializer(array($normalizer), array(new JsonEncoder()));

    $obj = new Company();
    $obj->name = 'Acme Inc.';
    $obj->address = '123 Main Street, Big City';

    $json = $serializer->serialize($obj);
    // {"org_name": "Acme Inc.", "org_address": "123 Main Street, Big City"}
    $objCopy = $serializer->deserialize($json);
    // Same data as $obj

.. _using-camelized-method-names-for-underscored-attributes:

CamelCase to snake_case
~~~~~~~~~~~~~~~~~~~~~~~

In many formats, it's common to use underscores to separate words (also known
as snake_case). However, PSR-1 specifies that the preferred style for PHP
properties and methods is CamelCase.

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

    $anne = $normalizer->denormalize(array('first_name' => 'Anne'), 'Person');
    // Person object with firstName: 'Anne'

Serializing Boolean Attributes
------------------------------

If you are using isser methods (methods prefixed by ``is``, like
``Acme\Person::isSportsman()``), the Serializer component will automatically
detect and use it to serialize related attributes.

The ``ObjectNormalizer`` also takes care of methods starting with ``has``, ``add``
and ``remove``.

Using Callbacks to Serialize Properties with Object Instances
-------------------------------------------------------------

When serializing, you can set a callback to format a specific object property::

    use Acme\Person;
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

    $normalizer->setCallbacks(array('createdAt' => $callback));

    $serializer = new Serializer(array($normalizer), array($encoder));

    $person = new Person();
    $person->setName('cordoval');
    $person->setAge(34);
    $person->setCreatedAt(new \DateTime('now'));

    $serializer->serialize($person, 'json');
    // Output: {"name":"cordoval", "age": 34, "createdAt": "2014-03-22T09:43:12-0500"}

Normalizers
-----------

There are several types of normalizers available:

:class:`Symfony\\Component\\Serializer\\Normalizer\\ObjectNormalizer`
    This normalizer leverages the :doc:`PropertyAccess Component </components/property_access>`
    to read and write in the object. It means that it can access to properties
    directly and through getters, setters, hassers, adders and removers. It supports
    calling the constructor during the denormalization process.

    Objects are normalized to a map of property names (method name stripped of
    the "get"/"set"/"has"/"remove" prefix and converted to lower case) to property
    values.

    The ``ObjectNormalizer`` is the most powerful normalizer. It is configured by
    default when using the Symfony Standard Edition with the serializer enabled.

:class:`Symfony\\Component\\Serializer\\Normalizer\\GetSetMethodNormalizer`
    This normalizer reads the content of the class by calling the "getters"
    (public methods starting with "get"). It will denormalize data by calling
    the constructor and the "setters" (public methods starting with "set").

    Objects are normalized to a map of property names (method name stripped of
    the "get" prefix and converted to lower case) to property values.

:class:`Symfony\\Component\\Serializer\\Normalizer\\PropertyNormalizer`
    This normalizer directly reads and writes public properties as well as
    **private and protected** properties. It supports calling the constructor
    during the denormalization process.

    Objects are normalized to a map of property names to property values.

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
    By default it uses the RFC3339_ format.

    .. versionadded:: 3.2
        Support for specifying datetime format during denormalization was
        introduced in the ``DateTimeNormalizer`` in Symfony 3.2.

:class:`Symfony\\Component\\Serializer\\Normalizer\\DataUriNormalizer`
    This normalizer converts :phpclass:`SplFileInfo` objects into a data URI
    string (``data:...``) such that files can be embedded into serialized data.

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
throws a :class:`Symfony\\Component\\Serializer\\Exception\\CircularReferenceException`
when such a case is encountered::

    $member = new Member();
    $member->setName('Kévin');

    $org = new Organization();
    $org->setName('Les-Tilleuls.coop');
    $org->setMembers(array($member));

    $member->setOrganization($org);

    echo $serializer->serialize($org, 'json'); // Throws a CircularReferenceException

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

    $serializer = new Serializer(array($normalizer), array($encoder));
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

        use Symfony\Component\Serializer\Annotation\MaxDepth;

        namespace Acme;

        class MyObj
        {
            /**
             * @MaxDepth(2)
             */
            public $foo;

            // ...
        }

    .. code-block:: yaml

        Acme\MyObj:
            attributes:
                foo:
                    max_depth: 2

    .. code-block:: xml

        <?xml version="1.0" ?>
        <serializer xmlns="http://symfony.com/schema/dic/serializer-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/serializer-mapping
                http://symfony.com/schema/dic/serializer-mapping/serializer-mapping-1.0.xsd"
        >
            <class name="Acme\MyObj">
                <attribute name="foo">
                    <max-depth>2</max-depth>
                </attribute>
        </serializer>

The metadata loader corresponding to the chosen format must be configured in
order to use this feature. It is done automatically when using the Symfony
Standard Edition. When using the standalone component, refer to
:ref:`the groups documentation <attributes-groups>` to
learn how to do that.

The check is only done if the ``enable_max_depth`` key of the serializer context
is set to ``true``. In the following example, the third level is not serialized
because it is deeper than the configured maximum depth of 2::

    $result = $serializer->normalize($level1, null, array('enable_max_depth' => true));
    /*
    $result = array(
        'foo' => 'level1',
        'child' => array(
                'foo' => 'level2',
                'child' => array(
                        'child' => null,
                    ),
            ),
    );
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

    $persons = array($person1, $person2);
    $data = $serializer->serialize($persons, 'json');

    // $data contains [{"name":"foo","age":99,"sportsman":false},{"name":"bar","age":33,"sportsman":true}]

If you want to deserialize such a structure, you need to add the
:class:`Symfony\\Component\\Serializer\\Normalizer\\ArrayDenormalizer`
to the set of normalizers. By appending ``[]`` to the type parameter of the
:method:`Symfony\\Component\\Serializer\\Serializer::deserialize` method,
you indicate that you're expecting an array instead of a single object.

.. code-block:: php

    use Symfony\Component\Serializer\Encoder\JsonEncoder;
    use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
    use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
    use Symfony\Component\Serializer\Serializer;

    $serializer = new Serializer(
        array(new GetSetMethodNormalizer(), new ArrayDenormalizer()),
        array(new JsonEncoder())
    );

    $data = ...; // The serialized data from the previous example
    $persons = $serializer->deserialize($data, 'Acme\Person[]', 'json');

Recursive Denormalization and Type Safety
-----------------------------------------

The Serializer Component can use the :doc:`PropertyInfo Component </components/property_info>` to denormalize
complex types (objects). The type of the class' property will be guessed using the provided
extractor and used to recursively denormalize the inner data.

When using the Symfony Standard Edition, all normalizers are automatically configured to use the registered extractors.
When using the component standalone, an implementation of :class:`Symfony\\Component\\PropertyInfo\\PropertyTypeExtractorInterface`,
(usually an instance of :class:`Symfony\\Component\\PropertyInfo\\PropertyInfoExtractor`) must be passed as the 4th
parameter of the ``ObjectNormalizer``::

    use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
    use Symfony\Component\Serializer\Serializer;
    use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
    use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

    namespace Acme;

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

    $normalizer = new ObjectNormalizer(null, null, null, new ReflectionExtractor()); //
    $serializer = new Serializer(array(new DateTimeNormalizer(), $normalizer));

    $obj = $serializer->denormalize(
        array('inner' => array('foo' => 'foo', 'bar' => 'bar'), 'date' => '1988/01/21'),
         'Acme\ObjectOuter'
    );

    dump($obj->getInner()->foo); // 'foo'
    dump($obj->getInner()->bar); // 'bar'
    dump($obj->getDate()->format('Y-m-d')); // '1988-01-21'

When a ``PropertyTypeExtractor`` is available, the normalizer will also check that the data to denormalize
matches the type of the property (even for primitive types). For instance, if a ``string`` is provided, but
the type of the property is ``int``, an :class:`Symfony\\Component\\Serializer\\Exception\\UnexpectedValueException`
will be thrown.

Learn more
----------

.. toctree::
    :maxdepth: 1
    :glob:

    /serializer

.. seealso::

    A popular alternative to the Symfony Serializer Component is the third-party
    library, `JMS serializer`_ (released under the Apache license, so incompatible with GPLv2 projects).

.. _`JMS serializer`: https://github.com/schmittjoh/serializer
.. _Packagist: https://packagist.org/packages/symfony/serializer
.. _RFC3339: https://tools.ietf.org/html/rfc3339#section-5.8
.. _JSON: http://www.json.org/
.. _XML: https://www.w3.org/XML/
.. _YAML: http://yaml.org/
.. _CSV: https://tools.ietf.org/html/rfc4180

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

Alternatively, you can clone the `<https://github.com/symfony/serializer>`_ repository.

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

    $serializer->deserialize($data, Person::class, 'xml', array('object_to_populate' => $person));
    // $person = App\Model\Person(name: 'foo', age: '69', sportsperson: true)

This is a common need when working with an ORM.

.. _component-serializer-attributes-groups:

Attributes Groups
-----------------

.. versionadded:: 2.7
    The support of serialization and deserialization groups was introduced
    in Symfony 2.7.

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

.. include:: /_includes/_annotation_loader_tip.rst.inc

.. _ignoring-attributes-when-serializing:

Ignoring Attributes
-------------------

.. note::

    Using attribute groups instead of the :method:`Symfony\\Component\\Serializer\\Normalizer\\AbstractNormalizer::setIgnoredAttributes`
    method is considered best practice.

.. versionadded:: 2.3
    The :method:`Symfony\\Component\\Serializer\\Normalizer\\AbstractNormalizer::setIgnoredAttributes`
    method was introduced in Symfony 2.3.

.. versionadded:: 2.7
    Prior to Symfony 2.7, attributes were only ignored while serializing. Since Symfony
    2.7, they are ignored when deserializing too.

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
    $serializer->serialize($person, 'json'); // Output: {"name":"foo","sportsperson":false}

Converting Property Names when Serializing and Deserializing
------------------------------------------------------------

.. versionadded:: 2.7
    The :class:`Symfony\\Component\\Serializer\\NameConverter\\NameConverterInterface`
    interface was introduced in Symfony 2.7.

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

    $serializer = new Serializer(array($normalizer), array(new JsonEncoder()));

    $obj = new Company();
    $obj->name = 'Acme Inc.';
    $obj->address = '123 Main Street, Big City';

    $json = $serializer->serialize($obj, 'json');
    // {"org_name": "Acme Inc.", "org_address": "123 Main Street, Big City"}
    $objCopy = $serializer->deserialize($json, Company::class, 'json');
    // Same data as $obj

.. _using-camelized-method-names-for-underscored-attributes:

CamelCase to snake_case
~~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 2.7
    The :class:`Symfony\\Component\\Serializer\\NameConverter\\CamelCaseToSnakeCaseNameConverter`
    interface was introduced in Symfony 2.7.

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

    $anne = $normalizer->denormalize(array('first_name' => 'Anne'), 'Person');
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

    Objects are normalized to a map of property names and values (names are
    generated removing the ``get``, ``set``, ``has`` or ``remove`` prefix from
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
    **private and protected** properties. It supports calling the constructor
    during the denormalization process.

    Objects are normalized to a map of property names to property values.

.. versionadded:: 2.6
    The :class:`Symfony\\Component\\Serializer\\Normalizer\\PropertyNormalizer`
    class was introduced in Symfony 2.6.

.. versionadded:: 2.7
    The :class:`Symfony\\Component\\Serializer\\Normalizer\\ObjectNormalizer`
    class was introduced in Symfony 2.7.

Handling Circular References
----------------------------

.. versionadded:: 2.6
    Handling of circular references was introduced in Symfony 2.6. In previous
    versions of Symfony, circular references led to infinite loops.

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
    $organization->setMembers(array($member));

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

    $serializer = new Serializer(array($normalizer), array($encoder));
    var_dump($serializer->serialize($org, 'json'));
    // {"name":"Les-Tilleuls.coop","members":[{"name":"K\u00e9vin", organization: "Les-Tilleuls.coop"}]}

Learn more
----------

.. toctree::
    :maxdepth: 1
    :glob:

    /serializer

.. seealso::

    A popular alternative to the Symfony Serializer Component is the third-party
    library, `JMS serializer`_ (released under the Apache license, so incompatible with GPLv2 projects).

.. _`PSR-1 standard`: https://www.php-fig.org/psr/psr-1/
.. _`JMS serializer`: https://github.com/schmittjoh/serializer
.. _Packagist: https://packagist.org/packages/symfony/serializer

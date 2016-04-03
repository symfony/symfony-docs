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

.. image:: /images/components/serializer/serializer_workflow.png

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

To use the ``ObjectNormalizer``, the :doc:`PropertyAccess component </components/property_access/index>`
must also be installed.

Usage
-----

Using the Serializer component is really simple. You just need to set up
the :class:`Symfony\\Component\\Serializer\\Serializer` specifying
which Encoders and Normalizer are going to be available::

    use Symfony\Component\Serializer\Serializer;
    use Symfony\Component\Serializer\Encoder\XmlEncoder;
    use Symfony\Component\Serializer\Encoder\JsonEncoder;
    use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

    // Allows to serialize/deserialize in JSON and XML
    $encoders = array(new XmlEncoder(), new JsonEncoder());
    $normalizers = array(new ObjectNormalizer());

    $serializer = new Serializer($normalizers, $encoders);

The preferred normalizer is the
:class:`Symfony\\Component\\Serializer\\Normalizer\\ObjectNormalizer`, but other
normalizers are available.

.. note::

    Read the dedicated sections to learn more about :doc:`/components/serializer/encoders`
    and :doc:`/components/serializer/normalizers`.

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

        // Getter
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

    $data = <<<EOF
    <person>
        <name>foo</name>
        <age>99</age>
        <sportsman>false</sportsman>
    </person>
    EOF;

    // Will return an instance of Acme\Person
    $person = $serializer->deserialize($data, 'Acme\Person', 'xml');

In this case, :method:`Symfony\\Component\\Serializer\\Serializer::deserialize`
needs three parameters:

#. The information to be decoded
#. The name of the class this information will be decoded to
#. The encoder used to convert that information into an array

Go further
----------

If this is not already done, you should take a look at :doc:`/components/serializer/encoders`
and :doc:`/components/serializer/normalizers` to be able to use the entire abilities of this component.

Normalizers
-----------

There are several types of normalizers available:

:class:`Symfony\\Component\\Serializer\\Normalizer\\ObjectNormalizer`
    This normalizer leverages the :doc:`PropertyAccess Component </components/property_access/index>`
    to read and write in the object. It means that it can access to properties
    directly and through getters, setters, hassers, adders and removers. It supports
    calling the constructor during the denormalization process.

    Objects are normalized to a map of property names (method name stripped of
    the "get"/"set"/"has"/"remove" prefix and converted to lower case) to property
    values.

    The ``ObjectNormalizer`` is the most powerful normalizer. It is a configured
    by default when using the Symfony Standard Edition with the serializer enabled.

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

.. seealso::

    A popular alternative to the Symfony Serializer Component is the third-party
    library, `JMS serializer`_ (released under the Apache license, so incompatible with GPLv2 projects).

.. _`JMS serializer`: https://github.com/schmittjoh/serializer
.. _Packagist: https://packagist.org/packages/symfony/serializer

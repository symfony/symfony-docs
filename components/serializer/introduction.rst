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

Usage
-----

Using the Serializer component is really simple. You just need to set up
the :class:`Symfony\\Component\\Serializer\\Serializer` specifying
which Encoders and Normalizer are going to be available::

    use Symfony\Component\Serializer\Serializer;
    use Symfony\Component\Serializer\Encoder\XmlEncoder;
    use Symfony\Component\Serializer\Encoder\JsonEncoder;
    use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

    // Allows to serialize/deserialize in JSON and XML
    $encoders = array(new XmlEncoder(), new JsonEncoder());
    // Allows to normalize objects thanks to their getters and setters
    $normalizers = array(new GetSetMethodNormalizer());

    $serializer = new Serializer($normalizers, $encoders);

The following examples assume that you instantiate the serializer as above.

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

        // Getter
        public function getName()
        {
            return $this->name;
        }

        public function getAge()
        {
            return $this->age;
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
    }

Now, if you want to serialize this object into JSON, you only need to
use the Serializer service created before::

    $person = new Acme\Person();
    $person->setName('foo');
    $person->setAge(99);

    $jsonContent = $serializer->serialize($person, 'json');

    // $jsonContent contains {"name":"foo","age":99}

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

JMSSerializer
-------------

A popular third-party library, `JMS serializer`_, provides a more
sophisticated albeit more complex solution. This library includes the
ability to configure how your objects should be serialized/deserialized via
annotations (as well as YAML, XML and PHP), integration with the Doctrine ORM,
and handling of other complex cases (e.g. circular references).

.. _`JMS serializer`: https://github.com/schmittjoh/serializer
.. _Packagist: https://packagist.org/packages/symfony/serializer

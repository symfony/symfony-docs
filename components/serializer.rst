.. index::
   single: Serializer
   single: Components; Serializer

The Serializer Component
========================

   The Serializer Component is meant to be used to turn objects into a
   specific format (XML, JSON, Yaml, ...) and the other way around.

In order to do so, the Serializer Component follows the following
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

* Use the official Git repository (https://github.com/symfony/Serializer);
* :doc:`Install it via Composer </components/using_components>` (``symfony/serializer`` on `Packagist`_).

Usage
-----

Using the Serializer component is really simple. You just need to set up
the :class:`Symfony\\Component\\Serializer\\Serializer` specifying
which Encoders and Normalizer are going to be available::

    use Symfony\Component\Serializer\Serializer;
    use Symfony\Component\Serializer\Encoder\XmlEncoder;
    use Symfony\Component\Serializer\Encoder\JsonEncoder;
    use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

    $encoders = array(new XmlEncoder(), new JsonEncoder());
    $normalizers = array(new GetSetMethodNormalizer());

    $serializer = new Serializer($normalizers, $encoders);

Serializing an Object
---------------------

For the sake of this example, assume the following class already
exists in your project::

    namespace Acme;

    class Person
    {
        private $age;
        private $name;

        // Getters
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

Ignoring Attributes when Serializing
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 2.3
    The :method:`GetSetMethodNormalizer::setIgnoredAttributes<Symfony\\Component\\Serializer\\Normalizer\\GetSetMethodNormalizer::setIgnoredAttributes>`
    method was added in Symfony 2.3.

As an option, there's a way to ignore attributes from the origin object when
serializing. To remove those attributes use the
:method:`Symfony\\Component\\Serializer\\Normalizer\\GetSetMethodNormalizer::setIgnoredAttributes`
method on the normalizer definition::

    use Symfony\Component\Serializer\Serializer;
    use Symfony\Component\Serializer\Encoder\JsonEncoder;
    use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

    $normalizer = new GetSetMethodNormalizer();
    $normalizer->setIgnoredAttributes(array('age'));
    $encoder = new JsonEncoder();

    $serializer = new Serializer(array($normalizer), array($encoder));
    $serializer->serialize($person, 'json'); // Output: {"name":"foo"}

Deserializing an Object
-----------------------

Let's see now how to do the exactly the opposite. This time, the information
of the `People` class would be encoded in XML format::

    $data = <<<EOF
    <person>
        <name>foo</name>
        <age>99</age>
    </person>
    EOF;

    $person = $serializer->deserialize($data,'Acme\Person','xml');

In this case, :method:`Symfony\\Component\\Serializer\\Serializer::deserialize`
needs three parameters:

1. The information to be decoded
2. The name of the class this information will be decoded to
3. The encoder used to convert that information into an array

Using Camelized Method Names for Underscored Attributes
-------------------------------------------------------

.. versionadded:: 2.3
    The :method:`GetSetMethodNormalizer::setCamelizedAttributes<Symfony\\Component\\Serializer\\Normalizer\\GetSetMethodNormalizer::setCamelizedAttributes>`
    method was added in Symfony 2.3.

Sometimes property names from the serialized content are underscored (e.g.
``first_name``).  Normally, these attributes will use get/set methods like
``getFirst_name``, when ``getFirstName`` method is what you really want. To
change that behavior use the
:method:`Symfony\\Component\\Serializer\\Normalizer\\GetSetMethodNormalizer::setCamelizedAttributes`
method on the normalizer definition::

    $encoder = new JsonEncoder();
    $normalizer = new GetSetMethodNormalizer();
    $normalizer->setCamelizedAttributes(array('first_name'));

    $serializer = new Serializer(array($normalizer), array($encoder));

    $json = <<<EOT
    {
        "name":       "foo",
        "age":        "19",
        "first_name": "bar"
    }
    EOT;

    $person = $serializer->deserialize($json, 'Acme\Person', 'json');

As a final result, the deserializer uses the ``first_name`` attribute as if
it were ``firstName`` and uses the ``getFirstName`` and ``setFirstName`` methods.

JMSSerializer
-------------

A popular third-party library, `JMS serializer`_, provides a more
sophisticated albeit more complex solution. This library includes the
ability to configure how your objects should be serialize/deserialized via
annotations (as well as YML, XML and PHP), integration with the Doctrine ORM,
and handling of other complex cases (e.g. circular references).

.. _`JMS serializer`: https://github.com/schmittjoh/serializer
.. _Packagist: https://packagist.org/packages/symfony/serializer

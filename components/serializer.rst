.. index::
   single: Serializer 
   single: Components; Serializer

The Serializer Component
========================

   The Serializer Component is meant to be used to turn objects into a
   specific format (XML, JSON, Yaml, ...) and the other way around.

In order to do so, the Serializer Component follows the following
simple schema.

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

You can install the component in many different ways:

* Use the official Git repository (https://github.com/symfony/Serializer);
* Install it via PEAR ( `pear.symfony.com/Serializer`);
* Install it via Composer (`symfony/serializer` on Packagist).

Usage
-----

Using the Serializer component is really simple. We just need to set up
the :class:`Symfony\\Component\\Serializer\\Serializer` specifying
which Encoders and Normalizer are going to be available::

    use Symfony\Component\Serializer\Serializer;
    use Symfony\Component\Serializer\Encoder\XmlEncoder;
    use Symfony\Component\Serializer\Encoder\JsonEncoder;
    use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

    $encoders = array(new XmlEncoder(), new JsonEncoder());
    $normalizers = array(new GetSetMethodNormalizer());

    $serializer = new Serializer($normalizers, $encoders);

Serializing an object
~~~~~~~~~~~~~~~~~~~~~

For the sake of this example, let's assume the following class already
exists in our project::

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

Now, if we want to serialize this object into JSON, we only need to
use the Serializer service created before::

    $person = new Acme\Person();
    $person->setName('foo');
    $person->setAge(99);

    $serializer->serialize($person, 'json'); // Output: {"name":"foo","age":99}

The first parameter of the :method:`Symfony\\Component\\Serializer\\Serializer::serialize`
is the object to be serialized and the second is used to choose the proper encoder,
in this case :class:`Symfony\\Component\\Serializer\\Encoder\\JsonEncoder`.

Deserializing an Object
~~~~~~~~~~~~~~~~~~~~~~~

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

JMSSerializationBundle
----------------------

A popular third-party bundle, `JMSSerializationBundle`_ exists and extends
(and sometimes replaces) the serialization functionality. This includes the
ability to configure how your objects should be serialize/deserialized via
annotations (as well as YML, XML and PHP), integration with the Doctrine ORM,
and handling of other complex cases (e.g. circular references).

.. _`JMSSerializationBundle`: https://github.com/schmittjoh/JMSSerializerBundle
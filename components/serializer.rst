.. index::
   single: Serializer 
   single: Components; Serializer

The Serializer Component
========================

   The Serializer Component is meant to be used to turn Objects into a
   specific format (XML, JSON, Yaml, ...) and the other way around.

In order to do so, the Serializer Components follows the following
simple schema.

.. image:: /images/components/serializer/serializer_workflow.png

As you can see in the picture above, an array is used as a man in
the middle. This way, Normalizers will only deal with turning specific
**formats** into **arrays** and vice versa. The same way, Normalizers 
will deal with turning specific **objects** into **arrays** and vice versa.

Installation
------------

You can install the component in many different ways:

* Use the official Git repository (https://github.com/symfony/Serializer);
* Install it via PEAR ( `pear.symfony.com/Serializer`);
* Install it via Composer (`symfony/serializer` on Packagist).

Usage
-----

Using the Serializer component is really simple. We just need to set up
the  :class:`Symfony\\Component\\Serializer\\Serializer` specifying
what Encoders and Normalizer are going to be available::

    use Symfony\Component\Serializer\Serializer;
    use Symfony\Component\Serializer\Encoder\XmlEncoder;
    use Symfony\Component\Serializer\Encoder\JsonEncoder;
    use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

    $encoders = array(new XmlEncoder(), new JsonEncoder() );
    $normalizers = array(new GetSetMethodNormalizer());

    $serializer = new Serializer($normalizers, $encoders);


Serializing an object
~~~~~~~~~~~~~~~~~~~~~

For the sake of this example, let's ssume the following class already
exists in our project::

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

    $person = new Person();
    $person->setName('foo');
    $person->setAge(99);

    $serializer->serialize($person, 'json'); // Output: {"name":"foo","age":99}

The first paramater of the `Serializer::serialize` is the object to be
serialized, the second one will be used to pick the proper encoder,
in this case :class:`Symfony\\Component\\Serializer\\Encoder\\JsonEncoder`.

Deserializing an object
~~~~~~~~~~~~~~~~~~~~~~~~ 

Let's see now how to do the exactly the opposite. This time, the information
of the `People` class would be encoded in XML format::

    $data = <<<EOF
    <person>
        <name>foo</name>
        <age>99</age>
    </person>
    EOF;

    $person = $serializer->deserialize($data,'Person','xml');

In this case, `Serializer::deserialize` needs three parameters:

1. The information to be decoded
2. The name of the class this information will be decoded to
3. The encoder used to convert that information into an array

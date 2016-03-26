.. index::
    single: PropertyAccess
    single: Components; PropertyAccess

The PropertyAccess Component
============================

    The PropertyAccess component provides function to read and write from/to an
    object or array using a simple string notation.

Installation
------------

You can install the component in two different ways:

* :doc:`Install it via Composer</components/using_components>` (``symfony/property-access`` on `Packagist`_);
* Use the official Git repository (https://github.com/symfony/property-access).

.. include:: /components/require_autoload.rst.inc

Usage
-----

The entry point of this component is the
:method:`PropertyAccess::createPropertyAccessor<Symfony\\Component\\PropertyAccess\\PropertyAccess::createPropertyAccessor>`
factory. This factory will create a new instance of the
:class:`Symfony\\Component\\PropertyAccess\\PropertyAccessor` class with the
default configuration::

    use Symfony\Component\PropertyAccess\PropertyAccess;

    $accessor = PropertyAccess::createPropertyAccessor();

Reading from Arrays
-------------------

You can read an array with the
:method:`PropertyAccessor::getValue<Symfony\\Component\\PropertyAccess\\PropertyAccessor::getValue>`
method. This is done using the index notation that is used in PHP::

    // ...
    $person = array(
        'first_name' => 'Wouter',
    );

    var_dump($accessor->getValue($person, '[first_name]')); // 'Wouter'
    var_dump($accessor->getValue($person, '[age]')); // null

As you can see, the method will return ``null`` if the index does not exists.

You can also use multi dimensional arrays::

    // ...
    $persons = array(
        array(
            'first_name' => 'Wouter',
        ),
        array(
            'first_name' => 'Ryan',
        )
    );

    var_dump($accessor->getValue($persons, '[0][first_name]')); // 'Wouter'
    var_dump($accessor->getValue($persons, '[1][first_name]')); // 'Ryan'

Reading from Objects
--------------------

The ``getValue()`` method is a very robust method, and you can see all of its
features when working with objects.

Accessing public Properties
~~~~~~~~~~~~~~~~~~~~~~~~~~~

To read from properties, use the "dot" notation::

    // ...
    $person = new Person();
    $person->firstName = 'Wouter';

    var_dump($accessor->getValue($person, 'firstName')); // 'Wouter'

    $child = new Person();
    $child->firstName = 'Bar';
    $person->children = array($child);

    var_dump($accessor->getValue($person, 'children[0].firstName')); // 'Bar'

.. caution::

    Accessing public properties is the last option used by ``PropertyAccessor``.
    It tries to access the value using the below methods first before using
    the property directly. For example, if you have a public property that
    has a getter method, it will use the getter.

Using Getters
~~~~~~~~~~~~~

The ``getValue()`` method also supports reading using getters. The method will
be created using common naming conventions for getters. It camelizes the
property name (``first_name`` becomes ``FirstName``) and prefixes it with
``get``. So the actual method becomes ``getFirstName()``::

    // ...
    class Person
    {
        private $firstName = 'Wouter';

        public function getFirstName()
        {
            return $this->firstName;
        }
    }

    $person = new Person();

    var_dump($accessor->getValue($person, 'first_name')); // 'Wouter'

You can override the called getter method using metadata (i.e. annotations or
configuration files). See `Custom method calls and virtual properties in a class`_.

Using Hassers/Issers
~~~~~~~~~~~~~~~~~~~~

And it doesn't even stop there. If there is no getter found, the accessor will
look for an isser or hasser. This method is created using the same way as
getters, this means that you can do something like this::

    // ...
    class Person
    {
        private $author = true;
        private $children = array();

        public function isAuthor()
        {
            return $this->author;
        }

        public function hasChildren()
        {
            return 0 !== count($this->children);
        }
    }

    $person = new Person();

    if ($accessor->getValue($person, 'author')) {
        var_dump('He is an author');
    }
    if ($accessor->getValue($person, 'children')) {
        var_dump('He has children');
    }

This will produce: ``He is an author``

Magic ``__get()`` Method
~~~~~~~~~~~~~~~~~~~~~~~~

The ``getValue()`` method can also use the magic ``__get()`` method::

    // ...
    class Person
    {
        private $children = array(
            'Wouter' => array(...),
        );

        public function __get($id)
        {
            return $this->children[$id];
        }
    }

    $person = new Person();

    var_dump($accessor->getValue($person, 'Wouter')); // array(...)

.. _components-property-access-magic-call:

Magic ``__call()`` Method
~~~~~~~~~~~~~~~~~~~~~~~~~

At last, ``getValue()`` can use the magic ``__call()`` method, but you need to
enable this feature by using :class:`Symfony\\Component\\PropertyAccess\\PropertyAccessorBuilder`::

    // ...
    class Person
    {
        private $children = array(
            'wouter' => array(...),
        );

        public function __call($name, $args)
        {
            $property = lcfirst(substr($name, 3));
            if ('get' === substr($name, 0, 3)) {
                return isset($this->children[$property])
                    ? $this->children[$property]
                    : null;
            } elseif ('set' === substr($name, 0, 3)) {
                $value = 1 == count($args) ? $args[0] : null;
                $this->children[$property] = $value;
            }
        }
    }

    $person = new Person();

    // Enable magic __call
    $accessor = PropertyAccess::createPropertyAccessorBuilder()
        ->enableMagicCall()
        ->getPropertyAccessor();

    var_dump($accessor->getValue($person, 'wouter')); // array(...)

.. caution::

    The ``__call()`` feature is disabled by default, you can enable it by calling
    :method:`PropertyAccessorBuilder::enableMagicCall<Symfony\\Component\\PropertyAccess\\PropertyAccessorBuilder::enableMagicCall>`
    see `Enable other Features`_.

Writing to Arrays
-----------------

The ``PropertyAccessor`` class can do more than just read an array, it can
also write to an array. This can be achieved using the
:method:`PropertyAccessor::setValue<Symfony\\Component\\PropertyAccess\\PropertyAccessor::setValue>`
method::

    // ...
    $person = array();

    $accessor->setValue($person, '[first_name]', 'Wouter');

    var_dump($accessor->getValue($person, '[first_name]')); // 'Wouter'
    // or
    // var_dump($person['first_name']); // 'Wouter'

Writing to Objects
------------------

The ``setValue()`` method has the same features as the ``getValue()`` method. You
can use setters, the magic ``__set()`` method or properties to set values::

    // ...
    class Person
    {
        public $firstName;
        private $lastName;
        private $children = array();

        public function setLastName($name)
        {
            $this->lastName = $name;
        }

        public function getLastName()
        {
            return $this->lastName;
        }

        public function getChildren()
        {
            return $this->children;
        }

        public function __set($property, $value)
        {
            $this->$property = $value;
        }
    }

    $person = new Person();

    $accessor->setValue($person, 'firstName', 'Wouter');
    $accessor->setValue($person, 'lastName', 'de Jong'); // setLastName is called
    $accessor->setValue($person, 'children', array(new Person())); // __set is called

    var_dump($person->firstName); // 'Wouter'
    var_dump($person->getLastName()); // 'de Jong'
    var_dump($person->getChildren()); // array(Person());

You can also use ``__call()`` to set values but you need to enable the feature,
see `Enable other Features`_.

.. code-block:: php

    // ...
    class Person
    {
        private $children = array();

        public function __call($name, $args)
        {
            $property = lcfirst(substr($name, 3));
            if ('get' === substr($name, 0, 3)) {
                return isset($this->children[$property])
                    ? $this->children[$property]
                    : null;
            } elseif ('set' === substr($name, 0, 3)) {
                $value = 1 == count($args) ? $args[0] : null;
                $this->children[$property] = $value;
            }
        }

    }

    $person = new Person();

    // Enable magic __call
    $accessor = PropertyAccess::createPropertyAccessorBuilder()
        ->enableMagicCall()
        ->getPropertyAccessor();

    $accessor->setValue($person, 'wouter', array(...));

    var_dump($person->getWouter()); // array(...)

You can override the called setter method using metadata (i.e. annotations or
configuration files). See `Custom method calls and virtual properties in a class`_.

Writing to Array Properties
~~~~~~~~~~~~~~~~~~~~~~~~~~~

The ``PropertyAccessor`` class allows to update the content of arrays stored in
properties through *adder* and *remover* methods.

.. code-block:: php

    // ...
    class Person
    {
        /**
         * @var string[]
         */
        private $children = array();

        public function getChildren(): array
        {
            return $this->children;
        }

        public function addChild(string $name): void
        {
            $this->children[$name] = $name;
        }

        public function removeChild(string $name): void
        {
            unset($this->children[$name]);
        }
    }

    $person = new Person();
    $accessor->setValue($person, 'children', array('kevin', 'wouter'));

    var_dump($person->getChildren()); // array('kevin', 'wouter')

The PropertyAccess component checks for methods called ``add<SingularOfThePropertyName>()``
and ``remove<SingularOfThePropertyName>()``. Both methods must be defined.
For instance, in the previous example, the component looks for the ``addChild()``
and ``removeChild()`` methods to access to the ``children`` property.
`The Inflector component`_ is used to find the singular of a property name.

If available, *adder* and *remover* methods have priority over a *setter* method.

Checking Property Paths
-----------------------

When you want to check whether
:method:`PropertyAccessor::getValue<Symfony\\Component\\PropertyAccess\\PropertyAccessor::getValue>`
can safely be called without actually calling that method, you can use
:method:`PropertyAccessor::isReadable<Symfony\\Component\\PropertyAccess\\PropertyAccessor::isReadable>`
instead::

    $person = new Person();

    if ($accessor->isReadable($person, 'firstName')) {
        // ...
    }

The same is possible for :method:`PropertyAccessor::setValue<Symfony\\Component\\PropertyAccess\\PropertyAccessor::setValue>`:
Call the
:method:`PropertyAccessor::isWritable<Symfony\\Component\\PropertyAccess\\PropertyAccessor::isWritable>`
method to find out whether a property path can be updated::

    $person = new Person();

    if ($accessor->isWritable($person, 'firstName')) {
        // ...
    }

Mixing Objects and Arrays
-------------------------

You can also mix objects and arrays::

    // ...
    class Person
    {
        public $firstName;
        private $children = array();

        public function setChildren($children)
        {
            $this->children = $children;
        }

        public function getChildren()
        {
            return $this->children;
        }
    }

    $person = new Person();

    $accessor->setValue($person, 'children[0]', new Person);
    // equal to $person->getChildren()[0] = new Person()

    $accessor->setValue($person, 'children[0].firstName', 'Wouter');
    // equal to $person->getChildren()[0]->firstName = 'Wouter'

    var_dump('Hello '.$accessor->getValue($person, 'children[0].firstName')); // 'Wouter'
    // equal to $person->getChildren()[0]->firstName

Custom method calls and virtual properties in a class
-----------------------------------------------------

.. versionadded:: 3.4
   Support for custom accessors was introduced in Symfony 3.4.

Sometimes you may not want the component to guess which method has to be called
when reading or writing properties. This is especially interesting when property
names are not in English or its singularization is not properly detected.

For those cases you can add metadata to the class being accessed so that the
component will use a particular method as a getter, setter or even adder and
remover (for collections).

Another interesting use of custom methods is declaring virtual properties
which are not stored directly in the object.

There are three supported ways to state this metadata supported out-of-the-box by
the component: using annotations, using YAML configuration files or using XML
configuration files.

.. caution::

    When using as a standalone component the metadata feature is disabled by
    default. You can enable it by calling
    :method:`PropertyAccessorBuilder::setMetadataFactory
    <Symfony\\Component\\PropertyAccess\\PropertyAccessorBuilder::setMetadataFactory>`
    see `Enable other Features`_.

There are four method calls that can be overriden: ``getter``, ``setter``, ``adder`` and
``remover``.

When using annotations you can precede a property with ``@PropertyAccessor`` to state which
method should be called when a get, set, add or remove operation is needed on the
property.

.. configuration-block::

    .. code-block:: php-annotations

        // ...
        use Symfony\Component\PropertyAccess\Annotation\PropertyAccessor;

        class Person
        {
            /**
             * @PropertyAccessor(getter="getFullName", setter="setFullName")
             */
            private $name;

            /**
             * @PropertyAccessor(adder="addNewChild", remover="discardChild")
             */
            private $children;

            public function getFullName()
            {
                return $this->name;
            }

            public function setFullName($fullName)
            {
                $this->name = $fullName;
            }
        }

    .. code-block:: yaml

        # src/AppBundle/Resources/config/property_access.yml
        Person:
            name:
                getter: getFullName
                setter: setFullName
            children:
                adder:   addNewChild
                remover: discardChild

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/property_access.xml -->
        <?xml version="1.0" ?>

        <property-access xmlns="http://symfony.com/schema/dic/property-access-mapping"
                            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                            xsi:schemaLocation="http://symfony.com/schema/dic/property-access-mapping http://symfony.com/schema/dic/property-access-mapping/property-access-mapping-1.0.xsd">

            <class name="Person">
                <property name="name" getter="getFullName" setter="setFullName" />
                <property name="children" adder="addNewChild" remover="discardChild" />
            </class>

        </property-access>

Then, using the overriden methods is automatic:

.. code-block:: php

    $person = new Person();

    $accessor->setValue($person, 'name', 'John Doe');
    // will call setFullName

    var_dump('Hello '.$accesor->getValue($person, 'name'));
    // will return 'Hello John Doe'

You can also associate a particular method with an operation on a property
using the ``@GetterAccessor``, ``@SetterAccessor``, ``@AdderAccessor`` and
``@RemoverAccessor`` annotations. All of them take only one parameter: ``property``.

This allows creating virtual properties that are not directly stored in the
object:

.. configuration-block::

    .. code-block:: php-annotations

        // ...
        use Symfony\Component\PropertyAccess\Annotation\GetterAccessor;
        use Symfony\Component\PropertyAccess\Annotation\SetterAccessor;

        class Invoice
        {
            private $quantity;

            private $pricePerUnit;

            // Notice that there is no real "total" property

            /**
             * @GetterAccessor(property="total")
             */
            public function getTotal()
            {
                return $this->quantity * $this->pricePerUnit;
            }

            // Notice that 'property' can be omitted in the parameter
            /**
             * @SetterAccessor("total")
             *
             * @param mixed $total
             */
            public function setTotal($total)
            {
                $this->quantity = $total / $this->pricePerUnit;
            }
        }

    .. code-block:: yaml

        Invoice:
            total:
                getter: getTotal
                setter: setTotal

    .. code-block:: xml

        <?xml version="1.0" ?>

        <property-access xmlns="http://symfony.com/schema/dic/property-access-mapping"
                            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                            xsi:schemaLocation="http://symfony.com/schema/dic/property-access-mapping http://symfony.com/schema/dic/property-access-mapping/property-access-mapping-1.0.xsd">

            <class name="Invoice">
                <property name="total" getter="getTotal" setter="setTotal" />
            </class>

        </property-access>

.. code-block:: php

    $invoice = new Invoice();

    $accessor->setValue($invoice, 'quantity', 20);
    $accessor->setValue($invoice, 'pricePerUnit', 10);
    var_dump('Total: '.$accesor->getValue($invoice, 'total'));
    // will return 'Total: 200'

Using property metadata with Symfony
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

By default, Symfony will look for property metadata in the following places
inside each bundle path:

- `<Bundle path>/Resources/config/property_accessor.xml`
- `<Bundle path>/Resources/config/property_accessor.yml`
- `<Bundle path>/Resources/config/property_accessor/*.xml`
- `<Bundle path>/Resources/config/property_accessor/*.yml`

If you need getting metadata from annotations you must explicitly enable them:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            property_access: { enable_annotations: true }

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:property_access enable-annotations="true" />
            </framework:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            'property_access' => array(
                'enable_annotations' => true,
            ),
        ));

Enable other Features
---------------------

The :class:`Symfony\\Component\\PropertyAccess\\PropertyAccessor` can be
configured to enable extra features. To do that you could use the
:class:`Symfony\\Component\\PropertyAccess\\PropertyAccessorBuilder`::

    // ...
    $accessorBuilder = PropertyAccess::createPropertyAccessorBuilder();

    // Enable magic __call
    $accessorBuilder->enableMagicCall();

    // Disable magic __call
    $accessorBuilder->disableMagicCall();

    // Check if magic __call handling is enabled
    $accessorBuilder->isMagicCallEnabled(); // true or false

    // At the end get the configured property accessor
    $accessor = $accessorBuilder->getPropertyAccessor();

    // Or all in one
    $accessor = PropertyAccess::createPropertyAccessorBuilder()
        ->enableMagicCall()
        ->getPropertyAccessor();

Or you can pass parameters directly to the constructor (not the recommended way)::

    // ...
    $accessor = new PropertyAccessor(true); // this enables handling of magic __call

If you need to enable metadata processing (see
`Custom method calls and virtual properties in a class`_) you must instantiate
a :class:`Symfony\\Component\\PropertyAccess\\Mapping\\Factory\\MetadataFactoryInterface`
and use the method `setMetadataFactory` on the
:class:`Symfony\\Component\\PropertyAccess\\PropertyAccessorBuilder`. Bundled with
the component you can find
a `MetadataFactory` class that supports different kind of loaders (annotations,
YAML and YML files) called :class:`Symfony\\Component\\PropertyAccess\\Mapping\\Factory\\LazyLoadingMetadataFactory`.

.. code-block:: php

    use Doctrine\Common\Annotations\AnnotationReader;
    use Symfony\Component\PropertyAccess\Mapping\Factory\LazyLoadingMetadataFactory;
    use Symfony\Component\PropertyAccess\Mapping\Loader\AnnotationLoader;
    use Symfony\Component\PropertyAccess\Mapping\Loader\LoaderChain;
    use Symfony\Component\PropertyAccess\Mapping\Loader\XMLFileLoader;
    use Symfony\Component\PropertyAccess\Mapping\Loader\YamlFileLoader;

    // ...

    $accessorBuilder = PropertyAccess::createPropertyAccessorBuilder();

    // Create annotation loader using Doctrine annotation reader
    $loader = new AnnotationLoader(new AnnotationReader());

    // or read metadata from a XML file
    $loader = new XmlFileLoader('metadata.xml');

    // or read metadata from a YAML file
    $loader = new YamlFileLoader('metadata.yml');

    // or combine several loaders in one
    $loader = new LoaderChain(
        new AnnotationLoader(new AnnotationReader()),
        new XmlFileLoader('metadata.xml'),
        new YamlFileLoader('metadata.yml'),
        new YamlFileLoader('metadata2.yml')
    );

    // Enable metadata loading
    $metadataFactory = new LazyLoadingMetadataFactory($loader);

    $accessorBuilder->setMetadataFactory($metadataFactory);

.. _Packagist: https://packagist.org/packages/symfony/property-access
.. _The Inflector component: https://github.com/symfony/inflector
